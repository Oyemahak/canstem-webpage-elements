// Disable Yoast Breadcrumbs schema on selected pages (safe, no class refs).
add_action( 'wp', function () {

    // Add every private/password page slug here:
    $private_slugs = array( 'live-links', 'fee-structure' );

    // Only act on those pages.
    if ( ! is_page( $private_slugs ) || ! defined( 'WPSEO_VERSION' ) ) {
        return;
    }

    // 1) Remove the 'breadcrumb' @id reference from the WebPage schema.
    add_filter( 'wpseo_schema_webpage', function( $data ) {
        if ( is_array( $data ) && isset( $data['breadcrumb'] ) ) {
            unset( $data['breadcrumb'] );
        }
        return $data;
    }, 20 );

    // 2) Neutralize Yoast's breadcrumb piece directly.
    add_filter( 'wpseo_schema_breadcrumb', function( $data ) {
        // Yoast passes an array; return empty to kill it.
        return array();
    }, 20 );

    // 3) Final safety net: strip any BreadcrumbList objects from the full graph.
    add_filter( 'wpseo_json_ld_output', function( $data ) {
        if ( is_array( $data ) && isset( $data['@graph'] ) && is_array( $data['@graph'] ) ) {
            $data['@graph'] = array_values( array_filter( $data['@graph'], function( $piece ) {
                // Handles both string '@type' and array '@type'
                if ( is_array( $piece ) && isset( $piece['@type'] ) ) {
                    if ( is_string( $piece['@type'] ) && $piece['@type'] === 'BreadcrumbList' ) return false;
                    if ( is_array( $piece['@type'] ) && in_array( 'BreadcrumbList', $piece['@type'], true ) ) return false;
                }
                return true;
            } ) );
        }
        return $data;
    }, 20 );

});