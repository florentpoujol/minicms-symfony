# Mini CMS - Symfony

The point of this project is both to practice and showcase my capabilities in web development with Symfony & Friends, by creating a basic CMS.

## Current application features

- User entity with built-in registration/login/logout
- 3 roles: regular user, that can only post comments on articles, writers, that write/edit their articles and admins that can do every thing
- access to a "profile" (at `/profile`) for all logged-in users (not editable)
- access to an admin section for the writers and admins with a list of articles (at `/admin/articles`)
- a form to create or edit an article (at `/admin/articles/create` and `/admin/articles/{slug}/edit`)
- a blog page that displays the currently published articles, with an excerpt of their content (at `/blog`)
- a page to display a single article (at `/blog/{slug}`), writers and admins can see non-published articles
- an "AuditLog", a database entity that track changes to other entities

### Used Symfony features/components

- Router + Controller + Twig views
- Built-in User authentication
- Forms
- Application tests
- Custom normalizer (almost)
- Translations
- Doctrine 
  - entities with relations + repositories
  - entities lifecycle events
  - Fixtures

## Setup

You must have Docker and Docker Compose installed locally.

- build the docker image with `docker compose build`
- the stack will use the port `8080` by default. If you need another port, you can set it with an `APP_PORT` env var in your `.env` file
- up the stack with `docker compose up -d`
- then install the composer dependencies via a container `./docker/composer install`

### Run the tests

- create the database for the `test` environment: `./docker/symfony doctrine:database:create --env=test`
- run the migrations and the seeders for the `test` environment: `./docker/symfony doctrine:migrations:migrate --env=test`
- run the seeders for the test environment:  `./docker/symfony doctrine:fixtures:load --env=test`
- run the tests `./docker/composer test`

### Run in the browser

- create the database for the `dev` environment: `./docker/symfony doctrine:database:create`
- run the migrations and the seeders for the `dev` environment: `./docker/symfony doctrine:migrations:migrate`
- run the seeders for the `dev` environment:  `./docker/symfony doctrine:fixtures:load`
- the site is accessible at http://localhost:8080 (or whateveer port you set with the `APP_PORT` env var in your `.env` file).
- the fixtures add three users
  - `user@example.com`, password is `user`
  - `writer@example.com`, password is `writer`
  - `admin@example.com`, password is `admin`

### Run all the quality tools

Quality tools like Rector, PHP-CS-Fixer and PHPStan are installed in the `tools` folder. Instal them with `./docker/composer install --working-dir=./tools`.

Then run them all as well as PHPUnit with the `./docker/composer all` command.  
Each also have their own composer alias.
