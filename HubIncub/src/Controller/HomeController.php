<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/anciens', name: 'app_anciens')]
    public function anciens(): Response
    {
        $portfolios = [
            [
                'name' => 'Dal Ferro',
                'role' => 'Ancien etudiant',
                'url' => 'https://dal-ferro.com',
            ],
            [
                'name' => 'Portfolio ancien etudiant',
                'role' => 'Promotion a renseigner',
                'url' => '#',
            ],
            [
                'name' => 'Portfolio ancien etudiant',
                'role' => 'Promotion a renseigner',
                'url' => '#',
            ],
        ];

        return $this->render('home/anciens.html.twig', [
            'portfolios' => $portfolios,
        ]);
    }
}
