# Equipment Alarm System

A web application built with PHP, MySQL, Bootstrap, and JavaScript for registering and managing alarms related to equipment.

## Overview

This system provides a straightforward interface for:

-   **Managing Equipment:** Performing CRUD (Create, Read, Update, Delete) operations on equipment records (including Name, Serial Number, Type).
-   **Managing Alarms:** Performing CRUD operations on alarm definitions (including Description, Classification, and association with specific Equipment).
-   **Monitoring & Control:** Viewing currently active alarms, activating/deactivating alarms, and viewing historical alarm activation data.
-   **System Logging:** Tracking key actions performed within the application.

## Features

-   **Dashboard (`index.php`):** Overview displaying counts of equipment, total alarms, active alarms, and urgent active alarms. Also highlights the top 3 most frequently triggered alarms.
-   \*\*Equipment Management (`equipment/`):
    -   List (`list.php`): Displays all equipment with sorting capabilities (client-side JS).
    -   Create (`create.php`): Form to add new equipment with validation (including unique serial number check).
    -   View (`view.php`): Shows details of a single equipment item and lists its associated alarms.
    -   Edit (`edit.php`): Form to modify existing equipment with validation.
    -   Delete (`delete.php`): Confirmation page to delete equipment (prevents deletion if associated alarms exist).
-   \*\*Alarm Management (`alarms/`):
    -   List (`list.php`): Displays all alarm definitions with current status (Active/Inactive) and sorting capabilities (client-side JS).
    -   Create (`create.php`): Form to add new alarms, associate them with equipment, and set classification.
    -   View (`view.php`): Shows details of a single alarm, its current status, associated equipment, and full activation history (including calculated durations).
    -   Edit (`edit.php`): Form to modify existing alarm definitions.
    -   Delete (`delete.php`): Confirmation page to delete alarms (prevents deletion if currently active, removes associated activation history).
    -   Manage (`manage.php`): Processes activate/deactivate requests. Displays alarms grouped by classification (Urgent, Emergency, Ordinary) with controls to toggle their status. Sends simulated email notifications via `sendEmail()` for 'Urgent' alarm activations.
    -   Activated View (`activated.php`): Displays _only_ currently active alarms. Includes client-side filtering by description and sorting capabilities. Also shows the top 3 historically most triggered alarms.
-   \*\*System Logs (`logs/view.php`):
    -   Displays a paginated list of all actions logged by the `logAction()` function (e.g., creations, updates, deletions, activations, deactivations, page views).
-   \*\*Shared Components (`includes/`):
    -   `header.php`/`footer.php`: Common HTML structure and navigation.
    -   `config.php`: Database connection details (`mysqli`).
    -   `functions.php`: Contains helper functions for logging (`logAction`), email simulation (`sendEmail`), database lookups (`getEquipmentById`, `getAlarmById`), status checking (`getAlarmStatus`), top alarm retrieval (`getTopTriggeredAlarms`), input sanitization (`sanitizeInput`), and feedback display (`showError`, `showSuccess`).
-   \*\*Client-Side Enhancements (`assets/js/scripts.js`):
    -   Bootstrap Tooltip initialization.
    -   Live filtering of the activated alarms table by description.
    -   Client-side sorting for tables with the `.sortable-table` class.
    -   `confirm()` dialogs for delete and status change actions.
-   **Security:**
    -   Uses prepared statements (`mysqli_prepare`, etc.) to prevent SQL injection.
    -   Uses `htmlspecialchars()` on output to prevent Cross-Site Scripting (XSS).

## Technology Stack

-   **Backend:** PHP (Procedural style)
-   **Database:** MySQL
-   **Frontend:** HTML, CSS, Bootstrap 5
-   **JavaScript:** Vanilla JS (for sorting, filtering, confirmations)

## Prerequisites

-   A web server with PHP support (e.g., Apache, Nginx with PHP-FPM)
-   PHP (Version used during development, e.g., 7.4+ or 8.x)
-   MySQL Database Server (e.g., 5.7+ or 8.0+)
-   Web Browser (Chrome, Firefox, Edge, etc.)

## Installation & Setup

1.  **Clone the repository:**
    ```bash
    git clone <your-repository-url>
    cd equipment-alarm-system
    ```
2.  **Database Setup:**
    -   Ensure your MySQL server is running.
    -   Create a MySQL database (the default name used in `includes/config.php` and `database.sql` is `equipment_alarm_system`). You can use a tool like phpMyAdmin or the MySQL command line:
        ```sql
        CREATE DATABASE IF NOT EXISTS equipment_alarm_system;
        ```
    -   Import the database schema and initial data from `database.sql`:
        ```bash
        # Using command line (replace <username>)
        mysql -u <username> -p equipment_alarm_system < database.sql
        ```
        _(Alternatively, import `database.sql` using a GUI tool like phpMyAdmin)_
3.  **Configure Database Connection:**
    -   Edit the file `includes/config.php`.
    -   Update the `DB_SERVER`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_NAME` constants with your MySQL connection details if they differ from the defaults (`localhost`, `root`, ``, `equipment_alarm_system`).
4.  **Web Server Configuration:**
    -   Configure your web server (e.g., Apache Virtual Host or Nginx server block) to point its document root to the project's root directory (the one containing `index.php`).
    -   Ensure the web server has the necessary permissions to read the project files.
5.  **Permissions (Optional):**
    -   Depending on your server setup, you might need to ensure the web server process has write permissions for logging if specific log files were used (though this project logs to the database `system_logs` table, so file permissions are less likely needed).

## Running the Application

-   Access the application through your web browser by navigating to the URL configured for your web server (e.g., `http://localhost/` or `http://equipment-alarm.test/` if using virtual hosts).
-   The main dashboard will be displayed at the root URL (`index.php`).

## Project Structure

```
/
├── alarms/             # Alarm CRUD, management, activation view PHP files
│   ├── activated.php
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   ├── list.php
│   ├── manage.php
│   └── view.php
├── assets/             # Frontend assets
│   ├── css/            # Custom CSS files (if any)
│   └── js/             # JavaScript files (scripts.js)
├── equipment/          # Equipment CRUD PHP files
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   ├── list.php
│   └── view.php
├── includes/           # Shared PHP files
│   ├── config.php      # Database configuration
│   ├── footer.php      # Common page footer
│   ├── functions.php   # Helper functions
│   └── header.php      # Common page header & navigation
├── logs/               # Log viewing PHP file
│   └── view.php
├── tests/              # Test files
│   └── Tester.php
│   └── TestRunner.php
├── memory-bank/        # Adaptive Memory Bank (ignored by .cursorrules)
├── .gitignore
├── .cursorrules
├── README.md           # This file
├── database.sql        # Database schema and initial data
└── index.php           # Main entry point / Dashboard
```
