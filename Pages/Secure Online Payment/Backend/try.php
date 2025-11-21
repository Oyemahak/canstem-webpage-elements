<?php
/**
 * CanSTEM – Clover charge endpoint
 * Route used by front-end form: /wp-json/canstem/charge
 */
add_action('rest_api_init', function () {
    register_rest_route(
        'canstem',
        '/charge',
        [
            'methods'             => 'POST',
            'callback'            => 'canstem_process_payment',
            'permission_callback' => '__return_true',
        ]
    );
});

function canstem_process_payment( WP_REST_Request $request ) {

    $data = $request->get_json_params();

    if ( empty( $data['token'] ) || empty( $data['amount'] ) ) {
        return new WP_REST_Response(
            [ 'success' => false, 'error' => 'Missing payment data.' ],
            400
        );
    }

    $token      = sanitize_text_field( $data['token'] );
    $amount_raw = floatval( $data['amount'] );

    if ( $amount_raw <= 0 ) {
        return new WP_REST_Response(
            [ 'success' => false, 'error' => 'Invalid amount.' ],
            400
        );
    }

    // ==========================
    // CUSTOMER FIELDS (Frontend)
    // ==========================
    $first_name = sanitize_text_field( $data['firstName'] ?? '' );
    $last_name  = sanitize_text_field( $data['lastName']  ?? '' );
    $email      = sanitize_email(      $data['email']     ?? '' );
    $phone      = sanitize_text_field( $data['phone']     ?? '' );
    $purpose    = sanitize_text_field( $data['purpose']   ?? '' );

    $full_name  = trim( $first_name . ' ' . $last_name );

    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) ) {
        return new WP_REST_Response(
            [ 'success' => false, 'error' => 'Missing required customer details.' ],
            400
        );
    }

    // ==========================
    // CLOVER API CONFIG
    // ==========================
    $merchant_id = '318000254739';
    $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13'; // keep server-side only

    $amount_cents = (int) round( $amount_raw * 100 );

    // Clean description (no refund text)
    $description = $purpose ? "Purpose: {$purpose}" : 'Online payment – CanSTEM Education';
    $note        = $purpose;

    // ==========================
    // STEP 1 – CREATE CUSTOMER (card-on-file)
    // ==========================
    $customer_payload = [
        'ecomind'     => 'ecom',
        'merchant_id' => $merchant_id,
        'email'       => $email,
        'firstName'   => $first_name,
        'lastName'    => $last_name,
        'name'        => $full_name,
        'phoneNumber' => $phone,
        // Attach the token as card-on-file source
        'source'      => $token,
    ];

    $customer_response = wp_remote_post(
        'https://scl.clover.com/v1/customers',
        [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $customer_payload ),
            'timeout' => 45,
        ]
    );

    if ( is_wp_error( $customer_response ) ) {
        return new WP_REST_Response(
            [
                'success' => false,
                'error'   => 'Gateway error (customer): ' . $customer_response->get_error_message(),
            ],
            500
        );
    }

    $customer_status = wp_remote_retrieve_response_code( $customer_response );
    $customer_body   = json_decode( wp_remote_retrieve_body( $customer_response ), true );

    if ( $customer_status < 200 || $customer_status >= 300 || empty( $customer_body['id'] ) ) {
        $msg = ! empty( $customer_body['message'] )
            ? $customer_body['message']
            : 'Could not create customer.';
        return new WP_REST_Response(
            [
                'success' => false,
                'error'   => $msg,
            ],
            400
        );
    }

    $customer_id = $customer_body['id'];

    // ==========================
    // STEP 2 – CHARGE THAT CUSTOMER
    // ==========================
    $charge_payload = [
        'ecomind'     => 'ecom',
        'merchant_id' => $merchant_id,
        'amount'      => $amount_cents,
        'currency'    => 'CAD',

        // Link this transaction to the saved customer
        'customer'    => [
            'id' => $customer_id,
        ],

        'receipt_email' => $email,
        'description'   => $description,
        'note'          => $note,
    ];

    $charge_response = wp_remote_post(
        'https://scl.clover.com/v1/charges',
        [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $charge_payload ),
            'timeout' => 45,
        ]
    );

    if ( is_wp_error( $charge_response ) ) {
        return new WP_REST_Response(
            [
                'success' => false,
                'error'   => 'Gateway error (charge): ' . $charge_response->get_error_message(),
            ],
            500
        );
    }

    $status_code = wp_remote_retrieve_response_code( $charge_response );
    $body        = json_decode( wp_remote_retrieve_body( $charge_response ), true );

    if ( $status_code >= 200 && $status_code < 300 && ! empty( $body['id'] ) ) {
        // SUCCESS – charge is complete
        return [
            'success'    => true,
            'chargeId'   => $body['id'],
            'customerId' => $customer_id,
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