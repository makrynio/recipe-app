# Recipe App

A recipe website where visitors can search and filter recipes, and registered users can save favourites and rate and comment on recipes. Built with plain PHP 8, MySQL, HTML5, CSS3 and JavaScript (no front-end frameworks). Group project for Networks and Web Technology (CSCK543), MSc Computer Science, University of Liverpool, February to March 2026.

## Team and contributions

Group D, four members:

| Member | Main contributions |
| --- | --- |
| **Thomas Makryniotis** | Authentication and account system (registration, login, logout, sessions, account page), database connection and configuration include, authentication helper include, shared header and footer includes, saved favourites, recipe rating and comments, authentication flow documentation, meeting minutes, group video presentation, submission package |
| **Mircea Muresan** | Front end and most of the JavaScript |
| **Alen Rasulov** | Search and filtering |
| **Edon Kelmendi** | Database schema and sample data |

Passwords are hashed with `password_hash()` and checked with `password_verify()`. Database access in the authentication, favourites and ratings code uses `mysqli` prepared statements.

## Features

- Browse, search and filter recipes by category, with search suggestions
- Recipe pages with ingredients and steps
- User registration, login, logout and an account page
- Save and remove favourite recipes
- Rate recipes with a star widget and leave a comment
- Accessibility statement page; form errors announced with ARIA roles

## Tech stack

PHP 8, MySQL 8, HTML5, CSS3, vanilla JavaScript. Developed and run locally on XAMPP.

## Running locally

1. Install [XAMPP](https://www.apachefriends.org/) (or any Apache, PHP 8 and MySQL 8 stack) and start Apache and MySQL.
2. Copy this folder into the web root (`htdocs`).
3. Create the database and load the sample data, for example with the MySQL command line:

   ```bash
   mysql -u root < database/schema.sql
   mysql -u root < database/seed.sql
   ```

4. Copy `config/db.example.php` to `config/db.php` and set your database credentials. `config/db.php` is git-ignored.
5. Open `http://localhost/recipe-app-main/` (adjust to your folder name).

Sample users (see `database/seed.sql`) share the demo password `password`. They are for local testing only.

## Project structure

```
config/      database credentials template
database/    schema.sql and seed.sql
includes/    db connection, auth, favourites, ratings, header and footer
pages/       login, register, account, search, results, recipe, accessibility
css/ js/     styles and scripts
docs/        authentication and session flow
```

See [docs/authentication-flow.md](docs/authentication-flow.md) for a walkthrough of how registration and sessions work.
