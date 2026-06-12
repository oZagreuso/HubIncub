<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ProjectRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        NewsRepository $newsRepository,
        PortfolioRepository $portfolioRepository,
        ProjectRepository $projectRepository,
        EventRepository $eventRepository,
    ): Response
    {
        $upcomingEvent = $eventRepository->createQueryBuilder('e')
            ->andWhere('e.startsAt IS NOT NULL')
            ->andWhere('e.startsAt >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('e.startsAt', 'ASC')
            ->addOrderBy('e.title', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $promotionCount = (int) $portfolioRepository->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.promotion)')
            ->andWhere('p.promotion IS NOT NULL')
            ->andWhere('p.promotion <> :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('home/index.html.twig', [
            'latestEvent' => $eventRepository->findOneBy([], ['startsAt' => 'DESC', 'title' => 'ASC']),
            'latestNews' => $newsRepository->findLatest(),
            'latestProject' => $projectRepository->findOneBy([], ['id' => 'DESC']),
            'networkStats' => [
                'members' => $portfolioRepository->count([]),
                'promotions' => $promotionCount,
                'projects' => $projectRepository->count([]),
                'upcomingEvent' => $upcomingEvent,
            ],
        ]);
    }

    #[Route('/anciens', name: 'app_anciens')]
    public function anciens(PortfolioRepository $portfolioRepository, UserRepository $userRepository): Response
    {
        // Les rôles administratifs sont résolus depuis les comptes utilisateur pour piloter l'ordre public et le statut affiché.
        $adminEmails = [];
        $delegateEmails = [];
        $onlineEmails = [];
        $now = new \DateTimeImmutable();

        foreach ($userRepository->findAll() as $user) {
            $email = strtolower($user->getEmail());

            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $adminEmails[] = $email;
            }

            if (in_array('ROLE_DELEGATE', $user->getRoles(), true)) {
                $delegateEmails[] = $email;
            }

            if ($user->isOnline($now)) {
                $onlineEmails[] = $email;
            }
        }

        $portfolios = $portfolioRepository->findBy([], ['promotion' => 'DESC', 'lastName' => 'ASC', 'firstName' => 'ASC']);

        // La priorité de l'annuaire est la suivante : administrateurs, délégué, puis membres réguliers par ordre alphabétique.
        usort($portfolios, static function ($left, $right) use ($adminEmails, $delegateEmails): int {
            $leftRank = in_array(strtolower($left->getEmail()), $adminEmails, true) ? 0 : 2;
            $rightRank = in_array(strtolower($right->getEmail()), $adminEmails, true) ? 0 : 2;

            if ($leftRank !== 0 && in_array(strtolower($left->getEmail()), $delegateEmails, true)) {
                $leftRank = 1;
            }

            if ($rightRank !== 0 && in_array(strtolower($right->getEmail()), $delegateEmails, true)) {
                $rightRank = 1;
            }

            if ($leftRank !== $rightRank) {
                return $leftRank <=> $rightRank;
            }

            $leftPromotion = (int) ($left->getPromotion() ?? 0);
            $rightPromotion = (int) ($right->getPromotion() ?? 0);

            if ($leftPromotion !== $rightPromotion) {
                return $rightPromotion <=> $leftPromotion;
            }

            return [strtolower($left->getLastName()), strtolower($left->getFirstName())]
                <=> [strtolower($right->getLastName()), strtolower($right->getFirstName())];
        });

        $memberMapData = $this->buildMemberMapData($portfolios);

        return $this->render('home/anciens.html.twig', [
            'adminEmails' => $adminEmails,
            'delegateEmails' => $delegateEmails,
            'localizedMemberCount' => $memberMapData['localizedMemberCount'],
            'mapAreas' => $memberMapData['areas'],
            'portfolioMapAreas' => $memberMapData['portfolioAreas'],
            'onlineEmails' => $onlineEmails,
            'portfolios' => $portfolios,
        ]);
    }

    #[Route('/mon-profil', name: 'app_member_profile', methods: ['GET', 'POST'])]
    public function memberProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $email = method_exists($user, 'getUserIdentifier') ? strtolower($user->getUserIdentifier()) : '';
        $portfolio = $portfolioRepository->findOneBy(['email' => $email]);

        if (!$portfolio) {
            throw $this->createNotFoundException('Fiche membre introuvable.');
        }

        if ($request->isMethod('POST')) {
            $type = (string) $request->request->get('type');

            if (!$this->isCsrfTokenValid('member_'.$type, (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            if ('photo' === $type) {
                $photoFilename = $this->uploadMemberPhoto($request->files->get('photo'), $portfolio->getFirstName().' '.$portfolio->getLastName());

                if ($photoFilename) {
                    $this->deleteMemberPhotoFile($portfolio->getPhotoFilename());
                    $portfolio->setPhotoFilename($photoFilename);
                    $entityManager->persist($portfolio);
                    $entityManager->flush();
                    $this->addFlash('success', 'Photo de profil mise à jour.');
                }
            }

            if ('delete_photo' === $type) {
                $this->deleteMemberPhotoFile($portfolio->getPhotoFilename());
                $portfolio->setPhotoFilename(null);
                $entityManager->persist($portfolio);
                $entityManager->flush();
                $this->addFlash('success', 'Photo de profil supprimée.');
            }

            if ('location' === $type) {
                $postalCode = $this->normalizePostalCode($this->optionalField($request, 'postalCode'));

                if ($postalCode && !$this->isSupportedPostalCode($postalCode)) {
                    $this->addFlash('error', 'Le code postal doit être français sur 5 chiffres ou luxembourgeois au format L-1234.');
                } else {
                    $portfolio->setPostalCode($postalCode);
                    $entityManager->persist($portfolio);
                    $entityManager->flush();
                    $this->addFlash('success', 'Localisation mise à jour.');
                }
            }

            return $this->redirectToRoute('app_member_profile');
        }

        return $this->render('home/mon_profil.html.twig', [
            'portfolio' => $portfolio,
        ]);
    }

    #[Route('/projets', name: 'app_projets')]
    public function projets(ProjectRepository $projectRepository): Response
    {
        return $this->render('home/projets.html.twig', [
            'projects' => $projectRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/evenements', name: 'app_evenements')]
    public function evenements(EventRepository $eventRepository): Response
    {
        return $this->render('home/evenements.html.twig', [
            'events' => $eventRepository->findBy([], ['startsAt' => 'DESC', 'title' => 'ASC']),
        ]);
    }

    #[Route('/actualites', name: 'app_actualites')]
    public function actualites(NewsRepository $newsRepository): Response
    {
        return $this->render('home/actualites.html.twig', [
            'newsItems' => $newsRepository->findBy([], ['publishedAt' => 'DESC', 'id' => 'DESC']),
        ]);
    }

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('home/mentions_legales.html.twig');
    }

    private function uploadMemberPhoto(mixed $file, string $label): ?string
    {
        if (!$file instanceof UploadedFile) {
            $this->addFlash('error', 'Sélectionnez une image à téléverser.');

            return null;
        }

        if ($file->getSize() && $file->getSize() > 2097152) {
            $this->addFlash('error', 'La photo doit peser 2 Mo maximum.');

            return null;
        }

        if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
            $this->addFlash('error', 'Le fichier transmis doit être une image.');

            return null;
        }

        $extension = $file->guessExtension() ?: 'bin';
        $filename = $this->slugify($label).'-'.bin2hex(random_bytes(6)).'.'.$extension;
        $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/portfolios';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $file->move($uploadDirectory, $filename);

        return $filename;
    }

    private function deleteMemberPhotoFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->getParameter('kernel.project_dir').'/public/uploads/portfolios/'.$filename;

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function optionalField(Request $request, string $name): ?string
    {
        $value = trim((string) $request->request->get($name));

        return '' === $value ? null : $value;
    }

    private function normalizePostalCode(?string $postalCode): ?string
    {
        if (!$postalCode) {
            return null;
        }

        $postalCode = strtoupper(str_replace(' ', '', $postalCode));

        if (1 === preg_match('/^\d{4}$/', $postalCode)) {
            return 'L-'.$postalCode;
        }

        return $postalCode;
    }

    private function isSupportedPostalCode(string $postalCode): bool
    {
        return 1 === preg_match('/^\d{5}$/', $postalCode) || 1 === preg_match('/^L-\d{4}$/', $postalCode);
    }

    /**
     * @param list<\App\Entity\Portfolio> $portfolios
     *
     * @return array{areas: array<string, int>, portfolioAreas: array<int, string>, localizedMemberCount: int}
     */
    private function buildMemberMapData(array $portfolios): array
    {
        $areas = [];
        $portfolioAreas = [];
        $localizedMemberCount = 0;

        foreach ($portfolios as $portfolio) {
            $postalCode = $portfolio->getPostalCode();

            if (!$postalCode) {
                continue;
            }

            $area = str_starts_with($postalCode, 'L-') ? 'LU' : $this->frenchPostalArea($postalCode);

            $areas[$area] = ($areas[$area] ?? 0) + 1;
            $portfolioAreas[(int) $portfolio->getId()] = $area;
            ++$localizedMemberCount;
        }

        ksort($areas);

        return [
            'areas' => $areas,
            'portfolioAreas' => $portfolioAreas,
            'localizedMemberCount' => $localizedMemberCount,
        ];
    }

    private function frenchPostalArea(string $postalCode): string
    {
        if (str_starts_with($postalCode, '20')) {
            return str_starts_with($postalCode, '202') ? '2B' : '2A';
        }

        return substr($postalCode, 0, 2);
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return '' === $value ? 'photo-membre' : $value;
    }
}
