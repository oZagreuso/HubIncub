# Agent Instructions - HubIncub

This file is intended for AI coding agents such as GPT/Codex, Claude, Gemini and similar tools.

Before changing the project, read:

```text
docs/AI_CONTEXT.md
```

## Mandatory Project Rules

- Keep `public/styles/app.css` as an import-only entry point.
- Add new CSS in the relevant file under `public/styles/modules`.
- Add a Doctrine migration for every schema change.
- Keep uploaded project and event images on disk under `public/uploads/admin`.
- Keep uploaded news images on disk under `public/uploads/admin`.
- Persist image metadata in the database, not binary image data.
- Preserve Symfony Security for admin authentication.
- Preserve captcha validation in `App\Security\LoginFormAuthenticator`.
- Keep `/admin` restricted to `ROLE_ADMIN`.
- Keep Olivier Dal Ferro as the only expected administrator.
- Keep `ROLE_DELEGATE` unique and subordinate to the administrator.
- Do not hard-code plain-text passwords in controllers, templates or fixtures.
- Validate PHP syntax and Doctrine schema after backend changes.

## Preferred Verification

```powershell
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:schema:validate
docker compose exec php php bin/console debug:router
```
