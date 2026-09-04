# Staging and deployment

## Recommended path

1. Provision **Seven S Racing** in the GoDaddy Managed WordPress Developer plan.
2. Keep `7s-racing.com` away from the new install until the staging review is approved, or leave the production install on a holding page.
3. In GoDaddy, open the site's **Settings** and create its built-in staging site.
4. Create an SFTP login for the staging environment.
5. Upload only these versioned directories:
   - `wp-content/themes/seven-s-racing`
   - `wp-content/plugins/seven-s-core`
6. In staging WordPress, activate the plugin and theme. Add Home, Racing, and Merch pages; set Home as the static homepage; assign the Racing and Merch templates.
7. Add verified sponsor links/logos, next race, results, biography, social profiles, and approved copy.
8. Review desktop and mobile, links, forms, media rights, accessibility, cache behavior, and backups.
9. Use GoDaddy's staging push only after approval. Confirm whether the push will overwrite the production database/content before running it.
10. Connect or confirm `7s-racing.com`, enable SSL, remove any holding page, and turn search indexing on only at launch.

## GitHub structure

Create a private repository named `seven-s-racing` and push this directory as its initial main branch. WordPress core and uploads stay outside Git. The included workflow validates PHP syntax when a runner provides PHP; deployment remains manual until PDC stores environment-specific SFTP credentials as GitHub secrets.

Suggested future secrets:

- `STAGING_SFTP_HOST`
- `STAGING_SFTP_USER`
- `STAGING_SFTP_PASSWORD`
- `STAGING_SFTP_PORT`
- `STAGING_WP_CONTENT_PATH`

Do not commit credentials or `wp-config.php`.

## Launch checks

- Replace all placeholder race data.
- Confirm permission to publish each photo and sponsor logo.
- Verify every sponsor URL and social link.
- Create Nick's account with the **Seven S Owner** role; retain a separate PDC administrator.
- Test image uploads and blog publishing from Nick's phone.
- Add backups and security controls supported by the GoDaddy plan.
- Leave WooCommerce uninstalled until fulfillment and inventory ownership are decided.

