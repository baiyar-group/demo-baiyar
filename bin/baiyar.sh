#!/bin/bash
# Run inside the `wpcli` container to point the plugin at a Baiyar project (no secret is kept in this repo):
#
#   docker compose run --rm \
#     -e BAIYAR_API_KEY=bk_test_... -e BAIYAR_PROJECT_ID=prj_... [-e BAIYAR_WEBHOOK_SECRET=whsec_...] [-e BAIYAR_API_URL=...] \
#     wpcli bash /setup/baiyar.sh
#
# Use a sandbox key (bk_test_...). The webhook secret is optional: without it the store still sends customers to Baiyar and
# marks the order paid when they come back, but not from Baiyar's webhook (which needs a public https address).
set -euo pipefail
cd /var/www/html

: "${BAIYAR_API_KEY:?set BAIYAR_API_KEY (bk_test_...)}"
: "${BAIYAR_PROJECT_ID:?set BAIYAR_PROJECT_ID (prj_...)}"

wp eval '
$o = get_option("woocommerce_baiyar_settings", []);
$o["enabled"]    = "yes";
$o["api_key"]    = getenv("BAIYAR_API_KEY");
$o["project_id"] = getenv("BAIYAR_PROJECT_ID");
if (getenv("BAIYAR_WEBHOOK_SECRET")) { $o["webhook_secret"] = getenv("BAIYAR_WEBHOOK_SECRET"); }
if (getenv("BAIYAR_API_URL")) { $o["api_url"] = getenv("BAIYAR_API_URL"); }
update_option("woocommerce_baiyar_settings", $o);
$g = new WC_Gateway_Baiyar();
echo "Baiyar is ", $g->is_available() ? "on and offered at checkout" : "NOT offered (check the store currency is IDR and the settings)", ".\n";
'
