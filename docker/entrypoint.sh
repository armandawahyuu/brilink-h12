#!/bin/sh

# DB SQLite disimpan di /data (volume terpisah), BUKAN di /app/database
# supaya volume tidak menutupi folder migrations bawaan image.
mkdir -p /data
if [ ! -f /data/database.sqlite ]; then
    touch /data/database.sqlite
fi
chown -R www-data:www-data /data

# Run migrations
php artisan migrate --force

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
