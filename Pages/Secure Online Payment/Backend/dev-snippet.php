<?php
/**
 * CanSTEM - Clover charge endpoint (FINAL + STAFF EMAIL NOTIFICATION + EXTRA PAYMENT DETAILS)
 * URL: /wp-json/canstem/charge
 *
 * What this does:
 * 1) Charges customer via Clover
 * 2) Attempts to fetch extra payment details (Tender, Card Brand, Order ID, etc.)
 * 3) Sends staff notification email to multiple recipients after successful charge
 * 4) Returns success + paymentId (chargeId) to frontend
 */

add_action('rest_api_init', function () {
  register_rest_route('canstem', '/charge', [
    'methods'             => 'POST',
    'callback'            => 'canstem_process_payment',
    'permission_callback' => '__return_true',
  ]);
});

/**
 * Small helper: safe array getter
 */
function canstem_arr_get($arr, $key, $default = '') {
  return isset($arr[$key]) ? $arr[$key] : $default;
}

/**
 * Clover helper: request wrapper
 */
function canstem_clover_request($method, $url, $secret_key, $ip = '', $body = null) {

  $args = [
    'method'  => $method,
    'headers' => [
      'Authorization'   => 'Bearer ' . $secret_key,
      'Content-Type'    => 'application/json',
      'Accept'          => 'application/json',
      'x-forwarded-for' => $ip,
    ],
    'timeout' => 45,
  ];

  if (!is_null($body)) {
    $args['body'] = wp_json_encode($body);
  }

  $resp = wp_remote_request($url, $args);

  if (is_wp_error($resp)) {
    return ['ok' => false, 'error' => $resp->get_error_message(), 'data' => null];
  }

  $code = wp_remote_retrieve_response_code($resp);
  $raw  = wp_remote_retrieve_body($resp);
  $data = json_decode($raw, true);

  if ($code < 200 || $code >= 300) {
    return [
      'ok'    => false,
      'error' => 'Clover API error (HTTP ' . $code . ')',
      'data'  => is_array($data) ? $data : ['raw' => $raw],
    ];
  }

  return ['ok' => true, 'error' => null, 'data' => $data];
}

/**
 * Try to fetch extra details from Clover Payments API using Payment ID
 * NOTE: This is best-effort. If Clover blocks/doesn't return fields, email still sends.
 */
function canstem_fetch_clover_payment_details($merchant_id, $secret_key, $payment_id, $ip = '') {

  // Many Clover setups use:
  // https://api.clover.com/v3/merchants/{mId}/payments/{paymentId}
  // If your account uses a different base, we fall back gracefully.
  $base_candidates = [
    'https://api.clover.com',     // common
    'https://scl.clover.com',     // your current base
  ];

  foreach ($base_candidates as $base) {
    $url = rtrim($base, '/') . '/v3/merchants/' . rawurlencode($merchant_id) . '/payments/' . rawurlencode($payment_id);

    $r = canstem_clover_request('GET', $url, $secret_key, $ip, null);

    if ($r['ok'] && is_array($r['data'])) {
      return $r['data'];
    }
  }

  return null;
}

/**
 * Send staff email notification (non-blocking)
 */
function canstem_send_payment_notification($details) {

  // ✅ Add/remove recipients here (Gmail + Google Groups + aliases are all OK)
  $to = [
    'canstem.education@gmail.com',
    'frontdesk@canstemeducation.com',
    'payments@canstemeducation.com',
  ];

  // Basic fields
  $amount   = floatval(canstem_arr_get($details, 'amount', 0));
  $name     = sanitize_text_field(canstem_arr_get($details, 'name', ''));
  $email    = sanitize_email(canstem_arr_get($details, 'email', ''));
  $phone    = sanitize_text_field(canstem_arr_get($details, 'phone', ''));
  $purpose  = sanitize_textarea_field(canstem_arr_get($details, 'purpose', ''));
  $time     = sanitize_text_field(canstem_arr_get($details, 'time', ''));
  $paymentId = sanitize_text_field(canstem_arr_get($details, 'paymentId', ''));

  // Extra Clover-like fields (best-effort)
  $orderId      = sanitize_text_field(canstem_arr_get($details, 'orderId', ''));
  $invoiceNo    = sanitize_text_field(canstem_arr_get($details, 'invoiceNumber', ''));
  $tender       = sanitize_text_field(canstem_arr_get($details, 'tender', ''));
  $cardBrand    = sanitize_text_field(canstem_arr_get($details, 'cardBrand', ''));
  $cardNumber   = sanitize_text_field(canstem_arr_get($details, 'cardNumber', ''));
  $entryType    = sanitize_text_field(canstem_arr_get($details, 'entryType', ''));
  $authCode     = sanitize_text_field(canstem_arr_get($details, 'authCode', ''));
  $transactionNo= sanitize_text_field(canstem_arr_get($details, 'transactionNo', ''));
  $externalPaymentId = sanitize_text_field(canstem_arr_get($details, 'externalPaymentId', ''));

  $taxAmount = canstem_arr_get($details, 'taxAmount', '');
  $tipAmount = canstem_arr_get($details, 'tipAmount', '');

  // ✅ Subject like Clover
  $subject = sprintf(
    'Payment from %s — CA$%s (PAID) | TXN %s',
    $name ?: 'Customer',
    number_format($amount, 2),
    $paymentId ?: 'N/A'
  );

  // Helper: row output (only if value exists)
  $row = function($label, $value, $is_code = false) {
    if ($value === '' || $value === null) return '';
    $v = $is_code ? '<code>' . esc_html($value) . '</code>' : esc_html($value);
    return '
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;width:220px;">' . esc_html($label) . '</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;">' . $v . '</td>
      </tr>';
  };

  $body = '
  <div style="font-family:Arial,Helvetica,sans-serif;color:#0f172a;max-width:760px;margin:0 auto;padding:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
      <h2 style="margin:0;color:#001161;">Payment Notification</h2>
      <span style="display:inline-block;background:#16a34a;color:#fff;padding:6px 14px;border-radius:999px;font-weight:700;font-size:12px;">
        PAID
      </span>
    </div>

    <p style="margin:0 0 12px;color:#475569;">
      A new payment was submitted from the CanSTEM payment form.
      Confirm full details in Clover using the Payment ID (TXN) below.
    </p>

    <table style="border-collapse:separate;border-spacing:0;width:100%;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
      <tr>
        <th style="text-align:left;background:#f8fafc;width:220px;padding:10px;">Amount</th>
        <td style="padding:10px;"><strong>CA$' . esc_html(number_format($amount, 2)) . '</strong></td>
      </tr>

      ' . $row('Payer Name', $name) . '
      ' . $row('Payer Email', $email) . '
      ' . $row('Phone', $phone) . '
      ' . $row('Purpose', $purpose) . '

      ' . $row('Payment ID (TXN)', $paymentId, true) . '
      ' . $row('Order ID', $orderId, true) . '
      ' . $row('Invoice Number', $invoiceNo, true) . '
      ' . $row('External Payment ID', $externalPaymentId, true) . '

      ' . $row('Tender', $tender) . '
      ' . $row('Card Brand', $cardBrand) . '
      ' . $row('Card Number', $cardNumber) . '
      ' . $row('Card Entry Type', $entryType) . '
      ' . $row('Auth Code', $authCode) . '
      ' . $row('Transaction #', $transactionNo) . '

      ' . ($taxAmount !== '' ? $row('Tax Amount (cents)', strval($taxAmount)) : '') . '
      ' . ($tipAmount !== '' ? $row('Tip Amount (cents)', strval($tipAmount)) : '') . '

      ' . $row('Submitted At', $time) . '
    </table>

    <p style="margin-top:12px;color:#64748b;font-size:12px;">
      Note: This is an internal notification email. Replying will go to the payer (if email was provided).
    </p>
  </div>';

  $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

  // ✅ This controls the sender label you see in Google Groups (no more "Student Request")
  $headers[] = 'From: Payment Submission <payments@canstemeducation.com>';

  // ✅ So staff can reply directly to payer
  if ($email) {
    $headers[] = 'Reply-To: ' . ($name ?: 'Payer') . ' <' . $email . '>';
  }

  return wp_mail($to, $subject, $body, $headers);
}

/**
 * Main endpoint callback
 */
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
  $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13'; // TODO: move to env later
  $clover_base = 'https://scl.clover.com';

  $amount_cents = (int) round($amount_raw * 100);
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';

  // ==============================
  // STEP 1: CREATE CUSTOMER
  // ==============================
  $customer_payload = [
    'ecomind'   => 'ecom',
    'email'     => $email,
    'firstName' => $first_name,
    'lastName'  => $last_name,
    'phone'     => $phone,
    'source'    => $token,
  ];

  $customer_resp = canstem_clover_request('POST', $clover_base . '/v1/customers', $secret_key, $ip, $customer_payload);
  if (!$customer_resp['ok']) {
    return new WP_REST_Response(['success' => false, 'error' => 'Customer creation failed.'], 500);
  }

  $customer_body = $customer_resp['data'];
  if (empty($customer_body['id'])) {
    return new WP_REST_Response(['success' => false, 'error' => 'Unable to create customer.'], 400);
  }

  $customer_id = $customer_body['id'];

  // ==============================
  // STEP 2: RECEIPT NOTE (PRINTED BY CLOVER)
  // ==============================
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
    'receipt_email' => $email,
    'description'   => $description,
  ];

  $charge_resp = canstem_clover_request('POST', $clover_base . '/v1/charges', $secret_key, $ip, $charge_payload);
  if (!$charge_resp['ok']) {
    return new WP_REST_Response(['success' => false, 'error' => 'Payment failed.'], 500);
  }

  $charge_body = $charge_resp['data'];

  if (!empty($charge_body['id'])) {

    $payment_id = sanitize_text_field($charge_body['id']); // this matches what you see as TXN in your notifications

    // ==============================
    // STEP 4: FETCH EXTRA PAYMENT DETAILS (BEST-EFFORT)
    // ==============================
    $extra = canstem_fetch_clover_payment_details($merchant_id, $secret_key, $payment_id, $ip);

    // Map extra details (only if available)
    $mapped = [
      'orderId' => '',
      'invoiceNumber' => '',
      'tender' => '',
      'cardBrand' => '',
      'cardNumber' => '',
      'entryType' => '',
      'authCode' => '',
      'transactionNo' => '',
      'externalPaymentId' => '',
      'taxAmount' => '',
      'tipAmount' => '',
    ];

    if (is_array($extra)) {

      // Common Clover payment object structure
      $mapped['externalPaymentId'] = sanitize_text_field(canstem_arr_get($extra, 'externalPaymentId', ''));

      // order.id might be nested or a direct id depending on API response
      if (!empty($extra['order']['id'])) {
        $mapped['orderId'] = sanitize_text_field($extra['order']['id']);
      } elseif (!empty($extra['orderId'])) {
        $mapped['orderId'] = sanitize_text_field($extra['orderId']);
      }

      // Tender label
      if (!empty($extra['tender']['label'])) {
        $mapped['tender'] = sanitize_text_field($extra['tender']['label']);
      } elseif (!empty($extra['tender']['labelKey'])) {
        $mapped['tender'] = sanitize_text_field($extra['tender']['labelKey']);
      }

      // Amount pieces (often in cents)
      if (isset($extra['taxAmount'])) $mapped['taxAmount'] = $extra['taxAmount'];
      if (isset($extra['tipAmount'])) $mapped['tipAmount'] = $extra['tipAmount'];

      // Card transaction fields
      if (!empty($extra['cardTransaction']) && is_array($extra['cardTransaction'])) {
        $ct = $extra['cardTransaction'];

        $mapped['cardBrand'] = sanitize_text_field(canstem_arr_get($ct, 'cardType', ''));
        $mapped['entryType'] = sanitize_text_field(canstem_arr_get($ct, 'entryType', ''));

        $first6 = sanitize_text_field(canstem_arr_get($ct, 'first6', ''));
        $last4  = sanitize_text_field(canstem_arr_get($ct, 'last4', ''));
        if ($first6 && $last4) {
          $mapped['cardNumber'] = $first6 . '••••••' . $last4; // masked like exports
        } elseif ($last4) {
          $mapped['cardNumber'] = '•••• ' . $last4;
        }

        $mapped['authCode'] = sanitize_text_field(canstem_arr_get($ct, 'authCode', ''));
        $mapped['transactionNo'] = sanitize_text_field(canstem_arr_get($ct, 'transactionNo', ''));
      }

      // Invoice number: Clover exports may call it "invoiceNumber" or you may not get it from API.
      // If your API provides it, it will fill here automatically.
      $mapped['invoiceNumber'] = sanitize_text_field(canstem_arr_get($extra, 'invoiceNumber', ''));
    }

    // ==============================
    // STEP 5: SEND STAFF EMAIL (DO NOT BLOCK SUCCESS)
    // ==============================
    try {
      canstem_send_payment_notification(array_merge([
        'amount'    => $amount_raw,
        'name'      => trim($first_name . ' ' . $last_name),
        'email'     => $email,
        'phone'     => $phone,
        'purpose'   => $purpose,
        'paymentId' => $payment_id,
        'time'      => wp_date('D, M j, Y · g:i a', time(), wp_timezone()),
      ], $mapped));
    } catch (Throwable $e) {
      error_log('Payment notification email failed: ' . $e->getMessage());
    }

    return [
      'success'  => true,
      'paymentId' => $payment_id,
    ];
  }

  return new WP_REST_Response(['success' => false, 'error' => 'Transaction declined.'], 400);
}