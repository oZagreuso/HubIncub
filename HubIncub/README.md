# HubIncub

HubIncub est un site Symfony destiné à présenter le réseau des anciens incubateurs de Metz Numeric School. Il expose une page d'accueil, un annuaire de portfolios et une interface d'administration protégée.

## Démarrage local

```powershell
docker compose up --build
```

Le site est ensuite disponible sur :

```text
http://localhost:8000
```

## Accès administration

L'administration est accessible depuis le menu principal ou directement via :

```text
http://localhost:8000/admin
```

L'accès est protégé par Symfony Security. Les utilisateurs disposant du rôle `ROLE_ADMIN` ou `ROLE_DELEGATE` peuvent accéder à cette page. La connexion admin exige aussi un captcha arithmétique.

Compte administrateur initial :

```text
Email : olivier@dal-ferro.com
```

Le mot de passe initial a été généré pendant la mise en place de l'administration et stocké uniquement sous forme hachée dans la base de données.

## Fonctionnalités

- Page d'accueil HubIncub.
- Annuaire des anciens avec portfolio et email visible, réservé aux membres connectés.
- Statut de présence visible dans l'annuaire lorsqu'un membre est récemment actif.
- Pages publiques `Projets` et `Événements`.
- L'email et le lien portfolio d'un membre sont obligatoires.
- Le profil LinkedIn d'un membre peut être renseigné de façon optionnelle avec une URL `linkedin.com` valide.
- L'ajout d'un membre crée aussi un compte utilisateur avec mot de passe hashé.
- L'administrateur et le délégué peuvent modifier les informations de base d'un membre dans leur champ de compétence.
- Un membre est soit `Incubateur`, soit `Ancien étudiant`.
- L'administrateur peut passer un membre incubateur en ancien étudiant.
- L'administrateur peut désigner un unique `Délégué des Incubateurs`.
- Le délégué peut accéder à l'administration et ajouter membres, projets, événements et actualités, sans supprimer de membre.
- Connexion administrateur.
- Captcha obligatoire sur la connexion membre et administrateur.
- Suivi de présence basé sur la dernière activité authentifiée, avec affichage d'un badge `Connecté`.
- Ajout de membres à l'annuaire.
- Suppression de membres depuis l'administration.
- Ajout de projets.
- Ajout d'événements.
- Ajout d'actualités, avec affichage automatique de la dernière sur la page d'accueil.
- Upload persistant d'images pour les projets et événements.
- Texte alternatif SEO pour les images administrées.
- CSS organisé en modules.

## Structure CSS

Le fichier `public/styles/app.css` est le point d'entrée unique. Il importe les modules suivants :

- `modules/colors.css` : variables de couleur.
- `modules/base.css` : styles globaux, typographie et reset léger.
- `modules/layout.css` : structure générale du site.
- `modules/components.css` : boutons, cartes, listes et composants réutilisables.
- `modules/pages.css` : styles propres aux pages.
- `modules/responsive.css` : adaptations responsive.

## Images

Les images du site se trouvent dans `public/images`, organisées par module :

```text
public/images/branding
public/images/errors
public/images/layout
public/images/partners
```

Les images ajoutées depuis l'administration sont stockées dans :

```text
public/uploads/admin/projects
public/uploads/admin/events
public/uploads/admin/news
```

Les fichiers uploadés reçoivent un nom lisible pour le SEO, basé sur le nom du projet, le titre de l'événement ou le titre de l'actualité.

## Présence des membres

Le statut de présence des membres est déterminé à partir du champ `lastSeenAt` de l'entité `User`. Ce champ est mis à jour automatiquement lorsqu'un utilisateur authentifié navigue sur le site. Un membre est affiché comme `Connecté` dans l'annuaire lorsque sa dernière activité date de moins de cinq minutes.

## Documentation IA

Le fichier `docs/AI_CONTEXT.md` contient une synthèse stable du projet, destinée aux assistants IA comme GPT/Codex, Claude, Gemini ou équivalents. Il doit être consulté avant toute intervention automatisée importante.

Le fichier `docs/WORK_SUMMARY_2026-05-28.md` documente les changements récents apportés à l’annuaire, aux données de test, aux pages d’erreur, à l’administration et aux ajustements visuels.
