<?php
/**
 * CanSTEM - Clover charge endpoint (FINAL CLEAN VERSION + STAFF EMAIL NOTIFICATION)
 * URL: /wp-json/canstem/charge
 *
 * What this does:
 * 1) Charges customer via Clover
 * 2) Sends a staff notification email (to multiple recipients) AFTER successful charge
 * 3) Returns success + chargeId to frontend
 */

add_action('rest_api_init', function () {
  register_rest_route('canstem', '/charge', [
    'methods'             => 'POST',
    'callback'            => 'canstem_process_payment',
    'permission_callback' => '__return_true',
  ]);
});

/**
 * Send staff email notification (non-blocking)
 */
function canstem_send_payment_notification($details) {

  // ✅ Add as many staff emails / Google Groups as you want here
  $to = [
    'canstem.education@gmail.com',
    'frontdesk@canstemeducation.com',
    // 'payments@canstemeducation.com', // optional
  ];

  $amount   = isset($details['amount']) ? floatval($details['amount']) : 0;
  $name     = sanitize_text_field($details['name'] ?? '');
  $email    = sanitize_email($details['email'] ?? '');
  $phone    = sanitize_text_field($details['phone'] ?? '');
  $purpose  = sanitize_textarea_field($details['purpose'] ?? '');
  $chargeId = sanitize_text_field($details['chargeId'] ?? '');
  $time     = sanitize_text_field($details['time'] ?? '');

  // ✅ Meaningful subject like Clover
  $subject = sprintf(
    'Payment from %s — CA$%s (PAID) | TXN %s',
    $name ?: 'Customer',
    number_format($amount, 2),
    $chargeId ?: 'N/A'
  );

  // ✅ Nice, clean email body
  $body = '
  <div style="font-family:Arial,Helvetica,sans-serif;color:#0f172a;max-width:720px;margin:0 auto;padding:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
      <h2 style="margin:0;color:#001161;">Payment Notification</h2>
      <span style="display:inline-block;background:#16a34a;color:#fff;padding:6px 14px;border-radius:999px;font-weight:700;font-size:12px;">
        PAID
      </span>
    </div>

    <p style="margin:0 0 12px;color:#475569;">
      A new payment was submitted from the CanSTEM payment form. Confirm full details in Clover using the Charge ID below.
    </p>

    <table style="border-collapse:separate;border-spacing:0;width:100%;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
      <tr>
        <th style="text-align:left;background:#f8fafc;width:220px;padding:10px;">Amount</th>
        <td style="padding:10px;"><strong>CA$' . esc_html(number_format($amount, 2)) . '</strong></td>
      </tr>
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;">Payer Name</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;">' . esc_html($name) . '</td>
      </tr>
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;">Payer Email</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;">' . esc_html($email) . '</td>
      </tr>
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;">Phone</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;">' . esc_html($phone) . '</td>
      </tr>
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;">Purpose</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;">' . nl2br(esc_html($purpose)) . '</td>
      </tr>
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;">Payment ID</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;"><code>' . esc_html($chargeId) . '</code></td>
      </tr>
      <tr>
        <th style="text-align:left;background:#f8fafc;padding:10px;border-top:1px solid #e5e7eb;">Submitted At</th>
        <td style="padding:10px;border-top:1px solid #e5e7eb;">' . esc_html($time) . '</td>
      </tr>
    </table>

    <p style="margin-top:12px;color:#64748b;font-size:12px;">
      Note: This is an internal notification email. Replying will go to the payer (if email was provided).
    </p>
  </div>';

  $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

  /**
   * IMPORTANT:
   * For best delivery, keep From domain as canstemeducation.com
   * (and ideally match your SMTP sending account/domain).
   */
  $headers[] = 'From: CanSTEM Payments <payments@canstemeducation.com>';

  // ✅ So staff can reply directly to payer
  if ($email) {
    $headers[] = 'Reply-To: ' . ($name ?: 'Payer') . ' <' . $email . '>';
  }

  return wp_mail($to, $subject, $body, $headers);
}

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
  $secret_key  = '97ea1413-4037-f6aa-d8aa-30fb40a75c13'; // TODO: move to env/secret manager
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

    $charge_id = sanitize_text_field($charge_body['id']);

    // ✅ Send staff notification (do NOT block payment success if email fails)
    try {
      canstem_send_payment_notification([
        'amount'   => $amount_raw,
        'name'     => trim($first_name . ' ' . $last_name),
        'email'    => $email,
        'phone'    => $phone,
        'purpose'  => $purpose,
        'chargeId' => $charge_id,
        'time'     => wp_date('D, M j, Y · g:i a', time(), wp_timezone()),
      ]);
    } catch (Throwable $e) {
      error_log('Payment notification email failed: ' . $e->getMessage());
    }

    return [
      'success'  => true,
      'chargeId' => $charge_id,
    ];
  }

  return new WP_REST_Response(['success' => false, 'error' => 'Transaction declined.'], 400);
}