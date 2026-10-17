# ISSP Website Developed by TechEmpery

Custom website for the Insurance Sector Strengthening Program (ISSP),
built with HTML, CSS, Tailwind CSS, JavaScript, PHP, and MySQLi.

## What this skeleton gives you

A working lightweight-MVC site:

- Front controller + router (`public/index.php`, `app/Core/Router.php`)
- mysqli database wrapper with prepared statements (`app/Core/Database.php`, `app/Core/Model.php`)
- Session-based auth with roles, covering both CMS staff and the stakeholder
  portal (`app/Core/Auth.php`)
- A fully wired reference module (**Pages**) showing the complete pattern:
  public controller -> model -> view, and the matching Admin CRUD screens.
- Every other module described in the file structure (Posts, Resources,
  Events, Team, Partners, Newsletter, Contact, Users, Organizations, Media,
  Settings) scaffolded with a working model and a starter controller/view —
  copy the Pages pattern into each as you build it out.
- The stakeholder document-access gate implemented end to end
  (`app/Controllers/Stakeholder/DocumentController.php`): checks
  `resources.visibility` and `resource_access_grants`, logs every download to
  `resource_download_logs`, and only then streams the file from `storage/`
  (which sits outside the web root).

## Getting started

1. Import the schema:
   `mysql -u root -p issp_db < database/issp_database_schema.sql`
2. Copy `.env.example` to `.env` and fill in your DB credentials.
3. Point your web server's document root at `public/`.
   Quick local run: `php -S localhost:8000 -t public`
4. Install PHPMailer (used by `app/Services/Mailer.php`):
   `composer install`
5. Build Tailwind CSS:
   `npm install && npm run build` (or `npm run watch` while developing)
   The views also load the Tailwind CDN build as a fallback so the site
   renders immediately without a build step — swap to the compiled
   `public/assets/css/tailwind.css` for production.
6. Create your first admin user (no registration screen is exposed on
   purpose — staff accounts are provisioned directly):
   ```sql
   INSERT INTO users (user_type, first_name, last_name, email, password_hash, status, email_verified_at)
   VALUES ('staff', 'Admin', 'User', 'admin@issprogram.com', '<bcrypt-hash>', 'active', NOW());

   INSERT INTO user_roles (user_id, role_id)
   SELECT u.id, r.id FROM users u, roles r
   WHERE u.email = 'admin@issprogram.com' AND r.name = 'super_admin';
   ```
   Generate the bcrypt hash with: `php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"`

## Folder structure

See `ISSP_File_Structure.md` (delivered separately) for the full annotated
tree and the reasoning behind it. In short: `public/` is the only
web-exposed folder; `app/`, `views/`, `storage/`, and `.env` all live outside
the document root.

## Extending a stub module

Every stub Admin controller (e.g. `Admin/PostController.php`) follows the
same shape as `Admin/PageController.php`. To finish a module:

1. Flesh out the model's query methods in `app/Models/`.
2. Copy `views/admin/pages/{index,create,edit}.php` into the module's view
   folder and adjust the fields.
3. Copy the controller methods from `Admin/PageController.php` and point
   them at the new model/view.

This keeps every module consistent without a heavier framework.
