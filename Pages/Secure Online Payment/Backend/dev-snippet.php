<?php
/**
 * CanSTEM – Clover charge endpoint (FINAL CLEAN VERSION)
 * URL: /wp-json/canstem/charge
 */

add_action('rest_api_init', function () {
  register_rest_route('canstem', '/charge', [
    'methods'             => 'POST',
    'callback'            => 'canstem_process_payment',
    'permission_callback' => '__return_true',
  ]);
});

function canstem_process_payment(WP_REST_Request $request) {

  $data = $request->get_json_params();

  if (empty($data['token']) || empty($data['amount'])) {
    return new WP_REST_Response(['success' => false, 'error' => 'Missing payment data.'], 400);
  }

  $token      = sanitize_text_field($data['token']);
  $amount_raw = floatval($data['amount']);

  if ($amount_raw <= 0) {
    return new WP_REST_Response(['success' => false, 'error' => 'Invalid amount.'], 400);
  }

  $first_name = sanitize_text_field($data['firstName'] ?? '');
  $last_name  = sanitize_text_field($data['lastName'] ?? '');
  $email      = sanitize_email($data['email'] ?? '');
  $phone      = sanitize_text_field($data['phone'] ?? '');
  $purpose    = sanitize_textarea_field($data['purpose'] ?? '');

  if (!$first_name || !$last_name || !$email || !$phone || !$purpose) {
    return new WP_REST_Response(['success' => false, 'error' => 'All fields are required.'], 400);
  }

  // ==============================
  // CLOVER CREDENTIALS (PRODUCTION)
  // ==============================
  $merchant_id = '318000254739';
  $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13'; // MOVE TO ENV LATER
  $clover_base = 'https://scl.clover.com';

  $amount_cents = (int) round($amount_raw * 100);
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';

  // ==============================
  // STEP 1: CREATE COF CUSTOMER
  // ==============================
  $customer_payload = [
    'ecomind'   => 'ecom',
    'email'     => $email,
    'firstName' => $first_name,
    'lastName'  => $last_name,
    'phone'     => $phone,
    'source'    => $token,
  ];

  $customer_resp = wp_remote_post(
    $clover_base . '/v1/customers',
    [
      'headers' => [
        'Authorization'   => 'Bearer ' . $secret_key,
        'Content-Type'    => 'application/json',
        'Accept'          => 'application/json',
        'x-forwarded-for' => $ip,
      ],
      'body'    => wp_json_encode($customer_payload),
      'timeout' => 45,
    ]
  );

  if (is_wp_error($customer_resp)) {
    return new WP_REST_Response(['success' => false, 'error' => 'Customer creation failed.'], 500);
  }

  $customer_body = json_decode(wp_remote_retrieve_body($customer_resp), true);

  if (empty($customer_body['id'])) {
    return new WP_REST_Response(['success' => false, 'error' => 'Unable to create customer.'], 400);
  }

  $customer_id = $customer_body['id'];

  // ==============================
  // STEP 2: CREATE CLEAN RECEIPT NOTE
  // ==============================
  // THIS IS THE ONLY TEXT CLOVER WILL PRINT
  $description = sprintf(
    'Purpose: %s | Payer: %s | Phone: %s',
    $purpose,
    trim($first_name . ' ' . $last_name),
    $phone
  );

  // ==============================
  // STEP 3: CREATE CHARGE
  // ==============================
  $charge_payload = [
    'merchant_id'   => $merchant_id,
    'amount'        => $amount_cents,
    'currency'      => 'CAD',
    'source'        => $customer_id,
    'receipt_email' => $email, // sends email but does NOT print it
    'description'   => $description,
  ];

  $charge_resp = wp_remote_post(
    $clover_base . '/v1/charges',
    [
      'headers' => [
        'Authorization'   => 'Bearer ' . $secret_key,
        'Content-Type'    => 'application/json',
        'Accept'          => 'application/json',
        'x-forwarded-for' => $ip,
      ],
      'body'    => wp_json_encode($charge_payload),
      'timeout' => 45,
    ]
  );

  if (is_wp_error($charge_resp)) {
    return new WP_REST_Response(['success' => false, 'error' => 'Payment failed.'], 500);
  }

  $charge_body = json_decode(wp_remote_retrieve_body($charge_resp), true);

  if (!empty($charge_body['id'])) {
    return [
      'success'  => true,
      'chargeId'=> $charge_body['id'],
    ];
  }

  return new WP_REST_Response(['success' => false, 'error' => 'Transaction declined.'], 400);
}