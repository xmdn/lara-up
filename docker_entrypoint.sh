#!/bin/sh

# wait for db to initialize
echo "Staying idle waiting for db"
sleep 30

# Set application key
yes | php artisan jwt:secret
php artisan key:generate

# Create tables using migration with dummy data
php artisan migrate:fresh --seed

# Run the app
php artisan serve --host 0.0.0.0

