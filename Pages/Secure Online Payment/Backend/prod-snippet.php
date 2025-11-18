<?php
/**
 * CanSTEM – Clover charge endpoint
 * URL used by JS: /wp-json/canstem/charge
 */

add_action('rest_api_init', function () {
    register_rest_route('canstem', '/charge', [
        'methods'             => 'POST',
        'callback'            => 'canstem_process_payment',
        'permission_callback' => '__return_true', // public endpoint (frontend)
    ]);
});

/**
 * Process a payment from the custom Clover iframe form.
 *
 * @param WP_REST_Request $request
 * @return array
 */
function canstem_process_payment( WP_REST_Request $request ) {

    $data = $request->get_json_params();

    if ( empty( $data['token'] ) || empty( $data['amount'] ) || empty( $data['email'] ) ) {
        return [
            'success' => false,
            'error'   => 'Missing required fields.'
        ];
    }

    // Sanitize input
    $token   = sanitize_text_field( $data['token'] );
    $amount  = floatval( $data['amount'] ); // amount in dollars
    $email   = sanitize_email( $data['email'] );
    $fname   = isset( $data['fname'] )   ? sanitize_text_field( $data['fname'] )   : '';
    $lname   = isset( $data['lname'] )   ? sanitize_text_field( $data['lname'] )   : '';
    $phone   = isset( $data['phone'] )   ? sanitize_text_field( $data['phone'] )   : '';
    $purpose = isset( $data['purpose'] ) ? sanitize_text_field( $data['purpose'] ) : '';

    // Convert dollars to cents for Clover (allow values like 0.01)
    $amount_cents = (int) round( $amount * 100 );

    if ( $amount_cents < 1 ) {
        return [
            'success' => false,
            'error'   => 'Amount must be at least CA$0.01.'
        ];
    }

    // Clover credentials (production)
    $merchant_id = '318000254739';
    $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13';

    // Construct a detailed description for the Clover receipt / invoice
    $description_lines   = [];
    $description_lines[] = "Purpose: {$purpose}";
    $description_lines[] = "Student / Payer: {$fname} {$lname}";
    $description_lines[] = "Contact Email: {$email}";
    $description_lines[] = "Contact Phone: {$phone}";
    $description_lines[] = "NO REFUNDS. All Payments are NON-REFUNDABLE.";
    $description_lines[] = "Email this receipt to CanSTEM.education@gmail.com";

    $description = implode( " | ", array_filter( $description_lines ) );

    // Build Clover charge payload
    $payload = json_encode([
        'merchant_id'   => $merchant_id,
        'amount'        => $amount_cents,
        'currency'      => 'CAD',
        'source'        => $token,
        'receipt_email' => $email,
        'description'   => $description,
    ]);

    // Send request to Clover charge endpoint
    $response = wp_remote_post( 'https://scl.clover.com/v1/charges', [
        'method'  => 'POST',
        'headers' => [
            'Authorization'  => 'Bearer ' . $secret_key,
            'Content-Type'   => 'application/json',
        ],
        'body'    => $payload,
        'timeout' => 45,
    ] );

    if ( is_wp_error( $response ) ) {
        // Optionally log: error_log( 'Clover error: ' . $response->get_error_message() );
        return [
            'success' => false,
            'error'   => 'Gateway error.'
        ];
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    // Clover returns an "id" on successful charge
    if ( isset( $body['id'] ) ) {
        return [
            'success'   => true,
            'charge_id' => $body['id']
        ];
    }

    // Optional: return Clover error details if present
    $error_msg = isset( $body['error'] ) ? $body['error'] : 'Unknown error from Clover.';
    return [
        'success' => false,
        'error'   => $error_msg,
        'raw'     => $body,
    ];
}