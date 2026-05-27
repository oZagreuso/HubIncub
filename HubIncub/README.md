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

L'accès est protégé par Symfony Security. Seuls les utilisateurs disposant du rôle `ROLE_ADMIN` peuvent accéder à cette page. La connexion admin exige aussi un captcha arithmétique.

Compte administrateur initial :

```text
Email : olivier@dal-ferro.com
```

Le mot de passe initial a été généré pendant la mise en place de l'administration et stocké uniquement sous forme hachée dans la base de données.

## Fonctionnalités

- Page d'accueil HubIncub.
- Annuaire des anciens avec portfolio et email visible.
- L'email et le lien portfolio d'un membre sont obligatoires.
- L'ajout d'un membre crée aussi un compte utilisateur avec mot de passe hashé.
- Un membre est soit `Incubateur`, soit `Ancien étudiant`.
- L'administrateur peut passer un membre incubateur en ancien étudiant.
- L'administrateur peut désigner un unique `Délégué des Incubateurs`.
- Le délégué peut accéder à l'administration et ajouter membres, projets, événements et actualités, sans supprimer de membre.
- Connexion administrateur.
- Captcha obligatoire sur la connexion administrateur.
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

Les images du site se trouvent dans `public/images`.

Les images ajoutées depuis l'administration sont stockées dans :

```text
public/uploads/admin
```

Les fichiers uploadés reçoivent un nom lisible pour le SEO, basé sur le nom du projet ou le titre de l'événement.

## Documentation IA

Le fichier `docs/AI_CONTEXT.md` contient une synthèse stable du projet, destinée aux assistants IA comme GPT/Codex, Claude, Gemini ou équivalents. Il doit être consulté avant toute intervention automatisée importante.
