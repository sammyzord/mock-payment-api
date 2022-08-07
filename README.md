# Simplified payment API (Mock)

## Run the project
Use the following commands:
```
$ docker-compose run --rm app composer install
$ docker-compose up
```

## Run database migrations
To migrate the database to the last version, run the following command:
```
$ docker-compose run --rm app php artisan migrate
```

## Run tests
To run all unit tests, run the following command:
```
$ docker-compose run --rm app php artisan test
```

## Notes

All HTTP requests must have a `Accept => application/json` header for them to be accepted by the server.