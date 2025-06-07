<?php
if (isset($_POST['dni'])) {
    $dni = $_POST['dni'];

    $token = 'apis-token-15028.FBbCJdfuHXMlG3mtxbHqns0Jsv0t0j91';
    $apiUrl = "https://api.apis.net.pe/v2/reniec/dni?numero=$dni";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token"
    ]);

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . curl_error($ch)]);
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $responseData = json_decode($response, true);

            if (isset($responseData['numeroDocumento'])) {
                echo json_encode(['success' => true, 'data' => $responseData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'DNI no encontrado']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error HTTP ' . $http_code, 'response' => $response]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'DNI no recibido']);
}
?>
