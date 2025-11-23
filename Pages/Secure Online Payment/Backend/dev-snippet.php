<?php
/**
 * CanSTEM – Clover custom checkout endpoint
 *
 * Flow:
 * 1) Create a v3 Customer (so they appear in Clover "Customers" + exports).
 * 2) Create an ecomm charge (/v1/charges) using the Checkout.js token.
 */

add_action('rest_api_init', function () {
    register_rest_route('canstem', '/charge', [
        'methods'             => 'POST',
        'callback'            => 'canstem_process_payment',
        'permission_callback' => '__return_true',
    ]);
});

function canstem_process_payment( WP_REST_Request $request ) {

    $data = $request->get_json_params();

    // ==========================
    // BASIC VALIDATION
    // ==========================
    if ( empty( $data['token'] ) || empty( $data['amount'] ) ) {
        return [
            'success' => false,
            'error'   => 'Missing token or amount.',
        ];
    }

    $token   = sanitize_text_field( $data['token'] );
    $amount  = floatval( $data['amount'] );

    // Customer fields from the form
    $first   = sanitize_text_field( $data['firstName'] ?? '' );
    $last    = sanitize_text_field( $data['lastName']  ?? '' );
    $email   = sanitize_email(      $data['email']     ?? '' );
    $phone   = sanitize_text_field( $data['phone']     ?? '' );
    $purpose = sanitize_text_field( $data['purpose']   ?? '' );

    if ( $amount <= 0 ) {
        return [
            'success' => false,
            'error'   => 'Invalid amount.',
        ];
    }

    if ( ! $first || ! $last || ! $email ) {
        return [
            'success' => false,
            'error'   => 'Missing required customer details.',
        ];
    }

    $full_name    = trim( "$first $last" );
    $amount_cents = (int) round( $amount * 100 );

    // ==========================
    // CLOVER CREDENTIALS
    // ==========================
    // Uses your new E-commerce Payment private token
    $secret_key  = '0ff42f61-65ae-77f7-bb66-5fa2b94b1d86';
    $merchant_id = '318000254739';

    // ====================================
    // STEP 1 — CREATE v3 CUSTOMER RECORD
    // ====================================
    // This populates Clover "Customers" and lets you reuse the profile.
    $customer_payload = [
        'firstName' => $first,
        'lastName'  => $last,

        'emailAddresses' => [
            [
                'emailAddress'     => $email,
                'emailAddressType' => 'HOME',
                'primaryEmail'     => true,
            ],
        ],

        'phoneNumbers' => [
            [
                'phoneNumber' => $phone,
                'phoneType'   => 'MOBILE',
            ],
        ],

        'metadata' => [
            'note'    => $purpose,
            'purpose' => $purpose,
        ],
    ];

    $customer_res = wp_remote_post(
        "https://scl.clover.com/v3/merchants/{$merchant_id}/customers",
        [
            'headers' => [
                'Authorization' => "Bearer {$secret_key}",
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $customer_payload ),
            'timeout' => 45,
        ]
    );

    if ( is_wp_error( $customer_res ) ) {
        return [
            'success' => false,
            'error'   => 'Clover error (customer create): ' . $customer_res->get_error_message(),
        ];
    }

    $cust_code = wp_remote_retrieve_response_code( $customer_res );
    $cust_body = json_decode( wp_remote_retrieve_body( $customer_res ), true );

    if ( $cust_code < 200 || $cust_code >= 300 || empty( $cust_body['id'] ) ) {
        $msg = ! empty( $cust_body['message'] ) ? $cust_body['message'] : 'Customer could not be created.';
        return [
            'success' => false,
            'error'   => $msg,
            'raw'     => $cust_body,
        ];
    }

    $customer_id = $cust_body['id'];

    // ====================================
    // STEP 2 — CREATE ECOMM CHARGE (v1)
    // ====================================
    // Uses token from Clover Checkout.js and attaches description + receipt email.
    $charge_payload = [
        'ecomind'       => 'ecom',
        'amount'        => $amount_cents,
        'currency'      => 'CAD',
        'source'        => $token,       // token from Checkout.js
        'receipt_email' => $email,       // student gets Clover receipt

        // This text shows in Clover + on the payment details.
        'description'   => $purpose
            ? "Purpose: {$purpose}"
            : "Online payment – CanSTEM Education",

        // Extra metadata visible in Clover exports / API
        'metadata'      => [
            'customer_id' => $customer_id,
            'name'        => $full_name,
            'phone'       => $phone,
            'purpose'     => $purpose,
        ],
    ];

    $charge_res = wp_remote_post(
        'https://scl.clover.com/v1/charges',
        [
            'headers' => [
                'Authorization' => "Bearer {$secret_key}",
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $charge_payload ),
            'timeout' => 45,
        ]
    );

    if ( is_wp_error( $charge_res ) ) {
        return [
            'success' => false,
            'error'   => 'Clover error (charge): ' . $charge_res->get_error_message(),
        ];
    }

    $charge_code = wp_remote_retrieve_response_code( $charge_res );
    $charge_body = json_decode( wp_remote_retrieve_body( $charge_res ), true );

    if ( $charge_code >= 200 && $charge_code < 300 && ! empty( $charge_body['id'] ) ) {
        return [
            'success'    => true,
            'chargeId'   => $charge_body['id'],
            'customerId' => $customer_id,
        ];
    }

    $msg = ! empty( $charge_body['message'] ) ? $charge_body['message'] : 'Charge failed.';
    return [
        'success' => false,
        'error'   => $msg,
        'raw'     => $charge_body,
    ];
}