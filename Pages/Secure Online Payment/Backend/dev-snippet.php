<?php
/**
 * CanSTEM – Clover charge endpoint
 * URL used by JS: /wp-json/canstem/charge
 */
add_action( 'rest_api_init', function () {
    register_rest_route(
        'canstem',
        '/charge',
        [
            'methods'             => 'POST',
            'callback'            => 'canstem_process_payment',
            // Public endpoint (Clover payments from front-end form)
            'permission_callback' => '__return_true',
        ]
    );
} );

function canstem_process_payment( WP_REST_Request $request ) {

    $data = $request->get_json_params();

    if ( empty( $data['token'] ) || empty( $data['amount'] ) ) {
        return new WP_REST_Response(
            [ 'success' => false, 'error' => 'Missing payment data.' ],
            400
        );
    }

    $token      = sanitize_text_field( $data['token'] );
    $amount_raw = floatval( $data['amount'] ); // dollars
    if ( $amount_raw <= 0 ) {
        return new WP_REST_Response(
            [ 'success' => false, 'error' => 'Invalid amount.' ],
            400
        );
    }

    // Customer fields (optional but strongly recommended)
    $first_name = isset( $data['firstName'] ) ? sanitize_text_field( $data['firstName'] ) : '';
    $last_name  = isset( $data['lastName'] ) ? sanitize_text_field( $data['lastName'] ) : '';
    $email      = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
    $phone      = isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '';
    $purpose    = isset( $data['purpose'] ) ? sanitize_text_field( $data['purpose'] ) : '';

    // Your Clover credentials
    $merchant_id = '318000254739';
    $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13'; // keep this safe!

    // Convert amount to cents (allow 0.01, etc.)
    $amount_cents = (int) round( $amount_raw * 100 );

    // Build description so it shows nicely on Clover invoice
    $description_parts = [];

    if ( $purpose ) {
        $description_parts[] = "Purpose: {$purpose}";
    }
    if ( $first_name || $last_name ) {
        $description_parts[] = 'Student / Payer: ' . trim( "{$first_name} {$last_name}" );
    }
    if ( $phone ) {
        $description_parts[] = "Phone: {$phone}";
    }

    // Your non-refundable message for invoice
    $description_parts[] = 'NO REFUNDS. All payments are NON-REFUNDABLE. Email this receipt to CanSTEM.education@gmail.com.';

    $description = implode( ' | ', $description_parts );

    $payload = [
        'merchant_id'   => $merchant_id,
        'amount'        => $amount_cents,
        'currency'      => 'CAD',
        'source'        => $token,
        'receipt_email' => $email,
        'description'   => $description,
    ];

    $response = wp_remote_post(
        'https://scl.clover.com/v1/charges',
        [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $payload ),
            'timeout' => 45,
        ]
    );

    if ( is_wp_error( $response ) ) {
        return new WP_REST_Response(
            [
                'success' => false,
                'error'   => 'Gateway error: ' . $response->get_error_message(),
            ],
            500
        );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body        = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $status_code >= 200 && $status_code < 300 && ! empty( $body['id'] ) ) {
        // Charge successful
        return [
            'success'  => true,
            'chargeId' => $body['id'],
        ];
    }

    $error_msg = ! empty( $body['message'] ) ? $body['message'] : 'Payment declined.';
    return new WP_REST_Response(
        [
            'success' => false,
            'error'   => $error_msg,
        ],
        400
    );
}