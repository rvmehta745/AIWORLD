#!/usr/bin/env bash

composer install
composer dump-autoload --optimize
./php-artisan key:generate --ansi
./php-artisan jwt:secret
./php-artisan ide-helper:generate
./php-artisan ide-helper:meta
./php-artisan ide-helper:eloquent
