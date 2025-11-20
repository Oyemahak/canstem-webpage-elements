<?php
/**
 * CanSTEM – Clover charge endpoint
 * URL used by JS: /wp-json/canstem/charge
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

function canstem_process_payment(WP_REST_Request $request)
{
    $data = $request->get_json_params();

    if (empty($data['token']) || empty($data['amount'])) {
        return new WP_REST_Response(
            ['success' => false, 'error' => 'Missing payment data.'],
            400
        );
    }

    $token      = sanitize_text_field($data['token']);
    $amount_raw = floatval($data['amount']);

    if ($amount_raw <= 0) {
        return new WP_REST_Response(
            ['success' => false, 'error' => 'Invalid amount.'],
            400
        );
    }

    // Customer fields from frontend
    $first_name = sanitize_text_field($data['firstName'] ?? '');
    $last_name  = sanitize_text_field($data['lastName'] ?? '');
    $email      = sanitize_email($data['email'] ?? '');
    $phone      = sanitize_text_field($data['phone'] ?? '');
    $purpose    = sanitize_text_field($data['purpose'] ?? '');

    // Combine first + last name for Clover (dashboard + CSV + receipt)
    $full_name = trim($first_name . ' ' . $last_name);

    // Clover credentials
    $merchant_id = '318000254739';
    $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13';

    // Convert to cents
    $amount_cents = (int) round($amount_raw * 100);

    // The description appears in the Clover invoice (only clean content)
    $description = "Purpose: {$purpose}";

    // NOTE appears in Clover CSV (exactly how you want it)
    $note = $purpose;

    // Build Clover payload
    $payload = [
        "merchant_id" => $merchant_id,
        "amount"      => $amount_cents,
        "currency"    => "CAD",
        "source"      => $token,

        // Email used for sending receipt
        "receipt_email" => $email,

        // Clean receipt description (NO refund message)
        "description" => $description,

        // CSV and dashboard will display this clean note
        "note" => $note,

        // Best practice: send customer object
        "customer" => [
            "name"  => $full_name,
            "email" => $email,
            "phone" => $phone
        ],

        // Additional recommended structure
        "billing" => [
            "address" => [
                "first_name" => $first_name,
                "last_name"  => $last_name,
                "phone"      => $phone,
            ],
            "email" => $email
        ]
    ];

    // Send request
    $response = wp_remote_post(
        'https://scl.clover.com/v1/charges',
        [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 45,
        ]
    );

    if (is_wp_error($response)) {
        return new WP_REST_Response(
            [
                'success' => false,
                'error'   => 'Gateway error: ' . $response->get_error_message(),
            ],
            500
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body        = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code >= 200 && $status_code < 300 && !empty($body['id'])) {
        return [
            'success'  => true,
            'chargeId' => $body['id'],
        ];
    }

    $error_msg = !empty($body['message']) ? $body['message'] : 'Payment declined.';

    return new WP_REST_Response(
        [
            'success' => false,
            'error'   => $error_msg,
        ],
        400
    );
}