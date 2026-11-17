<?php
/** Newsletter + link-column footer block, hooked ahead of storefront_credit. No real signup behind it
 * - this is a demo store, not a mailing list. Styled with Tailwind utility classes (see functions.php). */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="mx-auto flex max-w-5xl flex-wrap justify-between gap-12 px-6 py-14 lg:px-16">
  <div class="max-w-sm flex-1" style="min-width: 280px">
    <h2 class="mb-2 text-xl font-semibold text-ink"><?php esc_html_e('Dapat info produk baru', 'baiyar-storefront'); ?></h2>
    <p class="mb-4 text-sm text-neutral-600"><?php esc_html_e('Demo saja - formulir ini tidak benar-benar mengirim apa pun.', 'baiyar-storefront'); ?></p>
    <form class="flex gap-2" onsubmit="return false;">
      <input type="email"
             placeholder="<?php esc_attr_e('Alamat email', 'baiyar-storefront'); ?>"
             aria-label="<?php esc_attr_e('Alamat email', 'baiyar-storefront'); ?>"
             class="flex-1 rounded-md border border-neutral-300 px-3.5 py-2.5 text-sm" />
      <button type="submit" class="rounded-md bg-brand-gradient px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5">
        <?php esc_html_e('Daftar', 'baiyar-storefront'); ?>
      </button>
    </form>
  </div>
  <div class="flex flex-[2_1_420px] flex-wrap justify-end gap-12">
    <div>
      <h3 class="mb-3.5 text-xs font-semibold uppercase tracking-wide text-neutral-500"><?php esc_html_e('Belanja', 'baiyar-storefront'); ?></h3>
      <ul class="list-none space-y-2.5 p-0">
        <li><a class="text-[0.9375rem] text-ink !no-underline hover:text-brand-600" href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php esc_html_e('Semua produk', 'baiyar-storefront'); ?></a></li>
        <li><a class="text-[0.9375rem] text-ink !no-underline hover:text-brand-600" href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"><?php esc_html_e('Sneakers', 'baiyar-storefront'); ?></a></li>
        <li><a class="text-[0.9375rem] text-ink !no-underline hover:text-brand-600" href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"><?php esc_html_e('Sandal', 'baiyar-storefront'); ?></a></li>
      </ul>
    </div>
    <div>
      <h3 class="mb-3.5 text-xs font-semibold uppercase tracking-wide text-neutral-500"><?php esc_html_e('Bantuan', 'baiyar-storefront'); ?></h3>
      <ul class="list-none space-y-2.5 p-0">
        <li><a class="text-[0.9375rem] text-ink !no-underline hover:text-brand-600" href="<?php echo esc_url(get_permalink(wc_get_page_id('myaccount'))); ?>"><?php esc_html_e('Akun saya', 'baiyar-storefront'); ?></a></li>
        <li><a class="text-[0.9375rem] text-ink !no-underline hover:text-brand-600" href="<?php echo esc_url(get_permalink(wc_get_page_id('cart'))); ?>"><?php esc_html_e('Keranjang', 'baiyar-storefront'); ?></a></li>
        <li><a class="text-[0.9375rem] text-ink !no-underline hover:text-brand-600" href="#"><?php esc_html_e('Pengembalian', 'baiyar-storefront'); ?></a></li>
      </ul>
    </div>
    <div>
      <h3 class="mb-3.5 text-xs font-semibold uppercase tracking-wide text-neutral-500"><?php esc_html_e('Pembayaran oleh', 'baiyar-storefront'); ?></h3>
      <p class="mb-1.5"><?php echo wp_kses(baiyar_inline_logo(22), ['svg' => ['xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'role' => true, 'aria-label' => true, 'style' => true], 'defs' => [], 'lineargradient' => ['id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true], 'stop' => ['offset' => true, 'stop-color' => true], 'path' => ['fill' => true, 'd' => true]]); ?></p>
      <p class="text-[0.8125rem] text-neutral-500"><?php esc_html_e('QRIS - Virtual Account - E-wallet - Gerai retail', 'baiyar-storefront'); ?></p>
    </div>
  </div>
  <div class="flex w-full items-center gap-4 border-t border-neutral-200 pt-6">
    <span class="text-xs uppercase tracking-wide text-neutral-500"><?php esc_html_e('Ikuti Lapik', 'baiyar-storefront'); ?></span>
    <a href="#" aria-label="Instagram" class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-100 text-[0.6875rem] font-bold text-ink transition hover:-translate-y-0.5 hover:bg-brand-100">IG</a>
    <a href="#" aria-label="TikTok" class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-100 text-[0.6875rem] font-bold text-ink transition hover:-translate-y-0.5 hover:bg-brand-100">TT</a>
    <a href="#" aria-label="X" class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-100 text-[0.6875rem] font-bold text-ink transition hover:-translate-y-0.5 hover:bg-brand-100">X</a>
  </div>
</div>
