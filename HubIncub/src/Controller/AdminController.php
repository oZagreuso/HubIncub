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
 * Centralise l'interface d'administration protégée.
 *
 * Le traitement des formulaires reste explicite : chaque requête POST déclare
 * une action avec le champ `type`, puis délègue l'opération à une méthode privée.
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
            $this->handleAdminPost($request, $entityManager, $portfolioRepository, $projectRepository, $eventRepository, $newsRepository, $userRepository, $passwordHasher);

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

    #[Route('/members', name: 'app_admin_members', methods: ['GET', 'POST'])]
    public function members(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($request->isMethod('POST')) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $this->handleAdminPost($request, $entityManager, $portfolioRepository, null, null, null, $userRepository, $passwordHasher);

            return $this->redirectToRoute('app_admin_members', $request->query->all());
        }

        $portfolios = $portfolioRepository->findBy([], ['promotion' => 'DESC', 'lastName' => 'ASC', 'firstName' => 'ASC']);
        $usersByEmail = $this->indexUsersByEmail($userRepository);
        $filteredPortfolios = $this->filterPortfolios($portfolios, $usersByEmail, $request);

        return $this->render('admin/members.html.twig', [
            'portfolios' => $filteredPortfolios,
            'allPortfolios' => $portfolios,
            'memberStats' => $this->buildMemberStats($portfolios, $usersByEmail),
            'usersByEmail' => $usersByEmail,
            'delegatePortfolioId' => $this->findDelegatePortfolioId($portfolios, $userRepository),
            'adminPortfolioIds' => $this->findAdminPortfolioIds($portfolios, $userRepository),
            'filters' => [
                'q' => trim((string) $request->query->get('q')),
                'status' => (string) $request->query->get('status'),
                'promotion' => (string) $request->query->get('promotion'),
                'account' => (string) $request->query->get('account'),
                'profile' => (string) $request->query->get('profile'),
            ],
            'promotions' => $this->listPromotions($portfolios),
        ]);
    }

    #[Route('/members/{id}', name: 'app_admin_member_edit', methods: ['GET', 'POST'])]
    public function editMember(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $portfolio = $portfolioRepository->find($id);

        if (!$portfolio) {
            throw $this->createNotFoundException('Membre introuvable.');
        }

        $linkedUser = $userRepository->findOneBy(['email' => $portfolio->getEmail()]);

        if (!$this->isGranted('ROLE_ADMIN') && $linkedUser && in_array('ROLE_ADMIN', $linkedUser->getRoles(), true)) {
            throw $this->createAccessDeniedException('Seul l’administrateur peut modifier cette fiche.');
        }

        if ($request->isMethod('POST')) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $this->handleAdminPost($request, $entityManager, $portfolioRepository, null, null, null, $userRepository, $passwordHasher);

            return $this->redirectToRoute('app_admin_member_edit', ['id' => $id]);
        }

        return $this->render('admin/member_edit.html.twig', [
            'portfolio' => $portfolio,
            'linkedUser' => $linkedUser,
            'isAdminPortfolio' => $linkedUser && in_array('ROLE_ADMIN', $linkedUser->getRoles(), true),
            'isDelegatePortfolio' => $linkedUser && in_array('ROLE_DELEGATE', $linkedUser->getRoles(), true),
        ]);
    }

    private function handleAdminPost(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
        ?ProjectRepository $projectRepository,
        ?EventRepository $eventRepository,
        ?NewsRepository $newsRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): void {
        $type = (string) $request->request->get('type');

        // Chaque formulaire d'administration dispose d'un espace de jeton CSRF dédié.
        if (!$this->isCsrfTokenValid('admin_'.$type, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        match ($type) {
            'portfolio' => $this->createPortfolio($request, $entityManager, $portfolioRepository, $userRepository, $passwordHasher),
            'update_portfolio' => $this->updatePortfolio($request, $entityManager, $portfolioRepository, $userRepository),
            'delete_portfolio' => $this->deletePortfolio($request, $entityManager, $portfolioRepository, $userRepository),
            'delete_portfolio_photo' => $this->deletePortfolioPhoto($request, $entityManager, $portfolioRepository),
            'graduate_portfolio' => $this->graduatePortfolio($request, $entityManager, $portfolioRepository),
            'set_delegate' => $this->setDelegate($request, $entityManager, $portfolioRepository, $userRepository),
            'project' => $this->createProject($request, $entityManager),
            'update_project' => $this->updateProject($request, $entityManager, $projectRepository),
            'delete_project' => $this->deleteProject($request, $entityManager, $projectRepository),
            'event' => $this->createEvent($request, $entityManager),
            'update_event' => $this->updateEvent($request, $entityManager, $eventRepository),
            'delete_event' => $this->deleteEvent($request, $entityManager, $eventRepository),
            'news' => $this->createNews($request, $entityManager),
            'update_news' => $this->updateNews($request, $entityManager, $newsRepository),
            'delete_news' => $this->deleteNews($request, $entityManager, $newsRepository),
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
        $postalCode = $this->normalizePostalCode($this->optionalField($request, 'postalCode'));

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

        if ($postalCode && !$this->isSupportedPostalCode($postalCode)) {
            $this->addFlash('error', 'Le code postal doit être français sur 5 chiffres ou luxembourgeois au format L-1234.');

            return;
        }

        $portfolio = (new Portfolio())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($this->field($request, 'role'))
            ->setPromotion($promotion)
            ->setEmail($email)
            ->setUrl($this->field($request, 'url'))
            ->setLinkedinUrl($linkedinUrl)
            ->setPostalCode($postalCode);

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
        $postalCode = $this->normalizePostalCode($this->optionalField($request, 'postalCode'));
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

        if ($postalCode && !$this->isSupportedPostalCode($postalCode)) {
            $this->addFlash('error', 'Le code postal doit être français sur 5 chiffres ou luxembourgeois au format L-1234.');

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
            ->setLinkedinUrl($linkedinUrl)
            ->setPostalCode($postalCode);

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

        $this->deleteUploadedFile('portfolios', $portfolio->getPhotoFilename());
        $entityManager->remove($portfolio);
        $this->addFlash('success', 'Membre supprimé.');
    }

    private function deletePortfolioPhoto(
        Request $request,
        EntityManagerInterface $entityManager,
        PortfolioRepository $portfolioRepository,
    ): void {
        $portfolio = $portfolioRepository->find((int) $request->request->get('portfolioId'));

        if (!$portfolio) {
            $this->addFlash('error', 'Le membre est introuvable.');

            return;
        }

        $this->deleteUploadedFile('portfolios', $portfolio->getPhotoFilename());
        $portfolio->setPhotoFilename(null);
        $entityManager->persist($portfolio);
        $this->addFlash('success', 'Photo membre supprimée.');
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
        // Le repli SEO garantit un texte alternatif descriptif pour chaque image téléversée.
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

    private function updateProject(Request $request, EntityManagerInterface $entityManager, ?ProjectRepository $projectRepository): void
    {
        $project = $projectRepository?->find((int) $request->request->get('projectId'));

        if (!$project) {
            $this->addFlash('error', 'Le projet à modifier est introuvable.');

            return;
        }

        $name = $this->field($request, 'name');
        $imageFilename = $this->uploadImage($request->files->get('image'), $name, 'projects');

        if ($imageFilename) {
            $this->deleteUploadedFile('projects', $project->getImageFilename());
            $project->setImageFilename($imageFilename);
        }

        $project
            ->setName($name)
            ->setDescription($this->field($request, 'description'))
            ->setUrl($this->optionalField($request, 'url'))
            ->setImageAlt($this->optionalField($request, 'imageAlt') ?? 'Illustration du projet '.$name);

        $entityManager->persist($project);
        $this->addFlash('success', 'Projet mis à jour.');
    }

    private function deleteProject(Request $request, EntityManagerInterface $entityManager, ?ProjectRepository $projectRepository): void
    {
        $project = $projectRepository?->find((int) $request->request->get('projectId'));

        if (!$project) {
            $this->addFlash('error', 'Le projet à supprimer est introuvable.');

            return;
        }

        $this->deleteUploadedFile('projects', $project->getImageFilename());
        $entityManager->remove($project);
        $this->addFlash('success', 'Projet supprimé.');
    }

    private function createEvent(Request $request, EntityManagerInterface $entityManager): void
    {
        $startsAt = $this->optionalField($request, 'startsAt');
        $title = $this->field($request, 'title');
        // Le repli SEO reprend la règle des projets pour conserver des métadonnées d'indexation cohérentes.
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

    private function updateEvent(Request $request, EntityManagerInterface $entityManager, ?EventRepository $eventRepository): void
    {
        $event = $eventRepository?->find((int) $request->request->get('eventId'));

        if (!$event) {
            $this->addFlash('error', 'L’événement à modifier est introuvable.');

            return;
        }

        $startsAt = $this->optionalField($request, 'startsAt');
        $title = $this->field($request, 'title');
        $imageFilename = $this->uploadImage($request->files->get('image'), $title, 'events');

        if ($imageFilename) {
            $this->deleteUploadedFile('events', $event->getImageFilename());
            $event->setImageFilename($imageFilename);
        }

        $event
            ->setTitle($title)
            ->setDescription($this->field($request, 'description'))
            ->setStartsAt($startsAt ? new \DateTimeImmutable($startsAt) : null)
            ->setImageAlt($this->optionalField($request, 'imageAlt') ?? 'Illustration de l’événement '.$title);

        $entityManager->persist($event);
        $this->addFlash('success', 'Événement mis à jour.');
    }

    private function deleteEvent(Request $request, EntityManagerInterface $entityManager, ?EventRepository $eventRepository): void
    {
        $event = $eventRepository?->find((int) $request->request->get('eventId'));

        if (!$event) {
            $this->addFlash('error', 'L’événement à supprimer est introuvable.');

            return;
        }

        $this->deleteUploadedFile('events', $event->getImageFilename());
        $entityManager->remove($event);
        $this->addFlash('success', 'Événement supprimé.');
    }

    private function createNews(Request $request, EntityManagerInterface $entityManager): void
    {
        $publishedAt = $this->optionalField($request, 'publishedAt');
        $title = $this->field($request, 'title');
        // Le repli SEO applique la même règle que les projets et les événements.
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

    private function updateNews(Request $request, EntityManagerInterface $entityManager, ?NewsRepository $newsRepository): void
    {
        $news = $newsRepository?->find((int) $request->request->get('newsId'));

        if (!$news) {
            $this->addFlash('error', 'L’actualité à modifier est introuvable.');

            return;
        }

        $publishedAt = $this->optionalField($request, 'publishedAt');
        $title = $this->field($request, 'title');
        $imageFilename = $this->uploadImage($request->files->get('image'), $title, 'news');

        if ($imageFilename) {
            $this->deleteUploadedFile('news', $news->getImageFilename());
            $news->setImageFilename($imageFilename);
        }

        $news
            ->setTitle($title)
            ->setContent($this->field($request, 'content'))
            ->setPublishedAt($publishedAt ? new \DateTimeImmutable($publishedAt) : $news->getPublishedAt())
            ->setImageAlt($this->optionalField($request, 'imageAlt') ?? 'Illustration de l’actualité '.$title);

        $entityManager->persist($news);
        $this->addFlash('success', 'Actualité mise à jour.');
    }

    private function deleteNews(Request $request, EntityManagerInterface $entityManager, ?NewsRepository $newsRepository): void
    {
        $news = $newsRepository?->find((int) $request->request->get('newsId'));

        if (!$news) {
            $this->addFlash('error', 'L’actualité à supprimer est introuvable.');

            return;
        }

        $this->deleteUploadedFile('news', $news->getImageFilename());
        $entityManager->remove($news);
        $this->addFlash('success', 'Actualité supprimée.');
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
     * @return array<string, User>
     */
    private function indexUsersByEmail(UserRepository $userRepository): array
    {
        $usersByEmail = [];

        foreach ($userRepository->findAll() as $user) {
            $usersByEmail[strtolower($user->getEmail())] = $user;
        }

        return $usersByEmail;
    }

    /**
     * @param list<Portfolio> $portfolios
     * @param array<string, User> $usersByEmail
     *
     * @return list<Portfolio>
     */
    private function filterPortfolios(array $portfolios, array $usersByEmail, Request $request): array
    {
        $query = strtolower(trim((string) $request->query->get('q')));
        $status = (string) $request->query->get('status');
        $promotion = (string) $request->query->get('promotion');
        $account = (string) $request->query->get('account');
        $profile = (string) $request->query->get('profile');

        return array_values(array_filter($portfolios, function (Portfolio $portfolio) use ($usersByEmail, $query, $status, $promotion, $account, $profile): bool {
            $email = strtolower($portfolio->getEmail());
            $user = $usersByEmail[$email] ?? null;

            if ('' !== $query) {
                $haystack = strtolower(implode(' ', [
                    $portfolio->getFirstName(),
                    $portfolio->getLastName(),
                    $portfolio->getEmail(),
                    $portfolio->getPromotion(),
                ]));

                if (!str_contains($haystack, $query)) {
                    return false;
                }
            }

            if ('' !== $status && $portfolio->getRole() !== $status) {
                return false;
            }

            if ('' !== $promotion && $portfolio->getPromotion() !== $promotion) {
                return false;
            }

            if ('with_account' === $account && null === $user) {
                return false;
            }

            if ('without_account' === $account && null !== $user) {
                return false;
            }

            if ('admin' === $account && (!$user || !in_array('ROLE_ADMIN', $user->getRoles(), true))) {
                return false;
            }

            if ('delegate' === $account && (!$user || !in_array('ROLE_DELEGATE', $user->getRoles(), true))) {
                return false;
            }

            if ('without_photo' === $profile && null !== $portfolio->getPhotoFilename()) {
                return false;
            }

            if ('without_linkedin' === $profile && null !== $portfolio->getLinkedinUrl()) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @param list<Portfolio> $portfolios
     * @param array<string, User> $usersByEmail
     *
     * @return array<string, int>
     */
    private function buildMemberStats(array $portfolios, array $usersByEmail): array
    {
        $stats = [
            'total' => count($portfolios),
            'incubators' => 0,
            'alumni' => 0,
            'withAccount' => 0,
            'withoutAccount' => 0,
        ];

        foreach ($portfolios as $portfolio) {
            if (Portfolio::ROLE_INCUBATOR === $portfolio->getRole()) {
                ++$stats['incubators'];
            }

            if (Portfolio::ROLE_ALUMNI === $portfolio->getRole()) {
                ++$stats['alumni'];
            }

            if (isset($usersByEmail[strtolower($portfolio->getEmail())])) {
                ++$stats['withAccount'];
            } else {
                ++$stats['withoutAccount'];
            }
        }

        return $stats;
    }

    /**
     * @param list<Portfolio> $portfolios
     *
     * @return list<string>
     */
    private function listPromotions(array $portfolios): array
    {
        $promotions = [];

        foreach ($portfolios as $portfolio) {
            if ($portfolio->getPromotion()) {
                $promotions[] = $portfolio->getPromotion();
            }
        }

        $promotions = array_values(array_unique($promotions));
        rsort($promotions);

        return $promotions;
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

    private function uploadImage(mixed $file, string $label, string $module, int $maxBytes = 3145728): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        if ($file->getSize() && $file->getSize() > $maxBytes) {
            throw $this->createAccessDeniedException('Le fichier transmis est trop volumineux.');
        }

        if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
            throw $this->createAccessDeniedException('Le fichier transmis doit être une image.');
        }

        // Les noms de fichiers restent lisibles pour le SEO et incluent des octets aléatoires pour éviter les collisions.
        $extension = $file->guessExtension() ?: 'bin';
        $filename = $this->slugify($label).'-'.bin2hex(random_bytes(6)).'.'.$extension;
        $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/admin/'.$module;

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $file->move($uploadDirectory, $filename);

        return $filename;
    }

    private function deleteUploadedFile(string $module, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $baseDirectory = $this->getParameter('kernel.project_dir').'/public/uploads';
        $directory = 'portfolios' === $module ? $baseDirectory.'/portfolios' : $baseDirectory.'/admin/'.$module;
        $path = $directory.'/'.$filename;

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function slugify(string $value): string
    {
        // Les libellés sont convertis en slugs ASCII minuscules adaptés aux noms de fichiers publics.
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return '' === $value ? 'image-hubincub' : $value;
    }
}
