#!/bin/sh

# Initialize SQLite database if it doesn't exist
if [ ! -f "/app/database/apileon.sqlite" ]; then
    echo "Creating SQLite database..."
    mkdir -p /app/database
    php /app/create-database.php
fi

# Start supervisor (which manages nginx and php-fpm)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
