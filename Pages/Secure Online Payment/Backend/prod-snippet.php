<?php
/**
 * CanSTEM – Clover charge endpoint (Ecommerce Customer + Charge linking)
 * URL used by JS: /wp-json/canstem/charge
 */
add_action('rest_api_init', function () {
  register_rest_route('canstem', '/charge', [
    'methods'             => 'POST',
    'callback'            => 'canstem_process_payment',
    // NOTE: Public endpoint. Add server-side protection (reCAPTCHA / nonce / rate-limit) before scaling.
    'permission_callback' => '__return_true',
  ]);
});

function canstem_process_payment(WP_REST_Request $request) {

  $data = $request->get_json_params();

  if (empty($data['token']) || empty($data['amount'])) {
    return new WP_REST_Response(['success' => false, 'error' => 'Missing payment data.'], 400);
  }

  $token      = sanitize_text_field($data['token']); // clv_... from Clover tokenization
  $amount_raw = floatval($data['amount']); // dollars
  if ($amount_raw <= 0) {
    return new WP_REST_Response(['success' => false, 'error' => 'Invalid amount.'], 400);
  }

  $first_name = isset($data['firstName']) ? sanitize_text_field($data['firstName']) : '';
  $last_name  = isset($data['lastName']) ? sanitize_text_field($data['lastName']) : '';
  $email      = isset($data['email']) ? sanitize_email($data['email']) : '';
  $phone      = isset($data['phone']) ? sanitize_text_field($data['phone']) : '';
  $purpose    = isset($data['purpose']) ? sanitize_textarea_field($data['purpose']) : '';

  if (!$email || !$first_name || !$last_name || !$phone || !$purpose) {
    return new WP_REST_Response(['success' => false, 'error' => 'Please fill all required fields.'], 400);
  }

  // Clover credentials (PRODUCTION)
  // IMPORTANT: Move secret_key into wp-config.php or server env var in production.
  $merchant_id = '318000254739';
  $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13';

  // PRODUCTION base URL:
  $clover_base = 'https://scl.clover.com';

  // Convert amount to cents
  $amount_cents = (int) round($amount_raw * 100);

  // Build description (what you want visible to admin)
  $description_parts = [];
  $description_parts[] = 'Purpose: ' . $purpose;
  $description_parts[] = 'Payer: ' . trim($first_name . ' ' . $last_name);
  $description_parts[] = 'Email: ' . $email;
  $description_parts[] = 'Phone: ' . $phone;
  $description_parts[] = 'NO REFUNDS. All payments are NON-REFUNDABLE. Email this receipt to CanSTEM.education@gmail.com.';
  $description = implode(' | ', $description_parts);

  // -------------------------------
  // STEP 1: Create COF Customer (Ecommerce API) using the single-use token as "source"
  // This returns a customer ID that can be used as the source in /v1/charges to link the payment to customer.
  // -------------------------------
  $customer_payload = [
    'ecomind'   => 'ecom',
    'email'     => $email,
    'firstName' => $first_name,
    'lastName'  => $last_name,
    'source'    => $token, // IMPORTANT: token from Clover checkout.js
  ];

  $customer_resp = wp_remote_post(
    $clover_base . '/v1/customers',
    [
      'method'  => 'POST',
      'headers' => [
        'Authorization' => 'Bearer ' . $secret_key,
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
        'x-forwarded-for' => $_SERVER['REMOTE_ADDR'] ?? '',
      ],
      'body'    => wp_json_encode($customer_payload),
      'timeout' => 45,
    ]
  );

  if (is_wp_error($customer_resp)) {
    return new WP_REST_Response(
      ['success' => false, 'error' => 'Gateway error (customer): ' . $customer_resp->get_error_message()],
      500
    );
  }

  $customer_code = wp_remote_retrieve_response_code($customer_resp);
  $customer_body = json_decode(wp_remote_retrieve_body($customer_resp), true);

  if (!($customer_code >= 200 && $customer_code < 300) || empty($customer_body['id'])) {
    $msg = !empty($customer_body['message']) ? $customer_body['message'] : 'Could not create customer.';
    return new WP_REST_Response(['success' => false, 'error' => $msg, 'raw' => $customer_body], 400);
  }

  $customer_id = $customer_body['id'];

  // -------------------------------
  // STEP 2: Create Charge (Ecommerce API) using source = customerId
  // This links the payment to the customer so details appear in Clover dashboard.
  // -------------------------------
  $charge_payload = [
    'merchant_id'   => $merchant_id,
    'amount'        => $amount_cents,
    'currency'      => 'CAD',
    'source'        => $customer_id, // KEY FIX: link charge to customer
    'receipt_email' => $email,
    'description'   => $description,
  ];

  $charge_resp = wp_remote_post(
    $clover_base . '/v1/charges',
    [
      'method'  => 'POST',
      'headers' => [
        'Authorization' => 'Bearer ' . $secret_key,
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
        'x-forwarded-for' => $_SERVER['REMOTE_ADDR'] ?? '',
      ],
      'body'    => wp_json_encode($charge_payload),
      'timeout' => 45,
    ]
  );

  if (is_wp_error($charge_resp)) {
    return new WP_REST_Response(
      ['success' => false, 'error' => 'Gateway error (charge): ' . $charge_resp->get_error_message()],
      500
    );
  }

  $charge_code = wp_remote_retrieve_response_code($charge_resp);
  $charge_body = json_decode(wp_remote_retrieve_body($charge_resp), true);

  if ($charge_code >= 200 && $charge_code < 300 && !empty($charge_body['id'])) {
    return [
      'success'  => true,
      'chargeId' => $charge_body['id'],
      'customerId' => $customer_id,
    ];
  }

  $error_msg = !empty($charge_body['message']) ? $charge_body['message'] : 'Payment declined.';
  return new WP_REST_Response(['success' => false, 'error' => $error_msg, 'raw' => $charge_body], 400);
}