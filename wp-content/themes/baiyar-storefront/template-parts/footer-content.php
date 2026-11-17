<?php
/** Footer block (hooked ahead of storefront_credit, which style.css hides in favor of the legal row
 * below). No real signup behind the form - this is a demo store, not a mailing list. */
if (!defined('ABSPATH')) {
    exit;
}

$link = static fn (string $href, string $label) => '<li><a href="' . esc_url($href) . '">' . esc_html($label) . '</a></li>';
$shop = get_permalink(wc_get_page_id('shop'));
$svg = static fn (string $path) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
?>
<div class="baiyar-footer">
  <div class="baiyar-footer__top">
    <div class="baiyar-footer__left">
      <div>
        <h2 class="baiyar-footer__label"><?php esc_html_e('Berlangganan email kami', 'baiyar-storefront'); ?></h2>
        <form class="baiyar-footer__signup" onsubmit="return false;">
          <input type="email" placeholder="<?php esc_attr_e('Alamat email', 'baiyar-storefront'); ?>" aria-label="<?php esc_attr_e('Alamat email', 'baiyar-storefront'); ?>" />
          <button type="submit"><?php esc_html_e('Daftar', 'baiyar-storefront'); ?></button>
        </form>
      </div>
      <div>
        <h2 class="baiyar-footer__label"><?php esc_html_e('Ikuti Lapik', 'baiyar-storefront'); ?></h2>
        <div class="baiyar-footer__social">
          <a href="#" aria-label="Instagram"><?php echo $svg('<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.8" fill="currentColor"/>'); ?></a>
          <a href="#" aria-label="Facebook"><?php echo $svg('<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z"/>'); ?></a>
          <a href="#" aria-label="X"><?php echo $svg('<path d="M4 4l16 16M20 4L4 20"/>'); ?></a>
          <a href="#" aria-label="TikTok"><?php echo $svg('<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 3c.4 2.4 2 4 4.5 4.2"/>'); ?></a>
          <a href="#" aria-label="YouTube"><?php echo $svg('<rect x="3" y="6" width="18" height="12" rx="4"/><path d="M10.5 9.5v5l4-2.5-4-2.5z" fill="currentColor"/>'); ?></a>
        </div>
      </div>
    </div>
    <div class="baiyar-footer__columns">
      <div>
        <h2 class="baiyar-footer__label"><?php esc_html_e('Bantuan', 'baiyar-storefront'); ?></h2>
        <ul>
          <?php echo $link('mailto:halo@lapik.test', 'halo@lapik.test'); ?>
          <?php echo $link(get_permalink(wc_get_page_id('myaccount')), __('Akun saya', 'baiyar-storefront')); ?>
          <?php echo $link(get_permalink(wc_get_page_id('cart')), __('Keranjang', 'baiyar-storefront')); ?>
          <?php echo $link('#', __('Pengembalian & penukaran', 'baiyar-storefront')); ?>
        </ul>
      </div>
      <div>
        <h2 class="baiyar-footer__label"><?php esc_html_e('Belanja', 'baiyar-storefront'); ?></h2>
        <ul>
          <?php echo $link($shop, __('Semua produk', 'baiyar-storefront')); ?>
          <?php echo $link(get_term_link('sneakers', 'product_cat'), __('Sneakers', 'baiyar-storefront')); ?>
          <?php echo $link(get_term_link('sandal', 'product_cat'), __('Sandal', 'baiyar-storefront')); ?>
        </ul>
      </div>
      <div>
        <h2 class="baiyar-footer__label"><?php esc_html_e('Pembayaran oleh', 'baiyar-storefront'); ?></h2>
        <p style="margin:0"><?php echo wp_kses(baiyar_inline_logo(24), ['svg' => ['xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'role' => true, 'aria-label' => true, 'style' => true], 'defs' => [], 'lineargradient' => ['id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true], 'stop' => ['offset' => true, 'stop-color' => true], 'path' => ['fill' => true, 'd' => true]]); ?></p>
        <p class="baiyar-footer__pay-note"><?php esc_html_e('QRIS, virtual account, e-wallet, dan gerai retail', 'baiyar-storefront'); ?></p>
      </div>
    </div>
  </div>
  <div class="baiyar-footer__locale"><?php esc_html_e('ID', 'baiyar-storefront'); ?> &#8964;</div>
  <div class="baiyar-footer__bottom">
    <span>&copy; Lapik <?php echo esc_html(gmdate('Y')); ?>. <?php esc_html_e('Seluruh hak dilindungi', 'baiyar-storefront'); ?></span>
    <a href="#"><?php esc_html_e('Kebijakan pengembalian', 'baiyar-storefront'); ?></a>
    <a href="#"><?php esc_html_e('Kebijakan privasi', 'baiyar-storefront'); ?></a>
    <a href="#"><?php esc_html_e('Syarat layanan', 'baiyar-storefront'); ?></a>
    <a href="#"><?php esc_html_e('Kebijakan pengiriman', 'baiyar-storefront'); ?></a>
  </div>
</div>
