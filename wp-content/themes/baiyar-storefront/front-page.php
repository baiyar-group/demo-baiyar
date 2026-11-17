<?php
/** The store's homepage: hero, trust bar, category tiles, a Best Sellers grid (featured products,
 * rendered through WooCommerce's own loop templates so it behaves exactly like the shop page), and a
 * short brand-story block. Takes priority over Settings > Reading automatically (WP template
 * hierarchy), so no "front page" option needs to be chosen in wp-admin. */
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main id="main" class="site-main baiyar-home">

  <section class="baiyar-hero">
    <div class="baiyar-hero__text">
      <p class="baiyar-hero__eyebrow"><?php esc_html_e('Koleksi harian', 'baiyar-storefront'); ?></p>
      <h1><?php esc_html_e('Sepatu dan sandal yang enak dipakai dari pagi sampai malam.', 'baiyar-storefront'); ?></h1>
      <p class="baiyar-hero__lede"><?php esc_html_e('Material daur ulang, bentuk yang nyaman, harga yang masuk akal - dan checkout yang beneran jalan, lewat Baiyar.', 'baiyar-storefront'); ?></p>
      <div class="baiyar-hero__actions">
        <a class="baiyar-btn baiyar-btn--primary" href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>"><?php esc_html_e('Belanja Sneakers', 'baiyar-storefront'); ?></a>
        <a class="baiyar-btn baiyar-btn--secondary" href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>"><?php esc_html_e('Belanja Sandal', 'baiyar-storefront'); ?></a>
      </div>
    </div>
    <div class="baiyar-hero__art" aria-hidden="true">
      <div class="baiyar-hero__blob"></div>
      <?php
      $hero_query = new WP_Query(['post_type' => 'product', 'name' => 'runner-knit-sneakers', 'posts_per_page' => 1]);
      if ($hero_query->have_posts()) {
          $hero_query->the_post();
          echo get_the_post_thumbnail(get_the_ID(), 'large', ['class' => 'baiyar-hero__shoe']);
          wp_reset_postdata();
      }
      ?>
    </div>
  </section>

  <section class="baiyar-trust">
    <div class="baiyar-trust__item">
      <span class="baiyar-trust__icon">&#9989;</span>
      <p><?php esc_html_e('Checkout asli Baiyar, bukan tempelan', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item">
      <span class="baiyar-trust__icon">&#9850;</span>
      <p><?php esc_html_e('QRIS, VA, e-wallet dan retail dalam satu checkout', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item">
      <span class="baiyar-trust__icon">&#9851;</span>
      <p><?php esc_html_e('Material daur ulang di setiap pasang', 'baiyar-storefront'); ?></p>
    </div>
    <div class="baiyar-trust__item">
      <span class="baiyar-trust__icon">&#8635;</span>
      <p><?php esc_html_e('Tukar ukuran gampang, 30 hari', 'baiyar-storefront'); ?></p>
    </div>
  </section>

  <section class="baiyar-categories">
    <a class="baiyar-category-tile baiyar-category-tile--sneakers" href="<?php echo esc_url(get_term_link('sneakers', 'product_cat')); ?>">
      <span class="baiyar-category-tile__label"><?php esc_html_e('Sneakers', 'baiyar-storefront'); ?></span>
      <span class="baiyar-category-tile__cta"><?php esc_html_e('Lihat koleksi', 'baiyar-storefront'); ?> &rarr;</span>
    </a>
    <a class="baiyar-category-tile baiyar-category-tile--sandal" href="<?php echo esc_url(get_term_link('sandal', 'product_cat')); ?>">
      <span class="baiyar-category-tile__label"><?php esc_html_e('Sandal', 'baiyar-storefront'); ?></span>
      <span class="baiyar-category-tile__cta"><?php esc_html_e('Lihat koleksi', 'baiyar-storefront'); ?> &rarr;</span>
    </a>
  </section>

  <section class="baiyar-bestsellers">
    <div class="baiyar-section-heading">
      <h2><?php esc_html_e('Paling laris', 'baiyar-storefront'); ?></h2>
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php esc_html_e('Lihat semua', 'baiyar-storefront'); ?> &rarr;</a>
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
        // own product-grid CSS targets .products specifically and fought our column count here.
        echo '<div class="baiyar-bestseller-grid">';
        while ($bestsellers->have_posts()) :
            $bestsellers->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    endif;
    ?>
  </section>

  <section class="baiyar-story">
    <h2><?php esc_html_e('Dibuat untuk dipakai, bukan dipajang', 'baiyar-storefront'); ?></h2>
    <p><?php esc_html_e('Kami percaya sepatu sehari-hari nggak perlu rumit: bahan yang bertanggung jawab, bentuk yang nyaman buat jalan jauh, dan proses beli yang nggak bikin pusing - termasuk soal bayar.', 'baiyar-storefront'); ?></p>
  </section>

</main>

<?php get_footer();
