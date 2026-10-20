PRACTICAL 13 — PHP JSON API AND FETCH CRUD

Files added:
- event_api.php: JSON endpoint for GET (list), POST (add), PUT (update), DELETE (delete). Admin-only; validates input and uses prepared PDO statements.
- EventAPI.php: browser page that consumes the API with Fetch and updates the table without a full page reload.

Run:
1. Extract the ZIP into your XAMPP htdocs folder, preserving the included folder structure.
2. Start Apache and MySQL and use the same studenthub_db database configured in db.php.
3. Log in with an administrator account.
4. Open After Login/EventAPI.php (the folder path in your local project may include Practical 10).
5. Add, edit, and delete a test event; the table updates without reloading.

Database: Reuses the existing events table (id, title, category, event_date, created_at). No additional table or SQL import is required if Practical 12's events table is already set up. The API uses the existing project's PDO connection in db.php so it matches the current project architecture.
