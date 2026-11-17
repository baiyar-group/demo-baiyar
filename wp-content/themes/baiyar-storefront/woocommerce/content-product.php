<?php
/** Replaces WooCommerce's own content-product.php (loop card markup). The default template puts the
 * add-to-cart button as a sibling *after* the closing </a> of the title/price block, which makes a
 * price-left / quick-add-right footer row impossible with CSS alone (they don't share a flex parent).
 * Simple products only in this demo catalog, so this writes the card by hand instead of fighting that
 * hook order - media with a sale/featured badge on top, then a body with title and a price + quick-add
 * footer row. */
defined('ABSPATH') || exit;

global $product;

if (!$product || !$product->is_visible()) {
    return;
}
?>
<li <?php wc_product_class('', $product); ?>>
  <a href="<?php echo esc_url($product->get_permalink()); ?>" class="baiyar-product-card__media">
    <?php if ($product->is_on_sale()) : ?>
      <span class="baiyar-product-badge baiyar-product-badge--sale"><?php esc_html_e('Diskon', 'baiyar-storefront'); ?></span>
    <?php elseif ($product->is_featured()) : ?>
      <span class="baiyar-product-badge baiyar-product-badge--featured"><?php esc_html_e('Favorit', 'baiyar-storefront'); ?></span>
    <?php endif; ?>
    <?php echo $product->get_image('woocommerce_thumbnail'); ?>
  </a>
  <div class="baiyar-product-card__body">
    <h2 class="woocommerce-loop-product__title">
      <a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
    </h2>
    <div class="baiyar-product-card__footer">
      <span class="price"><?php echo $product->get_price_html(); ?></span>
      <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
  </div>
</li>
