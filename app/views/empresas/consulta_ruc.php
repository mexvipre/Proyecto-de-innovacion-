<?php
header('Content-Type: application/json');

if (!isset($_POST['ruc'])) {
    echo json_encode(['success' => false, 'message' => 'RUC no recibido']);
    exit;
}

$ruc = $_POST['ruc'];
$token = 'apis-token-15028.FBbCJdfuHXMlG3mtxbHqns0Jsv0t0j91';
$url = "https://api.apis.net.pe/v2/sunat/ruc?numero=$ruc";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode(['success' => false, 'message' => 'Error cURL: '.curl_error($ch)]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'message' => "Error HTTP $httpCode", 'response' => $response]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['ruc']) && !isset($data['numeroDocumento'])) {  // Dependiendo respuesta del API
    echo json_encode(['success' => false, 'message' => 'Datos no encontrados', 'data' => $data]);
} else {
    echo json_encode(['success' => true, 'data' => $data]);
}
