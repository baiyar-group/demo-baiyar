#!/bin/bash
# Run inside the `wpcli` container: docker compose run --rm wpcli bash /setup/setup.sh
# Installs WordPress, WooCommerce, the Baiyar Storefront child theme, the Baiyar plugin stub, and sample
# products with working add-to-cart / cart / checkout pages (Storefront + WooCommerce provide all of
# that out of the box; the child theme only restyles it with Baiyar's tokens and motion).
set -euo pipefail

DEMO_PORT="${DEMO_PORT:-8080}"
cd /var/www/html

echo "Waiting for the database..."
until (exec 3<>/dev/tcp/db/3306) 2>/dev/null; do
  sleep 2
done
exec 3<&- 3>&-

if ! wp core is-installed 2>/dev/null; then
  echo "Installing WordPress..."
  wp core install \
    --url="http://localhost:${DEMO_PORT}" \
    --title="Demo Baiyar" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=demo@baiyar.com \
    --skip-email
fi

echo "Pointing the site at the current DEMO_PORT..."
wp option update siteurl "http://localhost:${DEMO_PORT}"
wp option update home "http://localhost:${DEMO_PORT}"

wp rewrite structure '/%postname%/' --hard

echo "Installing Storefront (parent theme) and activating the Baiyar Storefront child theme..."
wp theme install storefront
wp theme activate baiyar-storefront

echo "Installing and activating WooCommerce..."
wp plugin install woocommerce --activate

echo "Activating the Baiyar plugin stub..."
wp plugin activate baiyar-for-woocommerce

echo "Enabling the Baiyar gateway at checkout..."
wp eval "update_option('woocommerce_baiyar_settings', ['enabled' => 'yes', 'title' => 'Baiyar', 'description' => 'Pay with QRIS, virtual account, e-wallet or a retail outlet.']);"

echo "Setting the store currency to IDR..."
wp option update woocommerce_currency IDR
wp option update woocommerce_currency_pos left
wp option update woocommerce_price_thousand_sep "."
wp option update woocommerce_price_decimal_sep ","
wp option update woocommerce_price_num_decimals 0
wp option update woocommerce_default_country "ID"

echo "Creating sample products..."
wp wc product create --user=admin \
  --name="Kopi Susu Gula Aren 250ml" \
  --type=simple --regular_price=28000 --sku=demo-kopi-susu \
  --manage_stock=true --stock_quantity=50 \
  --short_description="Kopi susu house blend, botol 250ml." || true
wp wc product create --user=admin \
  --name="Tote Bag Canvas" \
  --type=simple --regular_price=75000 --sku=demo-tote-bag \
  --manage_stock=true --stock_quantity=30 \
  --short_description="Tote bag kanvas, muat laptop 14 inci." || true
wp wc product create --user=admin \
  --name="Lilin Aromaterapi" \
  --type=simple --regular_price=45000 --sku=demo-lilin \
  --manage_stock=true --stock_quantity=40 \
  --short_description="Lilin soy wax, aroma lavender." || true
wp wc product create --user=admin \
  --name="Notebook A5 Dot Grid" \
  --type=simple --regular_price=32000 --sku=demo-notebook \
  --manage_stock=true --stock_quantity=60 \
  --short_description="Notebook sampul kulit sintetis, 120 lembar." || true

echo "Done. Admin: http://localhost:${DEMO_PORT}/wp-admin (admin / admin)"
echo "Shop:  http://localhost:${DEMO_PORT}/?page_id=$(wp option get woocommerce_shop_page_id)"
