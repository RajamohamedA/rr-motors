# RR ERP source review — 18 October 2026

The attached source includes confirmed fixes and regression tests. It is not a certification that every ERP workflow is production ready. Use a staging copy and a verified database backup before upgrading.

## Verified results

| Check | Result |
| --- | --- |
| PHPUnit, SQLite test database | 47 passed; 117 assertions |
| PHP syntax | 432 source/template PHP files; no lint errors |
| Compiled Blade syntax | 223 compiled files; no lint errors |
| Production frontend build | Passed |
| Route caching and Blade compilation | Passed |
| Composer manifest/lock validation | Valid |
| Composer security audit | No reported advisories |
| npm security audit | No reported vulnerabilities |

Tests include existing authentication/profile tests and new company/branch access, inactive account, onboarding, stock movement, and inclusive pricing regressions. SQLite coverage does not prove MySQL locking or concurrent posting behavior.


## Remaining launch blockers and limitations

1. Run every migration against the actual production MySQL version on a restored staging backup. This environment could not start a usable MySQL server. Review new deferred foreign keys against existing data and migration rollback plans.
2. Run concurrent sales, returns, stock transfers, edits and double-submit tests. Verify stock, ledgers, outstanding balances and invoice numbering after retries and competing requests. The new stock regression tests are sequential.
3. Browser end-to-end and PDF/thermal print visual checks were not run. Browser installation failed; GD was unavailable in the temporary PHP runtime. Test A4, A5, quick bills, GST/cash bills, logos, long descriptions and multipage documents using your actual browser/printer and PDF renderer.
4. Settings storage is not yet consistently connected to sales creation and printing. Verify each setting's actual downstream effect before exposing it as operational configuration.
5. User does not implement the email verification contract. The verified middleware alone therefore does not enforce a full email verification workflow. Decide and implement onboarding/verification behavior before relying on it.
6. The legacy sales return backfill links a sale item only when there is one matching item. Ambiguous historical rows remain unlinked and need manual reconciliation.
7. Securely provision the first Super Admin separately. No production default account/password is supplied. Validate Super Admin/company administrator/branch user permissions with representative accounts.
8. Review fractional quantities if used: database stock precision and model casts are not uniformly aligned. Reconcile before relying on sub-unit inventory.

## Deployment workflow

1. Keep the original ZIP and take an encrypted database/uploads backup; prove restore works.
2. Install PHP 8.4.1 or newer within the supported 8.x range and required extensions, including GD, PDO MySQL, mbstring, XML, curl, zip and fileinfo. Use a Node version supported by Vite 8.
3. Run composer install --no-dev --optimize-autoloader and npm ci, then npm run build. Deploy only through the public web root.
4. Copy .env.example to .env; configure the actual URL, database, mail, session, queue and cache. Generate a new application key only for a NEW installation. Preserve the existing APP_KEY on upgrades.
5. Ensure storage and bootstrap/cache are writable. Configure storage links, HTTPS, queue workers, scheduler and backups for the features you use. Replace log mail with a working mail transport.
6. On staging, run php artisan migrate, then the test matrix below. Do not use migrate:fresh on an existing installation. Review schema changes before production migrations.
7. Run php artisan optimize after configuration is final; restart queue workers. Keep APP_DEBUG=false. Configure monitoring and a rollback release.

## Manual acceptance matrix

| Area | Required scenarios |
| --- | --- |
| Isolation | Change URL IDs, session branch, submitted company/store IDs; test foreign companies and revoked branch access |
| Roles | Super Admin, Company Admin, assigned branch operator, read-only user, disabled account |
| Onboarding | New business remains pending; approval/rejection; duplicate email/company details; login and recovery |
| Billing | GST-inclusive 200 remains total 200; cash 200 remains 200; zero GST fallback; discounts; rounding; paid/partial/unpaid |
| Inventory | Shop/godown transfers, insufficient stock, foreign locations, concurrent deductions, returns and cancellations |
| Documents | Create/edit/show, complete-and-print, PDF, A4/A5/thermal, CGST/SGST and applicable interstate behavior |
| Other modules | Purchases/returns, quotations/conversion/shortage transfer, customers/suppliers, accounts/reports/import/export |
| Operations | Email delivery, uploads, queue failure/retry, scheduler, session expiry, backup restore and deployment rollback |

## Cleanup policy

Only confirmed empty/test/diagnostic source files were removed. Uncertain legacy views, models and styles are retained. The ZIP excludes vendor, node_modules, environment secrets, built assets, caches, logs, local databases and temporary downloaded runtimes. Install dependencies and build assets from the included lockfiles.
