<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\News;
use App\Entity\Portfolio;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\NewsRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
/**
 * Centralizes the protected administration interface.
 *
 * The controller keeps form handling explicit: each POST declares a content
 * type through the `type` field, then delegates creation to a private method.
 */
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        ProjectRepository $projectRepository,
        EventRepository $eventRepository,
        NewsRepository $newsRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($request->isMethod('POST')) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $this->handleAdminPost($request, $entityManager, $portfolioRepository, $userRepository, $passwordHasher);

            return $this->redirectToRoute('app_admin');
        }

        $portfolios = $portfolioRepository->findBy([], ['promotion' => 'DESC', 'lastName' => 'ASC', 'firstName' => 'ASC']);

        return $this->render('admin/index.html.twig', [
            'portfolios' => $portfolios,
            'delegatePortfolioId' => $this->findDelegatePortfolioId($portfolios, $userRepository),
            'adminPortfolioIds' => $this->findAdminPortfolioIds($portfolios, $userRepository),
            'projects' => $projectRepository->findBy([], ['name' => 'ASC']),
            'events' => $eventRepository->findBy([], ['startsAt' => 'DESC', 'title' => 'ASC']),
            'newsItems' => $newsRepository->findBy([], ['publishedAt' => 'DESC', 'id' => 'DESC']),
        ]);
    }

    private function handleAdminPost(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): void {
        $type = (string) $request->request->get('type');

        // Each admin form has a dedicated CSRF token namespace.
        if (!$this->isCsrfTokenValid('admin_'.$type, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        match ($type) {
            'portfolio' => $this->createPortfolio($request, $entityManager, $portfolioRepository, $userRepository, $passwordHasher),
            'update_portfolio' => $this->updatePortfolio($request, $entityManager, $portfolioRepository, $userRepository),
            'delete_portfolio' => $this->deletePortfolio($request, $entityManager, $portfolioRepository, $userRepository),
            'graduate_portfolio' => $this->graduatePortfolio($request, $entityManager, $portfolioRepository),
            'set_delegate' => $this->setDelegate($request, $entityManager, $portfolioRepository, $userRepository),
            'project' => $this->createProject($request, $entityManager),
            'event' => $this->createEvent($request, $entityManager),
            'news' => $this->createNews($request, $entityManager),
            default => null,
        };

        $entityManager->flush();
    }

    private function createPortfolio(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): void {
        $email = strtolower($this->field($request, 'email'));
        $password = $this->field($request, 'password');
        $passwordConfirmation = $this->field($request, 'passwordConfirmation');
        $linkedinUrl = $this->optionalField($request, 'linkedinUrl');
        $firstName = $this->field($request, 'firstName');
        $lastName = $this->field($request, 'lastName');
        $promotion = $this->field($request, 'promotion');

        if ($portfolioRepository->findOneBy(['email' => $email]) || $userRepository->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Ce membre existe déjà avec cet email.');

            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->addFlash('error', 'La confirmation du mot de passe ne correspond pas.');

            return;
        }

        if (!$this->isCnilCompliantPassword($password)) {
            $this->addFlash('error', 'Le mot de passe doit respecter la règle CNIL affichée.');

            return;
        }

        if ($linkedinUrl && !$this->isLinkedinUrl($linkedinUrl)) {
            $this->addFlash('error', 'Le profil LinkedIn doit être une URL linkedin.com valide.');

            return;
        }

        if (!$this->isPromotionYear($promotion)) {
            $this->addFlash('error', 'La promotion doit être une année sur 4 chiffres.');

            return;
        }

        $portfolio = (new Portfolio())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($this->field($request, 'role'))
            ->setPromotion($promotion)
            ->setEmail($email)
            ->setUrl($this->field($request, 'url'))
            ->setLinkedinUrl($linkedinUrl);

        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $entityManager->persist($portfolio);
        $entityManager->persist($user);
        $this->addFlash('success', 'Ajout enregistré.');
    }

    private function updatePortfolio(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
    ): void {
        $portfolio = $portfolioRepository->find((int) $request->request->get('portfolioId'));

        if (!$portfolio) {
            $this->addFlash('error', 'Le membre à modifier est introuvable.');

            return;
        }

        $linkedUser = $userRepository->findOneBy(['email' => $portfolio->getEmail()]);

        if (!$this->isGranted('ROLE_ADMIN') && $linkedUser && in_array('ROLE_ADMIN', $linkedUser->getRoles(), true)) {
            $this->addFlash('error', 'Seul l’administrateur peut modifier cette fiche.');

            return;
        }

        $email = strtolower($this->field($request, 'email'));
        $linkedinUrl = $this->optionalField($request, 'linkedinUrl');
        $firstName = $this->field($request, 'firstName');
        $lastName = $this->field($request, 'lastName');
        $promotion = $this->field($request, 'promotion');
        $existingPortfolio = $portfolioRepository->findOneBy(['email' => $email]);
        $existingUser = $userRepository->findOneBy(['email' => $email]);

        if ($existingPortfolio && $existingPortfolio !== $portfolio) {
            $this->addFlash('error', 'Un autre membre utilise déjà cet email.');

            return;
        }

        if ($existingUser && $existingUser !== $linkedUser) {
            $this->addFlash('error', 'Un autre compte utilisateur utilise déjà cet email.');

            return;
        }

        if ($linkedinUrl && !$this->isLinkedinUrl($linkedinUrl)) {
            $this->addFlash('error', 'Le profil LinkedIn doit être une URL linkedin.com valide.');

            return;
        }

        if (!$this->isPromotionYear($promotion)) {
            $this->addFlash('error', 'La promotion doit être une année sur 4 chiffres.');

            return;
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $portfolio->setRole($this->field($request, 'role'));
        }

        if ($linkedUser) {
            $linkedUser->setEmail($email);
            $entityManager->persist($linkedUser);
        }

        $portfolio
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPromotion($promotion)
            ->setEmail($email)
            ->setUrl($this->field($request, 'url'))
            ->setLinkedinUrl($linkedinUrl);

        $entityManager->persist($portfolio);
        $this->addFlash('success', 'Fiche membre mise à jour.');
    }

    private function deletePortfolio(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
    ): void {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $portfolio = $portfolioRepository->find((int) $request->request->get('portfolioId'));

        if (!$portfolio) {
            $this->addFlash('error', 'Le membre à supprimer est introuvable.');

            return;
        }

        $user = $userRepository->findOneBy(['email' => $portfolio->getEmail()]);

        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $entityManager->remove($user);
        }

        $entityManager->remove($portfolio);
        $this->addFlash('success', 'Membre supprimé.');
    }

    private function graduatePortfolio(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
    ): void {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $portfolio = $portfolioRepository->find((int) $request->request->get('portfolioId'));

        if (!$portfolio) {
            $this->addFlash('error', 'Le membre à modifier est introuvable.');

            return;
        }

        $portfolio->setRole(Portfolio::ROLE_ALUMNI);
        $entityManager->persist($portfolio);
        $this->addFlash('success', 'Le membre est passé en ancien étudiant.');
    }

    private function setDelegate(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
    ): void {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $portfolio = $portfolioRepository->find((int) $request->request->get('portfolioId'));

        if (!$portfolio) {
            $this->addFlash('error', 'Le membre délégué est introuvable.');

            return;
        }

        $selectedUser = $userRepository->findOneBy(['email' => $portfolio->getEmail()]);

        if (!$selectedUser) {
            $this->addFlash('error', 'Le membre sélectionné ne dispose pas encore de compte utilisateur.');

            return;
        }

        if (in_array('ROLE_ADMIN', $selectedUser->getRoles(), true)) {
            $this->addFlash('error', 'L’administrateur unique ne peut pas être désigné comme délégué.');

            return;
        }

        foreach ($userRepository->findAll() as $user) {
            $roles = array_values(array_diff($user->getRoles(), ['ROLE_DELEGATE']));

            if ($user === $selectedUser) {
                $roles[] = 'ROLE_DELEGATE';
            }

            $user->setRoles(array_values(array_unique($roles)));
            $entityManager->persist($user);
        }

        $this->addFlash('success', 'Délégué des Incubateurs mis à jour.');
    }

    private function createProject(Request $request, EntityManagerInterface $entityManager): void
    {
        $name = $this->field($request, 'name');
        // SEO fallback ensures uploaded images always have descriptive alt text.
        $imageAlt = $this->optionalField($request, 'imageAlt') ?? 'Illustration du projet '.$name;

        $project = (new Project())
            ->setName($name)
            ->setDescription($this->field($request, 'description'))
            ->setUrl($this->optionalField($request, 'url'))
            ->setImageFilename($this->uploadImage($request->files->get('image'), $name, 'projects'))
            ->setImageAlt($imageAlt);

        $entityManager->persist($project);
        $this->addFlash('success', 'Ajout enregistré.');
    }

    private function createEvent(Request $request, EntityManagerInterface $entityManager): void
    {
        $startsAt = $this->optionalField($request, 'startsAt');
        $title = $this->field($request, 'title');
        // SEO fallback mirrors project images for consistent indexing metadata.
        $imageAlt = $this->optionalField($request, 'imageAlt') ?? 'Illustration de l’événement '.$title;

        $event = (new Event())
            ->setTitle($title)
            ->setDescription($this->field($request, 'description'))
            ->setStartsAt($startsAt ? new \DateTimeImmutable($startsAt) : null)
            ->setImageFilename($this->uploadImage($request->files->get('image'), $title, 'events'))
            ->setImageAlt($imageAlt);

        $entityManager->persist($event);
        $this->addFlash('success', 'Ajout enregistré.');
    }

    private function createNews(Request $request, EntityManagerInterface $entityManager): void
    {
        $publishedAt = $this->optionalField($request, 'publishedAt');
        $title = $this->field($request, 'title');
        // SEO fallback follows the same rule as projects and events.
        $imageAlt = $this->optionalField($request, 'imageAlt') ?? 'Illustration de l’actualité '.$title;

        $news = (new News())
            ->setTitle($title)
            ->setContent($this->field($request, 'content'))
            ->setPublishedAt($publishedAt ? new \DateTimeImmutable($publishedAt) : new \DateTimeImmutable())
            ->setImageFilename($this->uploadImage($request->files->get('image'), $title, 'news'))
            ->setImageAlt($imageAlt);

        $entityManager->persist($news);
        $this->addFlash('success', 'Ajout enregistré.');
    }

    private function field(Request $request, string $name): string
    {
        return trim((string) $request->request->get($name));
    }

    private function optionalField(Request $request, string $name): ?string
    {
        $value = $this->field($request, $name);

        return '' === $value ? null : $value;
    }

    private function isCnilCompliantPassword(string $password): bool
    {
        return 1 === preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{12,}$/', $password);
    }

    private function isLinkedinUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        return 'https' === $scheme && is_string($host) && preg_match('/(^|\.)linkedin\.com$/', strtolower($host));
    }

    private function isPromotionYear(string $promotion): bool
    {
        return 1 === preg_match('/^\d{4}$/', $promotion);
    }

    /**
     * @param list<Portfolio> $portfolios
     */
    private function findDelegatePortfolioId(array $portfolios, UserRepository $userRepository): ?int
    {
        foreach ($portfolios as $portfolio) {
            $user = $userRepository->findOneBy(['email' => $portfolio->getEmail()]);

            if ($user && in_array('ROLE_DELEGATE', $user->getRoles(), true)) {
                return $portfolio->getId();
            }
        }

        return null;
    }

    /**
     * @param list<Portfolio> $portfolios
     *
     * @return list<int>
     */
    private function findAdminPortfolioIds(array $portfolios, UserRepository $userRepository): array
    {
        $adminPortfolioIds = [];

        foreach ($portfolios as $portfolio) {
            $user = $userRepository->findOneBy(['email' => $portfolio->getEmail()]);

            if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $adminPortfolioIds[] = (int) $portfolio->getId();
            }
        }

        return $adminPortfolioIds;
    }

    private function uploadImage(mixed $file, string $label, string $module): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
            throw $this->createAccessDeniedException('Le fichier transmis doit être une image.');
        }

        // Filenames are human-readable for SEO and include random bytes to avoid collisions.
        $extension = $file->guessExtension() ?: 'bin';
        $filename = $this->slugify($label).'-'.bin2hex(random_bytes(6)).'.'.$extension;
        $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/admin/'.$module;

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $file->move($uploadDirectory, $filename);

        return $filename;
    }

    private function slugify(string $value): string
    {
        // Converts labels to lowercase ASCII slugs suitable for public image filenames.
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return '' === $value ? 'image-hubincub' : $value;
    }
}
