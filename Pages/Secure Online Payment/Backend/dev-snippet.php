<?php
/**
 * CanSTEM – Clover charge endpoint (Ecommerce Customer + Charge linking)
 * URL used by JS: /wp-json/canstem/charge
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

  $token      = sanitize_text_field($data['token']); // clv_... token
  $amount_raw = floatval($data['amount']);
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
  // IMPORTANT: Move secret key to wp-config.php or environment variable in production.
  $merchant_id = '318000254739';
  $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13';

  $clover_base = 'https://scl.clover.com';
  $amount_cents = (int) round($amount_raw * 100);

  $ip = $_SERVER['REMOTE_ADDR'] ?? '';

  // -------------------------------
  // STEP 1: Create Card-on-File (COF) customer (Ecommerce API)
  // This is what makes the customer visible/linked in Clover.
  // -------------------------------
  $customer_payload = [
    'ecomind'   => 'ecom',
    'email'     => $email,
    'firstName' => $first_name,
    'lastName'  => $last_name,
    'phone'     => $phone,      // save phone on customer profile
    'source'    => $token,      // token from Clover hosted tokenization
  ];

  $customer_resp = wp_remote_post(
    $clover_base . '/v1/customers',
    [
      'method'  => 'POST',
      'headers' => [
        'Authorization'  => 'Bearer ' . $secret_key,
        'Content-Type'   => 'application/json',
        'Accept'         => 'application/json',
        'x-forwarded-for'=> $ip,
      ],
      'body'    => wp_json_encode($customer_payload),
      'timeout' => 45,
    ]
  );

  if (is_wp_error($customer_resp)) {
    return new WP_REST_Response(['success' => false, 'error' => 'Gateway error (customer): ' . $customer_resp->get_error_message()], 500);
  }

  $customer_code = wp_remote_retrieve_response_code($customer_resp);
  $customer_body = json_decode(wp_remote_retrieve_body($customer_resp), true);

  if (!($customer_code >= 200 && $customer_code < 300) || empty($customer_body['id'])) {
    $msg = !empty($customer_body['message']) ? $customer_body['message'] : 'Could not create customer.';
    return new WP_REST_Response(['success' => false, 'error' => $msg, 'raw' => $customer_body], 400);
  }

  $customer_id = $customer_body['id'];

  // -------------------------------
  // STEP 2: Create Charge using source = customerId (links payment to customer)
  // IMPORTANT CHANGE:
  // - Keep description SHORT so it doesn't clutter the receipt.
  // - Put purpose/details into metadata instead.
  // -------------------------------
  $charge_payload = [
    'merchant_id'   => $merchant_id,
    'amount'        => $amount_cents,
    'currency'      => 'CAD',
    'source'        => $customer_id,
    'receipt_email' => $email,

    // Keep this short so Clover doesn't print a big "purpose" block
    'description'   => 'CanSTEM Education Payment',

    // Use metadata for your internal tracking
    'metadata'      => [
      'payer_name' => trim($first_name . ' ' . $last_name),
      'payer_email'=> $email,
      'payer_phone'=> $phone,
      'purpose'    => $purpose,
      'site'       => 'canstemeducation.com/payment',
    ],

    // Optional: helps search/filter (keep it short)
    'external_reference_id' => 'CS-' . time(),
  ];

  $charge_resp = wp_remote_post(
    $clover_base . '/v1/charges',
    [
      'method'  => 'POST',
      'headers' => [
        'Authorization'  => 'Bearer ' . $secret_key,
        'Content-Type'   => 'application/json',
        'Accept'         => 'application/json',
        'x-forwarded-for'=> $ip,
      ],
      'body'    => wp_json_encode($charge_payload),
      'timeout' => 45,
    ]
  );

  if (is_wp_error($charge_resp)) {
    return new WP_REST_Response(['success' => false, 'error' => 'Gateway error (charge): ' . $charge_resp->get_error_message()], 500);
  }

  $charge_code = wp_remote_retrieve_response_code($charge_resp);
  $charge_body = json_decode(wp_remote_retrieve_body($charge_resp), true);

  if ($charge_code >= 200 && $charge_code < 300 && !empty($charge_body['id'])) {
    return [
      'success'    => true,
      'chargeId'   => $charge_body['id'],
      'customerId' => $customer_id,
    ];
  }

  $error_msg = !empty($charge_body['message']) ? $charge_body['message'] : 'Payment declined.';
  return new WP_REST_Response(['success' => false, 'error' => $error_msg, 'raw' => $charge_body], 400);
}