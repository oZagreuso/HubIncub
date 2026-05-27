<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\PortfolioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'latestNews' => $newsRepository->findLatest(),
        ]);
    }

    #[Route('/anciens', name: 'app_anciens')]
    public function anciens(PortfolioRepository $portfolioRepository): Response
    {
        return $this->render('home/anciens.html.twig', [
            'portfolios' => $portfolioRepository->findBy([], ['lastName' => 'ASC', 'firstName' => 'ASC']),
        ]);
    }
}
