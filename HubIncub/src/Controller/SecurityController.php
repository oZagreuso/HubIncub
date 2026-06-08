<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Gère les points d'entrée d'authentification délégués à Symfony Security.
 */
final class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $left = random_int(2, 9);
        $right = random_int(2, 9);
        $request->getSession()->set('admin_captcha_answer', (string) ($left + $right));

        // Symfony fournit le dernier identifiant soumis et la dernière erreur d'authentification.
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'captcha_question' => sprintf('%d + %d', $left, $right),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Le pare-feu intercepte cette route avant l'exécution du corps du contrôleur.
        throw new \LogicException('Cette méthode est interceptée par le pare-feu de déconnexion Symfony.');
    }
}
