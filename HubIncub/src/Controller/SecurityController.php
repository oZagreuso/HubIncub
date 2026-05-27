<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Handles authentication entry points delegated to Symfony Security.
 */
final class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $left = random_int(2, 9);
        $right = random_int(2, 9);
        $request->getSession()->set('admin_captcha_answer', (string) ($left + $right));

        // Symfony supplies the last submitted identifier and the last authentication error.
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'captcha_question' => sprintf('%d + %d', $left, $right),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // The firewall intercepts this route before the controller body is executed.
        throw new \LogicException('This method is intercepted by the Symfony logout firewall.');
    }
}
