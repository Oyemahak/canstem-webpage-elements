add_action('rest_api_init', function () {
    register_rest_route('canstem', '/charge', [
        'methods' => 'POST',
        'callback' => 'canstem_process_payment',
    ]);
});

function canstem_process_payment(WP_REST_Request $request) {

    $data = $request->get_json_params();

    $token   = sanitize_text_field($data['token']);
    $amount  = floatval($data['amount']) * 100; 
    $email   = sanitize_email($data['email']);
    $purpose = sanitize_text_field($data['purpose']);

    $merchant_id = "318000254739";
    $secret_key  = "97ea1413-4037-f6aa-d8aa-30fb40a75c13";

    $payload = json_encode([
        "merchant_id"   => $merchant_id,
        "amount"        => intval($amount),
        "currency"      => "CAD",
        "source"        => $token,
        "receipt_email" => $email,
        "description"   => $purpose,
    ]);

    $response = wp_remote_post("https://scl.clover.com/v1/charges", [
        "method" => "POST",
        "headers" => [
            "Authorization" => "Bearer " . $secret_key,
            "Content-Type"  => "application/json",
        ],
        "body" => $payload
    ]);

    if (is_wp_error($response)) {
        return ["success" => false];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    return (isset($body["id"])) ?
        ["success" => true] :
        ["success" => false];
}