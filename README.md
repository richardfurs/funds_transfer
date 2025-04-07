## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Set up

1. Run `docker exec -it funds_transfer-php-1 bash` to bash in container
2. Run `composer install` to install dependencies
3. Run `php bin/console doctrine:database:create` to create database
4. Run `php bin/console doctrine:migrations:migrate` to migrate database
5. Run `php bin/console doctrine:fixtures:load` to seed database
6. Run `php bin/console app:update-rates` to create initial currency rates
7. Run `service cron start` to start cron service
8. Run `php bin/console doctrine:database:create --env=test` to create test database
9. Run `php bin/console doctrine:migrations:migrate --env=test` to migrate test database

## Tests

1. Run `./vendor/bin/phpunit tests/Controller/ClientControllerTest.php` to test Client controller
2. Run `./vendor/bin/phpunit tests/Controller/AccountControllerTest.php` to test Account controller

## API

#### GET

1. `localhost/clients/{id}/accounts`
2. `localhost/accounts/{id}/transactions?offset={offset}&limit={limit}`

#### POST

`localhost/accounts/transfer`

```
{
    'from_account_id': 1,
    'to_account_id': 2
    'currency': USD
    'amount': 100
}
```