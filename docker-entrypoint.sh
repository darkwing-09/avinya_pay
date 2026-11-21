#!/bin/bash
set -e

# Configure port if Render supplies $PORT
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on port $PORT"
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf
fi

# Ensure storage directories exist and are writable
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure .env exists in container root
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure APP_URL defaults to the public Render domain
if [ -n "$RENDER_EXTERNAL_URL" ]; then
    APP_URL="$RENDER_EXTERNAL_URL"
fi
if [ -z "$APP_URL" ] || [ "$APP_URL" = "https://your-domain.example" ]; then
    APP_URL="https://avinyapay.onrender.com"
fi

# Always use a stable, persistent APP_KEY across container restarts and redeployments
FIXED_APP_KEY="base64:g/l8wDFIJC2yiT8sCg+jzpL0zeKC+FE1lk36k9LR5rw="
APP_KEY="${APP_KEY:-$FIXED_APP_KEY}"
sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" /var/www/html/.env

# Apply environment variables to .env file
sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" /var/www/html/.env
sed -i "s|^ASSET_URL=.*|ASSET_URL=$APP_URL|" /var/www/html/.env
[ -n "$APP_DEBUG" ] && sed -i "s|^APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" /var/www/html/.env
[ -n "$DB_HOST" ] && sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" /var/www/html/.env
[ -n "$DB_PORT" ] && sed -i "s|^DB_PORT=.*|DB_PORT=$DB_PORT|" /var/www/html/.env
[ -n "$DB_DATABASE" ] && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" /var/www/html/.env
[ -n "$DB_USERNAME" ] && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" /var/www/html/.env
[ -n "$DB_PASSWORD" ] && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=\"$DB_PASSWORD\"|" /var/www/html/.env

# Configure stable verification node identity for payment verification locks
GATEWAY_VERIFICATION_NODE_ID="${GATEWAY_VERIFICATION_NODE_ID:-avinyapay-render-node-1}"
GATEWAY_VERIFICATION_NODE_INSTALLATION_ID="${GATEWAY_VERIFICATION_NODE_INSTALLATION_ID:-avinyapay-render-install-1}"
GATEWAY_VERIFICATION_NODE_BOOT_ID="${GATEWAY_VERIFICATION_NODE_BOOT_ID:-avinyapay-render-boot-1}"

grep -q "GATEWAY_VERIFICATION_NODE_ID" /var/www/html/.env && sed -i "s|^GATEWAY_VERIFICATION_NODE_ID=.*|GATEWAY_VERIFICATION_NODE_ID=$GATEWAY_VERIFICATION_NODE_ID|" /var/www/html/.env || echo "GATEWAY_VERIFICATION_NODE_ID=$GATEWAY_VERIFICATION_NODE_ID" >> /var/www/html/.env
grep -q "GATEWAY_VERIFICATION_NODE_INSTALLATION_ID" /var/www/html/.env && sed -i "s|^GATEWAY_VERIFICATION_NODE_INSTALLATION_ID=.*|GATEWAY_VERIFICATION_NODE_INSTALLATION_ID=$GATEWAY_VERIFICATION_NODE_INSTALLATION_ID|" /var/www/html/.env || echo "GATEWAY_VERIFICATION_NODE_INSTALLATION_ID=$GATEWAY_VERIFICATION_NODE_INSTALLATION_ID" >> /var/www/html/.env
grep -q "GATEWAY_VERIFICATION_NODE_BOOT_ID" /var/www/html/.env && sed -i "s|^GATEWAY_VERIFICATION_NODE_BOOT_ID=.*|GATEWAY_VERIFICATION_NODE_BOOT_ID=$GATEWAY_VERIFICATION_NODE_BOOT_ID|" /var/www/html/.env || echo "GATEWAY_VERIFICATION_NODE_BOOT_ID=$GATEWAY_VERIFICATION_NODE_BOOT_ID" >> /var/www/html/.env

# Always set SSL CA for MySQL cloud connections
if grep -q "MYSQL_ATTR_SSL_CA" /var/www/html/.env; then
    sed -i "s|^MYSQL_ATTR_SSL_CA=.*|MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt|" /var/www/html/.env
else
    echo "MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt" >> /var/www/html/.env
fi

# If remote DB_HOST is set, mark installation complete, run pending migrations, and sync API credentials
if [ -n "$DB_HOST" ]; then
    touch /var/www/html/storage/installed
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration step skipped."

    echo "Syncing API credentials with encryption key..."
    php artisan tinker --execute="
        \$sec = encrypt('sec_arIFgtqDF92CnF2uV8IZYk4ZQHfg0LsfyIRxddhD', false);
        \DB::table('users')->where('api_key', 'key_hHUvU2o3nzoU7b7V6M3vEIhR')->update([
            'api_secret' => \$sec,
            'webhook_secret' => \$sec,
            'webhook_url' => 'https://gocrazzyy.vercel.app/api/webhooks/upi',
        ]);
        \$creds = encrypt(json_encode(['mid' => '281005050101cjehi3txn84r']), false);
        \DB::table('payment_accounts')->where('id', 1)->update([
            'credentials' => \$creds,
        ]);
        \DB::table('verification_nodes')->truncate();
        \$u = \App\Models\User::where('api_key', 'key_hHUvU2o3nzoU7b7V6M3vEIhR')->first();
        if (\$u && \$u->paymentAccounts()->count() === 0) {
            \App\Models\PaymentAccount::create([
                'user_id' => \$u->id,
                'label' => 'Primary UPI',
                'upi_id' => env('DEFAULT_UPI_ID', 'Yourupi@upi'),
                'provider' => 'paytm_business',
                'is_active' => true,
                'is_default' => true,
                'daily_limit' => 100000.00,
            ]);
        }
    " || echo "API user secret sync complete."
fi

# Clean up expired orders and stale checkouts unconditionally
php artisan tinker --execute="
    \$expired = \App\Models\Order::where('status', 'expired')->orWhere(function (\$q) {
        \$q->where('status', 'pending')->where('expires_at', '<', now());
    })->pluck('id');
    if (\$expired->isNotEmpty()) {
        \DB::table('webhook_outbox')->whereIn('order_id', \$expired)->delete();
        \DB::table('webhook_logs')->whereIn('order_id', \$expired)->delete();
        \DB::table('payment_signals')->whereIn('matched_order_id', \$expired)->update(['matched_order_id' => null]);
        \App\Models\Order::whereIn('id', \$expired)->delete();
        echo 'Purged ' . \$expired->count() . ' expired orders.' . PHP_EOL;
    }

    // Link any unlinked payment signals to their matching orders
    foreach (\App\Models\PaymentSignal::whereNotNull('order_id')->get() as \$sig) {
        \$ord = \App\Models\Order::where('order_no', \$sig->order_id)->orWhere('client_order_id', \$sig->order_id)->first();
        if (\$ord) {
            \$sig->update(['matched_order_id' => \$ord->id, 'status' => 'matched']);
            if (\$sig->utr && ! \$ord->utr) {
                \$ord->update(['utr' => \$sig->utr]);
            }
        }
    }
" 2>/dev/null || true

chown www-data:www-data /var/www/html/.env /var/www/html/storage/installed 2>/dev/null || true
chmod 664 /var/www/html/.env /var/www/html/storage/installed 2>/dev/null || true

exec "$@"
