# Legacy Data Migration Guide

How to move real member data from the old app (`icenig.org.ng`) into this app using the `legacy:migrate` Artisan command. This is an operational runbook — for the reasoning behind the field mappings and status decisions, see the original migration plan.

Do not skip steps or run `--force` before a clean dry run. This command writes real member records, payments, and files — treat it like a production database migration, because it is one.

## Overview

```
Old app DB (production)          This app
─────────────────────           ─────────────────
users                    ──▶     users
i_c_e_n_memberships      ──▶     membership_tiers
model_has_roles (admin)  ──▶     model_has_roles (admin)
i_c_e_n_user_memberships ──▶     user_memberships
  + EAV form data         ──▶     (reconstructed into typed columns)
payments                 ──▶     payments
files + uploaded files   ──▶     member_files + media library
```

Six steps, always run in this order (the command enforces it even if `--only` is given out of order): **users → tiers → roles → memberships → payments → files**. Each later step depends on the id mappings built by the earlier ones.

## Prerequisites

- A restorable SQL dump of the old app's **live production** database (not a stale local copy).
- Access to the old app's `storage/app/public` directory (for the `files` step) — either on this machine or copied here.
- Local MySQL/MariaDB with permission to create a new database.

## Phase A — Get the real data onto this machine

1. **Confirm which database is actually live.** The old app's config references two possible names (`appodnigeria_portal` in `.env`, `icenigeria2024_portal` in `.env.portal`). Check with your hosting provider which one the production site actually uses — do not guess.
2. **Export it.** Via SSH: `mysqldump -u <user> -p <database> > icen_legacy.sql`. Via cPanel/phpMyAdmin: use the Export tool.
3. **Restore it into a separate database** — never into this app's own `icen_portal` database:
   ```
   mysql -u root -e "CREATE DATABASE icen_legacy_import CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u root icen_legacy_import < icen_legacy.sql
   ```
4. **Get the uploaded files.** If you're migrating on the same machine as the old app, you already have them at `C:\laravel\icenig.org.ng\icenig.org.ng\storage\app\public`. If not, copy that directory here first — the migration reads real files from disk, not from the database.

## Phase B — Configure this app

In `.env` (not `.env.example`), confirm/set:

```
LEGACY_DB_HOST=127.0.0.1
LEGACY_DB_PORT=3306
LEGACY_DB_DATABASE=icen_legacy_import
LEGACY_DB_USERNAME=root
LEGACY_DB_PASSWORD=
LEGACY_STORAGE_PATH="C:/laravel/icenig.org.ng/icenig.org.ng/storage/app/public"
```

`LEGACY_STORAGE_PATH` must use forward slashes even on Windows — PHP's dotenv parser treats backslashes as escape sequences and will fail to parse the file otherwise.

Sanity-check the connection before running anything:

```
php artisan tinker --execute="dd(\DB::connection('legacy')->table('users')->count())"
```

If that errors, fix the `LEGACY_DB_*` values before continuing — every step below depends on this connection.

## Phase C — Dry run (always do this first)

```
php artisan legacy:migrate
```

With no `--force` flag, the command **writes nothing**. It walks every table, applies every mapping, and prints a summary table of what it *would* do:

```
+-------------+----------+---------+---------------------+
| Entity      | Imported | Skipped | Flagged for review  |
+-------------+----------+---------+---------------------+
| users       | 842      | 3       | 0                   |
| tiers       | 3        | 0       | 0                   |
| roles       | 4        | 0       | 0                   |
| memberships | 815      | 12      | 47                  |
| payments    | 1203     | 8       | 0                   |
| files       | 1109     | 6       | 22                  |
+-------------+----------+---------+---------------------+
```

Below the table, it lists every **skipped** row's reason (bad/missing data — nothing was importable) and every **flagged** row's reason (imported, but needs a human to check it — e.g. an EAV field that didn't match a known column, or an uploaded file whose category couldn't be inferred).

**Read the flagged/skipped lists before proceeding.** A large skipped count for `users` (missing emails) or a large flagged count for `memberships` (unmapped fields) usually means the old form had custom fields per tier that aren't in the field-mapping table yet — worth a second look at `app/Services/LegacyMigration/FieldMapper.php` before running for real.

You can scope the dry run to one step at a time while investigating:

```
php artisan legacy:migrate --only=users
php artisan legacy:migrate --only=users,tiers,memberships
```

## Phase D — Verify against a scratch database first

Don't point `--force` at this app's real `icen_portal` database on the first live attempt. Instead:

1. Create a scratch database (e.g. `icen_portal_migration_test`), run this app's migrations against it, and temporarily point `DB_DATABASE` at it.
2. Run the real import: `php artisan legacy:migrate --force`
3. Spot-check by hand:
   - Pick 5–10 real, known members and compare every field against the old dump.
   - Confirm membership numbers (`membership_number`) match exactly — this is the one thing real members will notice if it's wrong.
   - Confirm no email collision silently merged two different people into one account.
   - Open a handful of migrated files through Admin → User Memberships → a member → Documents relation manager and confirm they actually open.
   - Compare `SUM(payments.amount)` between old and new for a sample date range.
4. Only after that looks right, switch `DB_DATABASE` back to `icen_portal` and run for real.

## Phase E — Run for real

```
php artisan legacy:migrate --force
```

This is idempotent — it's safe to re-run after fixing an issue. Every entity is keyed on a natural key (`email` for users, `membership_number` for memberships, `transaction_reference` for payments), so re-running never creates duplicates, it just updates existing rows.

Run it during a maintenance window if possible, and take a backup of `icen_portal` immediately beforehand regardless of how many times it's been tested.

## Phase F — After the migration

- **Nothing is forced on members.** They log in with their existing email and password, same as before — passwords are carried over as-is (see "How passwords are handled" below).
- **Work the flagged list.** Everything the dry run/live run flagged for manual review needs a person to look at it — mostly EAV fields that landed in a membership's `extra_fields` JSON column (visible on the membership's admin edit page) because they didn't match the known field-mapping table, and uploaded files whose type couldn't be inferred (landed as `other`).
- **Once confident, tear down the temporary connection:**
  - Remove `LEGACY_DB_*` and `LEGACY_STORAGE_PATH` from `.env`.
  - Drop the `icen_legacy_import` database.
  - The `legacy` connection block can stay in `config/database.php` and the `app/Legacy/*` models can stay in the repo — they're inert without the env vars and cost nothing to keep in case the import needs to run again later.

## How passwords are handled

The old app also uses Laravel/bcrypt, so its password hashes are portable. `UserImporter` checks each password with `Hash::isHashed()`; when it's a recognized hash, it's copied over unchanged — Laravel's `hashed` cast on `User::password` also independently protects against re-hashing an already-hashed value. Members do **not** need to reset their password after migration.

## Command reference

```
php artisan legacy:migrate [--force] [--only=STEP,STEP,...]
```

| Option | Effect |
|---|---|
| *(none)* | Dry run — reports what would happen, writes nothing. |
| `--force` | Actually writes to the database. |
| `--only=users,tiers,roles,memberships,payments,files` | Limit to a subset of steps (still runs in dependency order). |

## Field mapping reference

The old app stored applicant-submitted data as EAV (one row per field per application) rather than typed columns. `app/Services/LegacyMigration/FieldMapper.php` is the single source of truth for how old field names map to new `user_memberships` columns:

| Old EAV field name | New column |
|---|---|
| `first_name` / `middle_name` / `last_name` | same |
| `gender`, `date_of_birth`, `place_of_birth`, `nationality`, `marital_status` | same |
| `state_of_origin`, `local_government_area` | same |
| `residential_address` | same |
| `current_postal_address` | `postal_address` |
| `next_of_kin_name`, `next_of_kin_address` | same |
| `primary_school`, `primary_school_year_passed_out` | `primary_school`, `primary_school_year` |
| `secondary_school`, `secondary_school_year_passed_out` | `secondary_school`, `secondary_school_year` |
| `higher_instition`, `higher_instition_course_offer`, `higher_instition_year_passed_out`, `higher_instition_final_grade`, `higher_instition_second_degree` | `higher_institution`, `higher_institution_course`, `higher_institution_year`, `higher_institution_grade`, `higher_institution_second_degree` |
| `do_you_belong_to_any_professional_institute_like_ours` (Yes/No) | `belongs_to_other_institute` (boolean) |
| `if_yes_name_of_the_institute`, `your_status_in_the_institute`, `quote_your_membership_number_of_the_institue` | `other_institute_name`, `other_institute_status`, `other_institute_membership_number` |
| `year_of_qualification` | same |
| `declaration_signature` | `declaration_name` |
| `passport`, `primary/secondary/higher_instition_certificate` | not columns — reconstructed as files by `FileImporter` |
| anything else | `extra_fields` JSON column (safety net, nothing is silently dropped) |

**Status mapping** (old free-text `status` → new `MembershipStatus` enum):

| Old | New |
|---|---|
| `approved`, no `expired_at` or future | `active` |
| `approved`, past `expired_at` | `expired` |
| `pending` (or anything unrecognized) | `pending_review` |

## Troubleshooting

**`SQLSTATE[HY000] [1049] Unknown database 'icen_legacy_import'`**
The `legacy` connection can't find the database — you haven't restored the dump yet, or `LEGACY_DB_DATABASE` doesn't match what you named it.

**`Failed to parse dotenv file. Encountered an unexpected escape sequence`**
`LEGACY_STORAGE_PATH` has backslashes in `.env`. Use forward slashes: `C:/laravel/icenig.org.ng/...`.

**Large `skipped` count on `files`**
Usually means `LEGACY_STORAGE_PATH` doesn't point at the right directory, or the old `files.path` values are stored in a format the importer doesn't expect. Check the printed skip reasons — they include the exact absolute path it tried.

**A membership is missing fields you know the applicant submitted**
Check that membership's `extra_fields` column in the admin panel — the value is probably there under its original old field name, just not yet added to `FieldMapper::COLUMN_MAP`. Add the mapping and re-run (`--force` is safe to re-run, it updates existing rows via `updateOrCreate`).
