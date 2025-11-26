<?php
// Client ID dari Imgur
$client_id = "b5b64b98f04cb37"; // <- GANTI DI SINI

if (!isset($_FILES['image'])) {
    echo json_encode(["success" => false, "message" => "No image uploaded."]);
    exit;
}

$image = file_get_contents($_FILES['image']['tmp_name']);
$base64 = base64_encode($image);

$opts = [
    "http" => [
        "method"  => "POST",
        "header"  => "Authorization: Client-ID $client_id\r\n" .
                     "Content-Type: application/x-www-form-urlencoded",
        "content" => http_build_query(['image' => $base64])
    ]
];

$context = stream_context_create($opts);
$response = file_get_contents("https://api.imgur.com/3/image", false, $context);
$result = json_decode($response, true);

if (isset($result['success']) && $result['success']) {
    echo json_encode(["success" => true, "link" => $result['data']['link']]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $result['data']['error'] ?? "Unknown error"
    ]);
}
?>
