# IDP Monitoring Collector

A small tool that consumes an **identity provider (IDP) monitoring REST API** for TV operators (MSOs), stores the incidents in **MySQL** and generates reports by date range.

## How it works

1. The user logs in through a web form; the tool requests an auth token from the monitoring API.
2. Every hour, it fetches the **MSO list** and the **last actions** from the API.
3. It keeps only the problems: operators and countries where authentication (AuthN) or authorization (AuthZ) is not `OK`, or where the average response time exceeds 2 seconds.
4. A PHP back end saves those records in MySQL using PDO with prepared statements and multi-row inserts.
5. The user can query the stored incidents between two dates and times.

## Tech stack

JavaScript · jQuery (AJAX) · Bootstrap · PHP · PDO · MySQL

## Getting started

1. Create a MySQL database named `idp_monitoring` with the `msolist` and `lastactions` tables.
2. Set your database connection in `db_save.php`.
3. Serve the folder with a PHP-enabled web server (for example, `php -S localhost:8000`).
4. Open `http://localhost:8000/test.html` and log in with your monitoring API credentials.

## Project files

| File | Purpose |
| --- | --- |
| `test.html` | Login form, scheduled data collection and report form |
| `db_save.php` | Data model, filtering rules and MySQL persistence |
