# Ctrl+Flow — Barangay Drainage Blockage Report System

A PHP/MySQL web app for residents to report clogged drains and sewage
blockages, and for barangay cleanup crews / admins to track, escalate, and
resolve them.

This codebase was converted from a TB (tuberculosis) patient case-management
system (`WEBSYS_FINAL_PROJECT`) to this domain. The auth, barangay-routing,
notification, audit-log, CSV import/export, and PDF-generation framework was
kept; everything patient/medication/referral-specific was redesigned around
blockage reports.

## Roles
- **super_admin** — manages all users, barangays, and reports; views analytics; generates PDF reports.
- **crew** — assigned to one barangay; manages reports and escalations for that barangay.
- **citizen** — reports blockages and tracks their own report status.

## Core entities
- `blockage_reports` — the report itself: barangay, landmark, material type, severity, spill status, photo, status (active → under_review → cleared).
- `escalations` — a report escalated to the City Engineering Office (or a neighboring barangay) when it needs equipment beyond what the local crew can handle.
- `notifications`, `audit_logs`, `import_logs` — unchanged in shape from the original system, repointed at reports instead of patients.

## Setup
1. Create a MySQL database named `ctrlflow` and import `database/ctrlflow.sql`.
2. Generate a super_admin password hash:
   ```
   php -r "echo password_hash('your_admin_password', PASSWORD_DEFAULT);"
   ```
   Paste it into `database/seed_admin.sql` and run that file too.
3. `composer install` (installs PHPMailer + Dompdf — already listed in `composer.json`).
4. Edit `config/db.php` with your DB credentials, and `config/env.php` with
   your SMTP sender details — **do not commit real credentials**.
5. Deploy the project folder as `CTRLFLOW` (e.g. `htdocs/CTRLFLOW`), so it
   matches the hardcoded `/CTRLFLOW/public/...` links throughout the app and
   `public/.htaccess`'s `RewriteBase`. If you deploy under a different folder
   name, you'll need to update both.
6. Visit `http://localhost/CTRLFLOW/public/login.php` and log in as the
   super_admin you seeded.
7. (Optional) Point a cron job at `cron/send_notifications.php` every few
   minutes to flush scheduled notifications.

## What changed from the original TB system
| Original | Ctrl+Flow |
|---|---|
| `patients` table | `blockage_reports` |
| `referrals` table | `escalations` |
| Patient / Health Worker / Super Admin | Citizen / Crew / Super Admin |
| Medication adherence tracking | *(removed — no equivalent)* |
| Contact tracing | *(removed — no equivalent)* |
| Staff follow-up (missed meds) | Stale-report follow-up (unresolved blockages) |

## Barangay list
`config/barangays.txt` ships with the original project's Baguio City
barangay list. Edit that file to match the barangay(s) you're actually
deploying for.
