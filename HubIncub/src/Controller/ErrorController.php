<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ErrorController extends AbstractController
{
    // Les routes de prévisualisation affichent les gabarits d'erreur de production lorsque le mode debug local est actif.
    #[Route('/403', name: 'app_error_403')]
    public function forbidden(): Response
    {
        return $this->render(
            'bundles/TwigBundle/Exception/error403.html.twig',
            [],
            new Response(status: Response::HTTP_FORBIDDEN),
        );
    }

    #[Route('/404', name: 'app_error_404')]
    public function notFound(): Response
    {
        return $this->render(
            'bundles/TwigBundle/Exception/error404.html.twig',
            [],
            new Response(status: Response::HTTP_NOT_FOUND),
        );
    }

    #[Route('/500', name: 'app_error_500')]
    public function serverError(): Response
    {
        return $this->render(
            'bundles/TwigBundle/Exception/error500.html.twig',
            [],
            new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR),
        );
    }
}
