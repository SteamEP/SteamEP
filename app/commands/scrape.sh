#!/bin/bash

cd /var/www/steamep.com/
php artisan scrape:cards
php artisan scrape:misc
php artisan scrape:import
