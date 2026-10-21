# ArtisanConnect

A web marketplace that connects local artisans with clients. Artisans sell handmade products and host workshops; clients can shop, book workshop seats, follow events and rate what they've tried.

Built with **Symfony 6.4** (PHP 8.1+), Doctrine ORM and Twig.

## Features

**Clients**
- Browse the shop, add products to a cart and check out
- Track their orders and cancel them
- Reserve seats in artisan workshops (*ateliers*)
- Browse events with live search
- Rate products and workshops

**Artisans**
- Manage products and workshops
- Follow incoming orders, update their status and assign a delivery person (*livreur*)
- Manage their delivery team

**Administrators**
- Dashboard with platform statistics
- Manage users, products, events and orders

**Accounts**
- Sign up with role selection (client / artisan) and email confirmation
- Form login with CSRF protection and role-based redirection
- Password reset by email verification code

## Tech stack

| Layer      | Tools                                                  |
|------------|--------------------------------------------------------|
| Backend    | Symfony 6.4, Doctrine ORM 3, Symfony Security, Mailer  |
| Frontend   | Twig, Bootstrap, Stimulus / Turbo (AssetMapper)        |
| Uploads    | VichUploaderBundle                                     |
| Pagination | KnpPaginatorBundle                                     |
| Database   | MySQL                                                  |

## Project structure

```
src/
├── Controller/   # HTTP controllers (shop, cart, checkout, orders, workshops, admin…)
├── Entity/       # Doctrine entities (User, Produit, Atelier, Reservation, Orders…)
├── Repository/   # Doctrine repositories
├── Form/         # Symfony form types
├── Service/      # Payment service
├── Security/     # Login success handler (role-based redirect)
└── Command/      # Console commands
templates/        # Twig views, grouped by feature
config/           # Bundles, routes and package configuration
```

## Getting started

Requirements: PHP 8.1+, Composer, MySQL.

```bash
git clone https://github.com/AyaTlijani/ArtisanConnect.git
cd ArtisanConnect
composer install
```

Create a `.env.local` file with your own values (it is git-ignored):

```dotenv
DATABASE_URL="mysql://user:password@127.0.0.1:3306/artisan_connect?serverVersion=8.0.32&charset=utf8mb4"
MAILER_DSN=smtp://user:password@smtp.example.com:587
APP_SECRET=your_secret
```

Then create the database and start the server:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
symfony server:start   # or: php -S localhost:8000 -t public
```
