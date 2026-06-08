<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ProjectRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        NewsRepository $newsRepository,
        ProjectRepository $projectRepository,
        EventRepository $eventRepository,
    ): Response
    {
        return $this->render('home/index.html.twig', [
            'latestEvent' => $eventRepository->findOneBy([], ['startsAt' => 'DESC', 'title' => 'ASC']),
            'latestNews' => $newsRepository->findLatest(),
            'latestProject' => $projectRepository->findOneBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/anciens', name: 'app_anciens')]
    public function anciens(PortfolioRepository $portfolioRepository, UserRepository $userRepository): Response
    {
        // Administrative roles are resolved from User accounts to control the public ordering and displayed status.
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

        // Directory priority is: administrators, delegate, then regular members ordered alphabetically.
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

        return $this->render('home/anciens.html.twig', [
            'adminEmails' => $adminEmails,
            'delegateEmails' => $delegateEmails,
            'onlineEmails' => $onlineEmails,
            'portfolios' => $portfolios,
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
}
