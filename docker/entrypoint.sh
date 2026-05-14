#!/bin/sh

# Create SQLite database if not exists
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
    chown www-data:www-data /app/database/database.sqlite
fi

# Run migrations
php artisan migrate --force

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
