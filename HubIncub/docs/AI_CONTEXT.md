# AI Context - HubIncub

Ce fichier sert de source de contexte prioritaire pour les assistants IA intervenant sur le projet HubIncub, notamment GPT/Codex, Claude, Gemini ou tout autre agent de génération, revue ou maintenance de code.

## Objectif Produit

HubIncub est le hub des anciens incubateurs de Metz Numeric School. Le site doit rester sobre, professionnel et cohérent avec l'identité orange de HubIncub.

Les objectifs principaux sont :

- présenter le réseau des anciens ;
- donner accès aux portfolios ;
- permettre à un administrateur de gérer les membres, projets et événements ;
- permettre à un administrateur de publier des actualités ;
- conserver une base technique simple, maintenable et compatible Docker.

## Stack Technique

- Symfony 8.
- Doctrine ORM.
- SQLite en local via Docker.
- Twig pour les vues.
- CSS natif modulaire.
- Symfony Security pour l'authentification admin.
- Docker Compose avec services `php` et `nginx`.

## Routes Principales

- `/` : page d'accueil.
- `/anciens` : annuaire des portfolios, protégé par authentification `ROLE_USER`.
- `/projets` : liste publique des projets.
- `/evenements` : liste publique des événements.
- `/connexion` : connexion admin.
- `/deconnexion` : déconnexion admin.
- `/admin` : interface d'administration protégée par `ROLE_ADMIN` ou `ROLE_DELEGATE`.

## Authentification

L'authentification admin utilise `symfony/security-bundle`.

La connexion est gérée par `App\Security\LoginFormAuthenticator`. Cet authenticator valide le jeton CSRF, le captcha arithmétique stocké en session, puis l'email et le mot de passe.

L'entité utilisateur est `App\Entity\User`.

Le provider Doctrine est configuré dans `config/packages/security.yaml`.

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

## Uploads et SEO Images

Les images ajoutées depuis l'administration pour les projets, événements et actualités sont stockées dans :

```text
public/uploads/admin
```

Le nom de fichier est généré à partir d'un slug du nom du projet ou du titre de l'événement, suivi d'un suffixe aléatoire.

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
- `layout.css` : header, hero, sections et structure.
- `components.css` : boutons, cartes, grilles, composants.
- `pages.css` : styles de pages spécifiques, notamment admin.
- `responsive.css` : media queries.

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
