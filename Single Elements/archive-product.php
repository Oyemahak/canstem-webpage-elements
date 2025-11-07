<?php
/**
 * Template override for WooCommerce Shop and Category pages
 * Displays the filter on category pages and product loop
 *
 * Copy this file to yourtheme/woocommerce/archive-product.php
 */

defined( 'ABSPATH' ) || exit;

get_header(); // Using Astra’s default header

?>

<main id="primary" class="site-main">

    <?php
    /**
     * WooCommerce breadcrumb and wrappers
     */
    do_action( 'woocommerce_before_main_content' );

    /**
     * ✅ Custom: Show Grade filter only on category pages
     */
    if ( is_product_category() ) {
        $taxonomy = 'pa_grade'; // Attribute name with pa_ prefix
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ]);

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            echo '<div class="grade-filter" style="margin: 20px 0 30px; padding: 15px; background: #f5f5f5; border-radius: 8px;">';
            echo '<h3 style="margin-bottom: 10px; font-size: 20px; color: #00427C;">Filter by Grade</h3>';
            echo '<ul style="list-style: none; display: flex; flex-wrap: wrap; gap: 10px; margin: 0; padding: 0;">';
            foreach ( $terms as $term ) {
                $term_link = get_term_link( $term );
                echo '<li><a href="' . esc_url( $term_link ) . '" style="padding: 8px 14px; background: #00427C; color: white; border-radius: 6px; text-decoration: none;">' . esc_html( $term->name ) . '</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    /**
     * WooCommerce Loop Header (Shop/Category Title, etc.)
     */
    do_action( 'woocommerce_shop_loop_header' );

    if ( woocommerce_product_loop() ) {

        do_action( 'woocommerce_before_shop_loop' );

        woocommerce_product_loop_start();

        if ( wc_get_loop_prop( 'total' ) ) {
            while ( have_posts() ) {
                the_post();

                do_action( 'woocommerce_shop_loop' );

                wc_get_template_part( 'content', 'product' );
            }
        }

        woocommerce_product_loop_end();

        do_action( 'woocommerce_after_shop_loop' );
    } else {
        do_action( 'woocommerce_no_products_found' );
    }

    do_action( 'woocommerce_after_main_content' );
    do_action( 'woocommerce_sidebar' );
    ?>

</main>

<?php get_footer(); // Using Astra’s default footer ?>