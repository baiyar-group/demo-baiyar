<?php
/** Newsletter + link-column footer block, hooked ahead of storefront_credit. No real signup behind it
 * - this is a demo store, not a mailing list. */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="baiyar-footer">
  <div class="baiyar-footer__newsletter">
    <h2><?php esc_html_e('Dapat info produk baru', 'baiyar-storefront'); ?></h2>
    <p><?php esc_html_e('Demo saja - formulir ini tidak benar-benar mengirim apa pun.', 'baiyar-storefront'); ?></p>
    <form class="baiyar-footer__form" onsubmit="return false;">
      <input type="email" placeholder="<?php esc_attr_e('Alamat email', 'baiyar-storefront'); ?>" aria-label="<?php esc_attr_e('Alamat email', 'baiyar-storefront'); ?>" />
      <button type="submit"><?php esc_html_e('Daftar', 'baiyar-storefront'); ?></button>
    </form>
  </div>
  <div class="baiyar-footer__columns">
    <div class="baiyar-footer__column">
      <h3><?php esc_html_e('Belanja', 'baiyar-storefront'); ?></h3>
      <ul>
        <li><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php esc_html_e('Semua produk', 'baiyar-storefront'); ?></a></li>
        <li><a href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"><?php esc_html_e('Sneakers', 'baiyar-storefront'); ?></a></li>
        <li><a href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"><?php esc_html_e('Sandal', 'baiyar-storefront'); ?></a></li>
      </ul>
    </div>
    <div class="baiyar-footer__column">
      <h3><?php esc_html_e('Bantuan', 'baiyar-storefront'); ?></h3>
      <ul>
        <li><a href="<?php echo esc_url(get_permalink(wc_get_page_id('myaccount'))); ?>"><?php esc_html_e('Akun saya', 'baiyar-storefront'); ?></a></li>
        <li><a href="<?php echo esc_url(get_permalink(wc_get_page_id('cart'))); ?>"><?php esc_html_e('Keranjang', 'baiyar-storefront'); ?></a></li>
        <li><a href="#"><?php esc_html_e('Pengembalian', 'baiyar-storefront'); ?></a></li>
      </ul>
    </div>
    <div class="baiyar-footer__column">
      <h3><?php esc_html_e('Pembayaran oleh', 'baiyar-storefront'); ?></h3>
      <p class="baiyar-footer__payment">
        <?php echo wp_kses(baiyar_inline_logo(22), ['svg' => ['xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'role' => true, 'aria-label' => true], 'defs' => [], 'lineargradient' => ['id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true], 'stop' => ['offset' => true, 'stop-color' => true], 'path' => ['fill' => true, 'd' => true]]); ?>
      </p>
      <p class="baiyar-footer__methods"><?php esc_html_e('QRIS - Virtual Account - E-wallet - Gerai retail', 'baiyar-storefront'); ?></p>
    </div>
  </div>
  <div class="baiyar-footer__social">
    <span><?php esc_html_e('Ikuti Lapik', 'baiyar-storefront'); ?></span>
    <a href="#" aria-label="Instagram">IG</a>
    <a href="#" aria-label="TikTok">TT</a>
    <a href="#" aria-label="X">X</a>
  </div>
</div>
