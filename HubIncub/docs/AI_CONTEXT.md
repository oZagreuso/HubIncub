# Contexte IA - HubIncub

Ce fichier sert de source de contexte prioritaire pour les assistants IA intervenant sur le projet HubIncub, notamment GPT/Codex, Claude, Gemini ou tout autre agent de génération, revue ou maintenance de code.

## Objectif Produit

HubIncub est le hub des anciens incubateurs de Metz Numeric School. Le site doit rester sobre, professionnel et cohérent avec l'identité orange de HubIncub.

Les objectifs principaux sont :

- présenter le réseau des anciens ;
- donner accès aux portfolios ;
- permettre à un administrateur de gérer les membres, projets et événements ;
- permettre à un administrateur de publier des actualités ;
- conserver une base technique simple, maintenable et compatible Docker.

## Socle Technique

- Symfony 8.
- Doctrine ORM.
- SQLite en local via Docker.
- Twig pour les vues.
- CSS natif modulaire.
- Symfony Security pour l'authentification d'administration et des membres.
- Docker Compose avec services `php` et `nginx`.

## Routes Principales

- `/` : page d'accueil.
- `/anciens` : annuaire des portfolios, protégé par authentification `ROLE_USER`.
- `/projets` : liste publique des projets.
- `/evenements` : liste publique des événements.
- `/connexion` : connexion membre et administration.
- `/deconnexion` : déconnexion membre et administration.
- `/admin` : interface d'administration protégée par `ROLE_ADMIN` ou `ROLE_DELEGATE`.

## Authentification

L'authentification utilise `symfony/security-bundle`.

La connexion est gérée par `App\Security\LoginFormAuthenticator`. Cet authentificateur valide le jeton CSRF, le captcha arithmétique stocké en session, puis l'email et le mot de passe.

L'entité utilisateur est `App\Entity\User`.

Le fournisseur Doctrine est configuré dans `config/packages/security.yaml`.

La zone `/admin` est protégée par :

```yaml
access_control:
    - { path: ^/admin, roles: [ROLE_ADMIN, ROLE_DELEGATE] }
    - { path: ^/anciens, roles: ROLE_USER }
```

Le compte initial d'Olivier Dal Ferro dispose de `ROLE_ADMIN` et utilise l'email :

```text
olivier@dal-ferro.com
```

Les mots de passe doivent rester hachés. Aucun mot de passe en clair ne doit être ajouté dans le code hors documentation temporaire explicitement demandée.

## Entités Métier

### Portfolio

Représente un membre de l'annuaire.

Champs principaux :

- `firstName`
- `lastName`
- `role`
- `email`
- `url`
- `linkedinUrl`

Règles métier :

- `firstName`, `lastName`, `role`, `email` et `url` sont obligatoires.
- `linkedinUrl` est optionnel et doit rester une URL HTTPS `linkedin.com` valide lorsqu'il est renseigné.
- `role` accepte uniquement `Incubateur` ou `Ancien étudiant`.
- `email` doit être unique dans les membres et dans les utilisateurs.
- L'ajout d'un membre crée un `User` avec `ROLE_USER`.
- Le mot de passe membre est obligatoire, confirmé une seconde fois, puis haché avant persistance.
- La règle affichée pour le mot de passe suit le minimum CNIL utilisé dans le projet : 12 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.
- La modification d'un membre se fait par POST CSRF `update_portfolio`.
- `ROLE_ADMIN` peut modifier toutes les informations de base d'un membre et son statut.
- `ROLE_DELEGATE` peut modifier les informations de base des membres non administrateurs, sans modifier le statut, sans supprimer, sans désigner le délégué et sans intervenir sur la fiche liée à `ROLE_ADMIN`.
- La suppression d'un membre se fait par POST CSRF `delete_portfolio` et supprime aussi le `User` associé si ce compte n'est pas administrateur.
- Le passage de `Incubateur` à `Ancien étudiant` se fait par POST CSRF `graduate_portfolio`.
- Olivier Dal Ferro est le seul administrateur attendu et son statut membre est `Incubateur`.
- Le rôle `ROLE_DELEGATE` désigne l'unique Délégué des Incubateurs.
- Seul `ROLE_ADMIN` peut désigner le délégué, supprimer un membre ou passer un membre en ancien étudiant.
- `ROLE_DELEGATE` peut accéder à `/admin` pour ajouter des membres, projets, événements et actualités.

### Project

Représente un projet ajouté depuis l'administration.

Champs principaux :

- `name`
- `description`
- `url`
- `imageFilename`
- `imageAlt`

### Event

Représente un événement ajouté depuis l'administration.

Champs principaux :

- `title`
- `startsAt`
- `description`
- `imageFilename`
- `imageAlt`

### News

Représente une actualité ajoutée depuis l'administration.

La dernière actualité publiée est affichée automatiquement sur la page d'accueil.

Champs principaux :

- `title`
- `content`
- `publishedAt`
- `imageFilename`
- `imageAlt`

### User

Représente un utilisateur authentifiable.

Champs principaux :

- `email`
- `roles`
- `password`
- `lastSeenAt`

La présence en ligne est suivie avec `lastSeenAt`. Le champ est mis à jour automatiquement par `App\EventSubscriber\UserPresenceSubscriber` lors des requêtes authentifiées, avec un intervalle minimal d'une minute entre deux écritures. Un utilisateur est considéré connecté si sa dernière activité date de moins de cinq minutes.

## Téléversements et SEO des Images

Les images ajoutées depuis l'administration pour les projets, événements et actualités sont stockées dans :

```text
public/uploads/admin/projects
public/uploads/admin/events
public/uploads/admin/news
```

Le nom de fichier est généré à partir d'un slug du nom du projet, du titre de l'événement ou du titre de l'actualité, suivi d'un suffixe aléatoire.

Les images administrées doivent disposer d'un texte alternatif :

- fourni manuellement via le champ `Texte alternatif SEO` ;
- généré automatiquement à partir du titre si le champ est vide.

Les images rendues dans Twig doivent conserver autant que possible :

- `alt` descriptif ;
- `width` et `height` ;
- `loading="lazy"` pour les images non prioritaires ;
- `decoding="async"`.

## CSS

Le CSS est volontairement séparé en modules :

- `colors.css` : variables et palette.
- `base.css` : base document, typographie globale.
- `layout.css` : en-tête, héros, sections et structure.
- `components.css` : boutons, cartes, grilles, composants.
- `pages.css` : styles de pages spécifiques, notamment administration.
- `responsive.css` : requêtes média.

`app.css` ne doit rester qu'un point d'entrée avec des `@import`.

## Conventions de Maintenance

- Préserver la simplicité du projet.
- Éviter les refontes larges non demandées.
- Garder les styles cohérents avec la palette orange/navy existante.
- Ne pas supprimer les migrations existantes.
- Ajouter une migration pour toute modification de schéma.
- Ne pas stocker les uploads en base64 en base de données.
- Ne pas exposer de mot de passe en clair dans les templates ou contrôleurs.
- Tester au minimum :
  - la syntaxe PHP ;
  - les migrations ;
  - `doctrine:schema:validate` ;
  - les routes publiques principales.

## Commandes Utiles

```powershell
docker compose up --build
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:schema:validate
docker compose exec php php bin/console debug:router
```

## État Courant Vérifié

Cette section décrit l'état du projet à maintenir comme référence opérationnelle pour les interventions futures. Le style attendu est professionnel, factuel et impersonnel.

### Positionnement

HubIncub est présenté comme le hub des anciens de l'Incubateur de MNS.

Le titre principal de la page d'accueil est :

```text
Le hub des anciens de l'Incubateur de MNS
```

La page d'accueil doit mettre en avant le réseau, les derniers contenus administrés et l'accès aux principales rubriques sans adopter une logique de page marketing déconnectée du produit.

### Architecture Applicative

Le projet est une application Symfony organisée selon une structure classique :

- `src/Controller` contient les contrôleurs HTTP.
- `src/Entity` contient les entités Doctrine.
- `src/Repository` contient les dépôts Doctrine.
- `src/Security` contient l'authentificateur personnalisé.
- `src/EventSubscriber` contient le suivi de présence utilisateur.
- `templates` contient les vues Twig.
- `public/styles/modules` contient les modules CSS.
- `public/images` contient les images statiques.
- `public/uploads/admin` contient les images administrées.
- `migrations` contient l'historique des changements de schéma et des données initiales.

Le projet ne doit pas être transformé en application JavaScript côté client. Les vues Twig, le CSS natif et les traitements Symfony doivent rester l'approche principale.

### Contrôleurs Publics

`App\Controller\HomeController` expose les pages publiques et protégées suivantes :

- `app_home` sur `/`, avec affichage de la dernière actualité, du dernier projet et du dernier événement.
- `app_anciens` sur `/anciens`, protégé par `ROLE_USER`.
- `app_projets` sur `/projets`.
- `app_evenements` sur `/evenements`.
- `app_actualites` sur `/actualites`.
- `app_mentions_legales` sur `/mentions-legales`.

La page `/anciens` trie les portfolios avec priorité aux administrateurs, puis au délégué, puis aux autres membres. La présence en ligne est calculée à partir des utilisateurs dont `lastSeenAt` date de moins de cinq minutes.

### Administration

`App\Controller\AdminController` centralise l'administration sous `/admin`.

Deux écrans principaux existent :

- `/admin` : tableau de gestion des contenus, membres, projets, événements et actualités.
- `/admin/members` : gestion dédiée des membres avec filtres et statistiques.

Les formulaires d'administration utilisent un champ `type` et des jetons CSRF de la forme `admin_{type}`. Les actions POST sont distribuées par `handleAdminPost`.

Actions prises en charge :

- création de membre ;
- modification de fiche membre ;
- suppression de membre ;
- passage en ancien étudiant ;
- désignation du délégué ;
- création de projet ;
- création d'événement ;
- création d'actualité.

Les règles d'autorisation sont les suivantes :

- `ROLE_ADMIN` conserve tous les droits d'administration.
- `ROLE_DELEGATE` peut accéder à l'administration et ajouter des contenus ou des membres.
- `ROLE_DELEGATE` ne doit pas pouvoir supprimer un membre, changer un statut, désigner un délégué ou modifier la fiche liée à un compte administrateur.
- L'administrateur attendu reste Olivier Dal Ferro.
- Le rôle `ROLE_DELEGATE` doit rester unique.

### Modèle de Données

`Portfolio` représente un membre affiché dans l'annuaire. L'email est unique. Les statuts valides sont exclusivement :

- `Incubateur`
- `Ancien étudiant`

Les champs métier principaux sont `firstName`, `lastName`, `role`, `url`, `email`, `linkedinUrl` et `promotion`.

`User` représente un compte authentifiable. Il contient l'email, les rôles, le mot de passe haché et `lastSeenAt`. La méthode `getRoles` ajoute toujours `ROLE_USER`. La méthode `isOnline` considère un utilisateur connecté si la dernière activité date de moins de cinq minutes.

`Project` représente un projet administré. Les champs principaux sont `name`, `description`, `url`, `imageFilename` et `imageAlt`.

`Event` représente un événement administré. Les champs principaux sont `title`, `startsAt`, `description`, `imageFilename` et `imageAlt`.

`News` représente une actualité administrée. Les champs principaux sont `title`, `content`, `publishedAt`, `imageFilename` et `imageAlt`. La dernière actualité publiée est utilisée sur la page d'accueil.

### Validation Métier

Lors de la création d'un membre :

- l'email est normalisé en minuscules ;
- l'email doit être unique côté `Portfolio` et côté `User` ;
- le mot de passe doit être confirmé ;
- le mot de passe doit respecter la règle CNIL utilisée par le projet : au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial ;
- l'URL LinkedIn doit être une URL HTTPS dont l'hôte est `linkedin.com` ou un sous-domaine de `linkedin.com` ;
- la promotion doit être une année sur quatre chiffres ;
- un compte `User` avec `ROLE_USER` est créé en même temps que la fiche `Portfolio`.

Lors de la modification d'un membre :

- l'email reste synchronisé entre `Portfolio` et `User` si un utilisateur lié existe ;
- un délégué ne doit pas modifier la fiche d'un administrateur ;
- seul un administrateur peut modifier le statut.

### Authentification et Sécurité

L'authentification est gérée par `App\Security\LoginFormAuthenticator`.

Le formulaire de connexion valide :

- le captcha arithmétique stocké en session ;
- le jeton CSRF `authenticate` ;
- l'email ;
- le mot de passe.

Après connexion réussie, la session supprime `admin_captcha_answer`. La redirection utilise le chemin cible si présent, sinon `/admin`.

Les règles de sécurité principales sont :

- `/admin` nécessite `ROLE_ADMIN` ou `ROLE_DELEGATE`.
- `/anciens` nécessite `ROLE_USER`.
- les mots de passe doivent rester hachés ;
- aucun mot de passe en clair ne doit être ajouté dans les contrôleurs, templates, migrations ou documentation durable.

### Téléversements

Les images administrées sont stockées sur disque :

- projets : `public/uploads/admin/projects`
- événements : `public/uploads/admin/events`
- actualités : `public/uploads/admin/news`

Le nom de fichier est généré par slug ASCII du libellé, suivi d'un suffixe aléatoire de 6 octets encodés en hexadécimal. Le fichier doit être une image selon son type MIME.

La base de données ne stocke jamais l'image binaire. Elle stocke uniquement `imageFilename` et `imageAlt`.

### Interface et Contenus

Les templates publics sont situés dans `templates/home`.

La navigation principale de la page d'accueil donne accès à :

- Réseau ;
- Projets ;
- Événements ;
- Actualités ;
- Administration.

La page d'accueil utilise :

- le logo HubIncub ;
- les logos partenaires Metz Numeric School et IFA Business School ;
- une section `Réseau en chiffres` alimentée par les données réelles : membres, promotions représentées, projets publiés et prochain événement programmé ;
- un flux éditorial `À la une` si au moins une actualité, un projet ou un événement existe ;
- trois cartes de réseau menant vers anciens, projets et événements.

Les images Twig doivent conserver des attributs utiles à la performance et à l'accessibilité : `alt`, `width`, `height`, `loading` lorsque pertinent et `decoding="async"`.

### Palette et Direction Visuelle

La palette est centralisée dans `public/styles/modules/colors.css`.

L'orange de marque courant est plus vif que la version initiale :

```css
--orange: #ff5a00;
--orange-dark: #d64a00;
--orange-hover: #aa3900;
--orange-soft: #ffe6d8;
--orange-wash: #fff3ec;
```

Le contrepoint secondaire courant est un bleu-vert :

```css
--teal: #0f7c7a;
--teal-soft: #e2f5f3;
--teal-wash: #f1fbfa;
```

Les neutres ont été refroidis pour éviter une interface trop brune :

```css
--navy: #20242a;
--navy-hover: #15191f;
--navy-soft: #edf1f4;
--navy-ink: #171b20;
--ink: #262a2f;
--muted: #66717b;
--subtle: #8a96a1;
--line: #dce3e8;
--line-strong: #c5d0d8;
--page: #fff7f1;
--surface: #ffffff;
--surface-soft: #f7fafb;
--surface-warm: #ffe9dc;
```

Les futures évolutions visuelles doivent préserver une interface sobre, professionnelle, lisible et cohérente avec l'identité orange de HubIncub.

### Conventions CSS

`public/styles/app.css` doit rester limité aux imports.

Les styles doivent être ajoutés dans le module pertinent :

- `colors.css` pour les variables ;
- `base.css` pour la base globale ;
- `layout.css` pour l'en-tête, les héros, sections et structures générales ;
- `components.css` pour boutons, cartes, grilles et composants réutilisables ;
- `pages.css` pour les vues spécifiques ;
- `responsive.css` pour les adaptations responsive.

Les modifications visuelles doivent réutiliser les variables existantes autant que possible.

### Migrations et Données

Toute modification du schéma Doctrine doit être accompagnée d'une migration.

Les migrations existantes ne doivent pas être supprimées. Les données de démonstration et contenus initiaux déjà présents doivent être traités comme un historique applicatif, sauf demande explicite contraire.

### Vérifications Attendues

Après une modification backend, les vérifications recommandées sont :

```powershell
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:schema:validate
docker compose exec php php bin/console debug:router
```

Après une modification PHP ciblée, vérifier au minimum la syntaxe du ou des fichiers modifiés.

Après une modification de Twig ou CSS, vérifier le rendu des pages concernées lorsque l'environnement local est disponible.

### Principes d'Intervention

Les interventions futures doivent :

- conserver une formulation professionnelle et impersonnelle dans la documentation projet ;
- limiter les modifications au besoin exprimé ;
- éviter les refontes larges non demandées ;
- préserver les règles de sécurité et d'accès ;
- éviter la duplication de logique métier ;
- ne pas déplacer les responsabilités sans nécessité ;
- ne pas convertir la base Twig/CSS en architecture frontend lourde ;
- documenter les changements durables dans ce fichier lorsque cela améliore les interventions futures.
