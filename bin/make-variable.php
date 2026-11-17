<?php
// Usage: wp eval-file /setup/make-variable.php <product_id> <colors comma sep> <sizes comma sep>
// Turns an already-created simple product into a variable one: attributes Warna + Ukuran, one variation per
// colour x size, each inheriting the parent's price/sale price and stock.
$id = (int) ($args[0] ?? 0);
$colors = array_map('trim', explode(',', $args[1] ?? ''));
$sizes = array_map('trim', explode(',', $args[2] ?? ''));
$image_ids = array_map('intval', explode(',', $args[3] ?? ''));

$simple = wc_get_product($id);
$regular = $simple->get_regular_price();
$sale = $simple->get_sale_price();

wp_set_object_terms($id, 'variable', 'product_type');
$product = new WC_Product_Variable($id);

$a1 = new WC_Product_Attribute();
$a1->set_name('Warna');
$a1->set_options($colors);
$a1->set_position(0);
$a1->set_visible(true);
$a1->set_variation(true);
$a2 = new WC_Product_Attribute();
$a2->set_name('Ukuran');
$a2->set_options($sizes);
$a2->set_position(1);
$a2->set_visible(true);
$a2->set_variation(true);
$product->set_attributes([$a1, $a2]);
$product->set_manage_stock(false);
$product->save();

foreach ($colors as $ci => $color) {
    foreach ($sizes as $size) {
        $v = new WC_Product_Variation();
        $v->set_parent_id($id);
        $v->set_attributes(['warna' => $color, 'ukuran' => $size]);
        $v->set_regular_price($regular);
        if ($sale !== '') {
            $v->set_sale_price($sale);
        }
        $v->set_manage_stock(true);
        // One size per colour is sold out, so the struck-through size button shows up like in the reference.
        $v->set_stock_quantity($size === $sizes[3] && $color === $colors[0] ? 0 : 10);
        if (!empty($image_ids[$ci])) {
            $v->set_image_id($image_ids[$ci]);
        }
        $v->set_status('publish');
        $v->save();
    }
}
WC_Product_Variable::sync($id);
wc_delete_product_transients($id);
