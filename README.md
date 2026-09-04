# Seven S Racing

PDC-managed WordPress build for Seven S Racing and driver Nick Shannon.

## Repository scope

This repository intentionally versions only PDC-owned code and reviewed seed assets:

- `wp-content/themes/seven-s-racing` — public presentation
- `wp-content/plugins/seven-s-core` — racing content types and owner dashboard
- `preview` — dependency-free visual review build
- `docs` — provisioning, deployment, and content notes

WordPress core, runtime uploads, secrets, caches, and the database do not belong in Git.

## Review locally

From the repository root, run `npx serve preview` and open the printed local URL. The preview is static; the production theme reads the same sections from WordPress.

## WordPress installation

1. Upload `seven-s-racing` to `wp-content/themes/`.
2. Upload `seven-s-core` to `wp-content/plugins/`.
3. Activate **Seven S Core**, then activate **Seven S Racing**.
4. Visit **Settings → Permalinks** and save once.
5. Create and assign a menu named **Primary** if custom labels are desired.
6. Add real sponsor URLs/logos and verified race details before launch.

See [docs/deployment.md](docs/deployment.md) for the staging-to-production workflow.

