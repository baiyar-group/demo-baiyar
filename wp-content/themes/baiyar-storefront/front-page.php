<?php
/** The store's homepage: hero, trust bar, category tiles, a Best Sellers grid (featured products,
 * rendered through WooCommerce's own loop templates so it behaves exactly like the shop page), and a
 * short brand-story block. Takes priority over Settings > Reading automatically (WP template
 * hierarchy), so no "front page" option needs to be chosen in wp-admin.
 *
 * Styled with Tailwind utility classes (loaded via the Play CDN, see functions.php) since this is
 * markup we author ourselves. `.baiyar-trust__item` / `.baiyar-category-tile` / `.baiyar-story` are
 * kept only as hooks for the scroll-reveal JS, not for visual styling, which is all Tailwind classes
 * here. `.baiyar-hero-photo` is a real styling hook though (style.css) - it pulls the hero up behind
 * the sticky header so the header floats on top of the photo, Allbirds-style. */
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main id="main" class="site-main">

  <section class="baiyar-hero-photo relative flex min-h-[100svh] items-end overflow-hidden">
    <img
      src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/hero-photo.jpg"
      alt="<?php esc_attr_e('Seseorang berjalan santai memakai sneakers Lapik di jalan setapak.', 'baiyar-storefront'); ?>"
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

  <section class="mx-auto grid max-w-5xl grid-cols-2 gap-6 border-y border-neutral-200 px-6 py-10 md:grid-cols-4">
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-lg">&#9989;</span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('Checkout asli Baiyar, bukan tempelan', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-lg">&#9850;</span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('QRIS, VA, e-wallet dan retail dalam satu checkout', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-lg">&#9851;</span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('Material daur ulang di setiap pasang', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item text-center">
      <span class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-lg">&#8635;</span>
      <p class="text-sm font-medium text-neutral-700"><?php esc_html_e('Tukar ukuran gampang, 30 hari', 'baiyar-storefront'); ?></p>
    </div>
  </section>

  <section class="mx-auto grid max-w-5xl grid-cols-1 gap-5 px-6 py-12 md:grid-cols-2">
    <a href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"
       class="baiyar-category-tile group relative flex min-h-[320px] flex-col justify-end overflow-hidden rounded-xl bg-cover bg-center p-7 shadow-md transition hover:-translate-y-1 hover:shadow-xl"
       style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/tile-sneakers.svg')">
      <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/0 to-transparent"></span>
      <span class="relative text-2xl font-semibold text-white"><?php esc_html_e('Sneakers', 'baiyar-storefront'); ?></span>
      <span class="relative text-sm font-medium text-white/90"><?php esc_html_e('Lihat koleksi', 'baiyar-storefront'); ?> &rarr;</span>
    </a>
    <a href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"
       class="baiyar-category-tile group relative flex min-h-[320px] flex-col justify-end overflow-hidden rounded-xl bg-cover bg-center p-7 shadow-md transition hover:-translate-y-1 hover:shadow-xl"
       style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/img/tile-sandal.svg')">
      <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/0 to-transparent"></span>
      <span class="relative text-2xl font-semibold text-white"><?php esc_html_e('Sandal', 'baiyar-storefront'); ?></span>
      <span class="relative text-sm font-medium text-white/90"><?php esc_html_e('Lihat koleksi', 'baiyar-storefront'); ?> &rarr;</span>
    </a>
  </section>

  <section class="py-6 pb-14">
    <div class="mx-auto flex max-w-5xl items-baseline justify-between px-6">
      <h2 class="text-2xl font-semibold text-ink"><?php esc_html_e('Paling laris', 'baiyar-storefront'); ?></h2>
      <a class="whitespace-nowrap text-sm font-semibold text-brand-600 hover:text-brand-700" href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php esc_html_e('Lihat semua', 'baiyar-storefront'); ?> &rarr;</a>
    </div>
    <?php
    $bestsellers = new WP_Query([
        'post_type' => 'product',
        'posts_per_page' => 4,
        'tax_query' => [[
            // WooCommerce stores "featured" as a product_visibility term, not post meta.
            'taxonomy' => 'product_visibility',
            'field' => 'name',
            'terms' => 'featured',
        ]],
    ]);
    if ($bestsellers->have_posts()) :
        // A plain wrapper, not <ul class="products"> (woocommerce_product_loop_start()) - Storefront's
        // own product-grid CSS targets .products specifically and fought our column count here. Grid
        // layout itself still comes from style.css (WooCommerce's generated card markup, not ours).
        echo '<div class="baiyar-bestseller-grid mt-4">';
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
