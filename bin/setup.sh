#!/bin/bash
# Run inside the `wpcli` container: docker compose run --rm wpcli bash /setup/setup.sh
# Installs WordPress, WooCommerce, the Baiyar Storefront child theme, the Baiyar plugin, and sample
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
    --title="Lapik" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=demo@baiyar.com \
    --skip-email
fi

echo "Pointing the site at the current DEMO_PORT..."
wp option update siteurl "http://localhost:${DEMO_PORT}"
wp option update home "http://localhost:${DEMO_PORT}"

# The store's own brand - Baiyar is the payment processor behind it, not the retailer (see
# functions.php), so the site identity is never "Baiyar".
wp option update blogname "Lapik"
wp option update blogdescription "Sepatu dan sandal sehari-hari"
wp theme mod remove custom_logo 2>/dev/null || true

wp rewrite structure '/%postname%/' --hard

echo "Installing Storefront (parent theme) and activating the Baiyar Storefront child theme..."
wp theme install storefront
wp theme activate baiyar-storefront

echo "Installing and activating WooCommerce..."
wp plugin install woocommerce --activate

echo "Activating the Baiyar plugin..."
wp plugin activate baiyar-for-woocommerce

echo "Switching the Baiyar gateway on (it is offered at checkout once bin/baiyar.sh has connected it to a project)..."
wp eval "update_option('woocommerce_baiyar_settings', ['enabled' => 'yes', 'title' => 'Baiyar', 'description' => 'Bayar dengan QRIS, virtual account, e-wallet, atau gerai retail.']);"

echo "Installing the Indonesian language packs (WordPress, WooCommerce, Storefront)..."
wp language core install id_ID
wp language plugin install woocommerce id_ID
wp language theme install storefront id_ID || true
wp site switch-language id_ID

echo "Renaming the store pages to Indonesian titles (slugs stay)..."
for pair in "shop:Toko" "cart:Keranjang" "checkout:Checkout" "myaccount:Akun saya"; do
  page_id="$(wp eval "echo wc_get_page_id('${pair%%:*}');")"
  [ "$page_id" -gt 0 ] && wp post update "$page_id" --post_title="${pair#*:}" >/dev/null
done

echo "Turning off WooCommerce coming-soon mode (new installs ship with it on)..."
wp option update woocommerce_coming_soon no
wp option update woocommerce_store_pages_only no

echo "Setting the store currency to IDR..."
wp option update woocommerce_currency IDR
wp option update woocommerce_currency_pos left
wp option update woocommerce_price_thousand_sep "."
wp option update woocommerce_price_decimal_sep ","
wp option update woocommerce_price_num_decimals 0
wp option update woocommerce_default_country "ID"

echo "Clearing any previous demo products (this store's catalog is fully regenerated each run)..."
existing_products="$(wp post list --post_type=product --format=ids)"
if [ -n "$existing_products" ]; then
  wp post delete $existing_products --force
fi

echo "Creating product categories..."
wp term create product_cat "Sneakers" --slug=sneakers || true
wp term create product_cat "Sandal" --slug=sandal || true
SNEAKERS_CAT="$(wp term list product_cat --slug=sneakers --field=term_id)"
SANDAL_CAT="$(wp term list product_cat --slug=sandal --field=term_id)"

SIZES="39,40,41,42,43,44"
declare -A COLORS=(
  [demo-runner-knit]="Putih Tulang,Biru Langit,Hijau Sage"
  [demo-wool-comfort]="Abu Muda,Terakota,Biru Indigo"
  [demo-everyday-canvas]="Putih,Kuning,Biru Denim"
  [demo-recycled-slide]="Putih,Pasir,Zaitun"
  [demo-eva-comfort]="Cokelat,Biru Baja,Zaitun"
  [demo-strap-sandals]="Krem,Terakota,Biru Denim"
)

create_product() {
  local name="$1" price="$2" sku="$3" stock="$4" cat="$5" desc="$6" featured="$7" image="$8" sale_price="${9:-}"
  local id
  id="$(wp wc product create --user=admin --porcelain \
    --name="$name" --type=simple --regular_price="$price" --sku="$sku" \
    --manage_stock=true --stock_quantity="$stock" \
    --categories="[{\"id\":${cat}}]" --featured="$featured" \
    --short_description="$desc")"
  if [ -n "$sale_price" ]; then
    wp wc product update "$id" --user=admin --sale_price="$sale_price" >/dev/null
  fi
  wp media import "/setup/product-images/${image}" --post_id="$id" --featured_image
  local base="${image%.jpg}" gallery=""
  for n in 2 3; do
    local gid
    gid="$(wp media import "/setup/product-images/${base}-${n}.jpg" --post_id="$id" --porcelain)"
    gallery="${gallery:+$gallery,}$gid"
  done
  wp post meta update "$id" _product_image_gallery "$gallery" >/dev/null
  local color_ids=""
  for n in 1 2 3; do
    local cid
    cid="$(wp media import "/setup/product-images/${base}-color-${n}.jpg" --post_id="$id" --porcelain)"
    color_ids="${color_ids:+$color_ids,}$cid"
  done
  wp eval-file /setup/make-variable.php "$id" "${COLORS[$sku]}" "$SIZES" "$color_ids" >/dev/null
  echo "  -> ${name} (#${id})"
}

echo "Creating sneakers..."
create_product "Runner Knit Sneakers" 450000 demo-runner-knit 40 "$SNEAKERS_CAT" \
  "Rajutan breathable dari serat daur ulang, ringan dipakai sepanjang hari." true runner-knit-sneakers.jpg 360000
create_product "Wool Comfort Sneakers" 520000 demo-wool-comfort 35 "$SNEAKERS_CAT" \
  "Lapisan wol alami, hangat tanpa bikin gerah." true wool-comfort-sneakers.jpg
create_product "Everyday Canvas Sneakers" 380000 demo-everyday-canvas 55 "$SNEAKERS_CAT" \
  "Kanvas tebal, cocok dipakai santai setiap hari." false everyday-canvas-sneakers.jpg

echo "Creating sandals..."
create_product "Recycled Slide Sandals" 220000 demo-recycled-slide 50 "$SANDAL_CAT" \
  "Sol empuk dari karet daur ulang, anti licin." true recycled-slide-sandals.jpg 180000
create_product "EVA Comfort Sandals" 195000 demo-eva-comfort 60 "$SANDAL_CAT" \
  "Ringan seharian, busa EVA yang balik ke bentuk semula." false eva-comfort-sandals.jpg
create_product "Strap Sandals" 250000 demo-strap-sandals 45 "$SANDAL_CAT" \
  "Tali adjustable, pas di segala bentuk kaki." true strap-sandals.jpg

echo "Setting up a curated primary menu (Semua Produk / Sneakers / Sandal only, not WordPress's default page list)..."
existing_menus="$(wp menu list --format=ids)"
if [ -n "$existing_menus" ]; then
  wp menu delete $existing_menus
fi
wp menu create "Main Menu"
SHOP_URL="$(wp eval 'echo esc_url( get_permalink( wc_get_page_id( "shop" ) ) );')"
SNEAKERS_URL="$(wp eval 'echo esc_url( get_term_link( "sneakers", "product_cat" ) );')"
SANDAL_URL="$(wp eval 'echo esc_url( get_term_link( "sandal", "product_cat" ) );')"
wp menu item add-custom "Main Menu" "Semua Produk" "$SHOP_URL"
wp menu item add-custom "Main Menu" "Sneakers" "$SNEAKERS_URL"
wp menu item add-custom "Main Menu" "Sandal" "$SANDAL_URL"
wp menu location assign "Main Menu" primary
wp menu location assign "Main Menu" handheld

echo "Setting the homepage to the Baiyar Storefront front page template..."
wp option update show_on_front posts

echo "Done. Admin: http://localhost:${DEMO_PORT}/wp-admin (admin / admin)"
echo "Store: http://localhost:${DEMO_PORT}/"
