<?php
/** Mini cart (the slide-in drawer body), restructured after allbirds.com's: header with item count and a
 * free-shipping progress bar, one row per item (photo, name, variant, remove link / price, quantity
 * stepper), then a pinned footer with subtotal, checkout and the payment methods Baiyar offers. Kept
 * compatible with WooCommerce's own fragment refresh (this file *is* the fragment). */
defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart');

$free_from = 500000;
$cart = WC()->cart;
$count = $cart ? $cart->get_cart_contents_count() : 0;
?>
<div class="baiyar-drawer">
  <div class="baiyar-drawer__head">
    <h2 class="baiyar-drawer__title"><?php echo esc_html(sprintf(__('Keranjang (%d)', 'baiyar-storefront'), $count)); ?></h2>
    <?php if ($cart && !$cart->is_empty()) :
        $subtotal = (float) $cart->get_subtotal();
        $left = max(0, $free_from - $subtotal);
        $pct = min(100, round($subtotal / $free_from * 100)); ?>
      <p class="baiyar-drawer__ship">
        <?php echo $left > 0
            ? wp_kses_post(sprintf(__('Belanja %s lagi untuk gratis ongkir!', 'baiyar-storefront'), wc_price($left)))
            : esc_html__('Kamu dapat gratis ongkir!', 'baiyar-storefront'); ?>
      </p>
      <div class="baiyar-drawer__bar" role="presentation"><span style="width: <?php echo (int) $pct; ?>%"></span></div>
    <?php endif; ?>
  </div>

<?php if ($cart && !$cart->is_empty()) : ?>
  <ul class="woocommerce-mini-cart cart_list product_list_widget baiyar-drawer__items <?php echo esc_attr($args['list_class'] ?? ''); ?>">
    <?php foreach ($cart->get_cart() as $key => $item) :
        $product = $item['data'];
        if (!$product instanceof WC_Product || !$product->exists() || $item['quantity'] <= 0) {
            continue;
        }
        $link = $product->is_visible() ? $product->get_permalink($item) : '';
        $on_sale = $product->is_on_sale();
        $variant = $item['variation'] ? implode(' / ', array_map(fn ($k, $v) => ucfirst(str_replace('attribute_', '', $k)) . ': ' . $v, array_keys($item['variation']), $item['variation'])) : '';
        $line = $cart->get_product_subtotal($product, $item['quantity']);
        ?>
      <li class="woocommerce-mini-cart-item mini_cart_item baiyar-drawer__item">
        <a class="baiyar-drawer__thumb" href="<?php echo esc_url($link); ?>"><?php echo $product->get_image('woocommerce_thumbnail'); ?></a>
        <div class="baiyar-drawer__info">
          <a class="baiyar-drawer__name" href="<?php echo esc_url($link); ?>"><?php echo esc_html($product->get_title()); ?></a>
          <?php if ($variant) : ?><div class="baiyar-drawer__variant"><?php echo esc_html($variant); ?></div><?php endif; ?>
          <a role="button" class="baiyar-drawer__remove remove_from_cart_button"
             href="<?php echo esc_url(wc_get_cart_remove_url($key)); ?>"
             data-product_id="<?php echo esc_attr($item['product_id']); ?>"
             data-cart_item_key="<?php echo esc_attr($key); ?>"
             data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"><?php esc_html_e('Hapus', 'baiyar-storefront'); ?></a>
        </div>
        <div class="baiyar-drawer__side">
          <div class="baiyar-drawer__price<?php echo $on_sale ? ' is-sale' : ''; ?>"><?php echo wp_kses_post($line); ?></div>
          <?php if ($on_sale) : ?><div class="baiyar-drawer__flag"><?php esc_html_e('Diskon', 'baiyar-storefront'); ?></div><?php endif; ?>
          <div class="baiyar-stepper" data-key="<?php echo esc_attr($key); ?>" data-qty="<?php echo (int) $item['quantity']; ?>">
            <button type="button" data-step="-1" aria-label="<?php esc_attr_e('Kurangi', 'baiyar-storefront'); ?>">
              <?php if ($item['quantity'] <= 1) : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg>
              <?php else : ?>&minus;<?php endif; ?>
            </button>
            <span><?php echo (int) $item['quantity']; ?></span>
            <button type="button" data-step="1" aria-label="<?php esc_attr_e('Tambah', 'baiyar-storefront'); ?>">+</button>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="baiyar-drawer__foot">
    <div class="baiyar-drawer__subtotal"><span><?php esc_html_e('Subtotal', 'baiyar-storefront'); ?></span><strong><?php echo wp_kses_post($cart->get_cart_subtotal()); ?></strong></div>
    <a class="baiyar-drawer__checkout" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php esc_html_e('Lanjut ke checkout', 'baiyar-storefront'); ?></a>
    <div class="baiyar-drawer__pay">
      <span>QRIS</span><span>Virtual Account</span><span>E-wallet</span><span>Gerai retail</span>
    </div>
  </div>
<?php else : ?>
  <p class="woocommerce-mini-cart__empty-message baiyar-drawer__empty"><?php esc_html_e('Keranjang masih kosong.', 'baiyar-storefront'); ?></p>
<?php endif; ?>
</div>
<?php do_action('woocommerce_after_mini_cart'); ?>
