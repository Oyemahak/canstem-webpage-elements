// Name In Snippet: Disable Yoast Breadcrumbs schema on private pages
// Remove Yoast Breadcrumbs schema AND WebPage->breadcrumb reference on selected pages.
add_action( 'wp', function () {

    $private_slugs = array( 'live-links', 'fees-structure' ); // add more slugs as needed

    if ( ! is_page( $private_slugs ) ) {
        return; // do nothing on other pages
    }

    // 1) Drop the BreadcrumbList piece from the schema graph.
    add_filter( 'wpseo_schema_graph_pieces', function( $pieces, $context ) {
        foreach ( $pieces as $k => $piece ) {
            $matches_class = class_exists( '\Yoast\WP\SEO\Generators\Schema\Breadcrumb' )
                && $piece instanceof \Yoast\WP\SEO\Generators\Schema\Breadcrumb;
            $matches_type = is_object( $piece ) && property_exists( $piece, 'type' )
                && $piece->type === 'BreadcrumbList';
            if ( $matches_class || $matches_type ) {
                unset( $pieces[$k] );
            }
        }
        return $pieces;
    }, 10, 2 );

    // 2) Remove the 'breadcrumb' @id from the WebPage schema piece.
    add_filter( 'wpseo_schema_webpage', function( $data ) {
        if ( isset( $data['breadcrumb'] ) ) {
            unset( $data['breadcrumb'] );
        }
        return $data;
    }, 10, 1 );

});