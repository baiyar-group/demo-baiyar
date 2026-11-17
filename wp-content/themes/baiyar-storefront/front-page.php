<?php
/** The store's homepage: hero, trust bar, a shop-by-category carousel, a bestsellers product carousel
 * (rendered through WooCommerce's own loop templates, via this theme's woocommerce/content-product.php
 * override, so it behaves exactly like the shop page everywhere else that template is used), and a
 * short brand-story block. Takes priority over Settings > Reading automatically (WP template
 * hierarchy), so no "front page" option needs to be chosen in wp-admin.
 *
 * Styled with Tailwind utility classes (loaded via the Play CDN, see functions.php) since this is
 * markup we author ourselves. `.baiyar-trust__item` / `.baiyar-story` are kept only as hooks for the
 * scroll-reveal JS; `.baiyar-shop-tile` / `.baiyar-carousel-track` / `.baiyar-carousel-arrow` are real
 * styling hooks (style.css) for the two horizontal carousels, wired up by wireCarousels() in
 * assets/js/baiyar-storefront.js. `.baiyar-hero-photo` is a real styling hook too - it pulls the hero up
 * behind the sticky header so the header floats on top of the photo, Allbirds-style. */
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main id="main" class="site-main">

  <section class="baiyar-hero-photo relative flex min-h-[100svh] items-end overflow-hidden">
    <img
      src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/hero-photo.jpg"
      alt="<?php esc_attr_e('Sekelompok teman duduk santai bersama di rumah, suasana hangat sepanjang hari.', 'baiyar-storefront'); ?>"
      class="absolute inset-0 h-full w-full object-cover"
      fetchpriority="high"
    />
    <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/20 to-brand-700/10"></span>
    <div class="relative w-full px-6 pb-20 pt-32 md:px-16 md:pb-28">
      <div class="max-w-xl">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand-100"><?php esc_html_e('Koleksi harian', 'baiyar-storefront'); ?></p>
        <h1 class="text-4xl font-bold leading-tight tracking-tight text-white md:text-6xl">
          <?php esc_html_e('Sepatu dan sandal yang enak dipakai dari pagi sampai malam.', 'baiyar-storefront'); ?>
        </h1>
        <p class="mt-5 max-w-md text-lg text-white/85">
          <?php esc_html_e('Material daur ulang, bentuk yang nyaman, harga yang masuk akal - dan checkout yang beneran jalan, lewat Baiyar.', 'baiyar-storefront'); ?>
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <a href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"
             class="rounded-md bg-brand-gradient px-7 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg">
            <?php esc_html_e('Belanja Sneakers', 'baiyar-storefront'); ?>
          </a>
          <a href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"
             class="rounded-md border border-white/70 bg-white/10 px-7 py-3 text-sm font-semibold uppercase tracking-wide text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/20">
            <?php esc_html_e('Belanja Sandal', 'baiyar-storefront'); ?>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-2 gap-6 border-y border-neutral-200 px-6 py-10 md:grid-cols-4">
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></svg>
      </span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('Checkout asli Baiyar, bukan tempelan', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h3"/></svg>
      </span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('QRIS, VA, e-wallet dan retail dalam satu checkout', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M4 20c0-9 5-15 16-16-1 11-7 16-16 16z"/><path d="M4.5 19.5c3-4 7-7.5 11-10.5"/></svg>
      </span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('Material daur ulang di setiap pasang', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M5.5 9A7 7 0 0 1 19 8.5"/><path d="M18.5 15A7 7 0 0 1 5 15.5"/></svg>
      </span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('Tukar ukuran gampang, 30 hari', 'baiyar-storefront'); ?></p>
    </div>
  </section>

  <section class="baiyar-shop-carousel-section pt-4">
    <div class="baiyar-shop-tiles">
      <div class="baiyar-shop-tile"
           style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/tile-new-arrivals.jpg')">
        <span class="baiyar-shop-tile__eyebrow"><?php esc_html_e('Koleksi baru', 'baiyar-storefront'); ?></span>
        <span class="baiyar-shop-tile__pills">
          <a class="baiyar-shop-tile__pill" href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"><?php esc_html_e('Belanja Sneakers', 'baiyar-storefront'); ?></a>
          <a class="baiyar-shop-tile__pill" href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"><?php esc_html_e('Belanja Sandal', 'baiyar-storefront'); ?></a>
        </span>
      </div>
      <a href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"
         class="baiyar-shop-tile"
         style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/tile-sneakers.jpg')">
        <span class="baiyar-shop-tile__pill"><?php esc_html_e('Sneakers', 'baiyar-storefront'); ?></span>
      </a>
      <a href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"
         class="baiyar-shop-tile"
         style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/tile-sandal.jpg')">
        <span class="baiyar-shop-tile__pill"><?php esc_html_e('Sandal', 'baiyar-storefront'); ?></span>
      </a>
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
         class="baiyar-shop-tile"
         style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/tile-bestseller.jpg')">
        <span class="baiyar-shop-tile__pill"><?php esc_html_e('Best Seller', 'baiyar-storefront'); ?></span>
      </a>
    </div>
  </section>

  <section class="pt-10 pb-14">
    <div class="baiyar-section-head">
      <h2 class="baiyar-section-title"><?php esc_html_e('Best Sellers', 'baiyar-storefront'); ?></h2>
      <div class="baiyar-carousel-arrows">
        <button type="button" class="baiyar-carousel-arrow" data-carousel-prev="bestsellers" aria-label="<?php esc_attr_e('Sebelumnya', 'baiyar-storefront'); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button type="button" class="baiyar-carousel-arrow" data-carousel-next="bestsellers" aria-label="<?php esc_attr_e('Berikutnya', 'baiyar-storefront'); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </div>
    <?php
    $bestsellers = new WP_Query([
        'post_type' => 'product',
        'posts_per_page' => 6,
        'orderby' => 'menu_order title',
    ]);
    if ($bestsellers->have_posts()) :
        // A plain wrapper, not <ul class="products"> (woocommerce_product_loop_start()) - Storefront's
        // own product-grid CSS targets .products specifically and fought our column count here. This is
        // a horizontally-scrolling carousel track, not a grid - see .baiyar-bestseller-grid in style.css.
        echo '<div class="baiyar-carousel-track baiyar-bestseller-grid" data-carousel="bestsellers">';
        while ($bestsellers->have_posts()) :
            $bestsellers->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    endif;
    ?>
  </section>

  <section class="baiyar-story mx-auto max-w-xl px-6 pb-20 text-center">
    <h2 class="mb-4 text-3xl font-semibold text-ink"><?php esc_html_e('Dibuat untuk dipakai, bukan dipajang', 'baiyar-storefront'); ?></h2>
    <p class="text-lg leading-relaxed text-neutral-600"><?php esc_html_e('Kami percaya sepatu sehari-hari nggak perlu rumit: bahan yang bertanggung jawab, bentuk yang nyaman buat jalan jauh, dan proses beli yang nggak bikin pusing - termasuk soal bayar.', 'baiyar-storefront'); ?></p>
  </section>

</main>

<?php get_footer();
