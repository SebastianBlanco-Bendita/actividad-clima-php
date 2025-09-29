<?php
    header('Content-Type: application/json');

    // --- 1. LEER LOS DATOS QUE ENVÍA JOURNEY BUILDER ---
    $jsonPayload = file_get_contents('php://input');
    $data = json_decode($jsonPayload, true);

    $inArguments = [];
    if (isset($data['inArguments']) && is_array($data['inArguments'])) {
        foreach ($data['inArguments'] as $arg) {
            $inArguments = array_merge($inArguments, $arg);
        }
    }

    $channelNumber = $inArguments['channelNumber'] ?? '';
    $authToken = $inArguments['authToken'] ?? '';
    $templateName = $inArguments['templateName'] ?? '';
    $templateParamsCSV = $inArguments['templateParams'] ?? '';
    $contactPhoneNumber = $inArguments['phoneNumber'] ?? '';

    // --- 2. PREPARAR LA LLAMADA A LA API DE EASYPODS ---
    $easypods_url = "https://api-qa.easypods.co/EasyPodsWebhookQA/api/Outbound/{$channelNumber}/SendBulkMessages";

    $params_array = explode(',', $templateParamsCSV);
    $body_parameters = [];
    foreach ($params_array as $param) {
        $trimmed_param = trim($param);
        if (!empty($trimmed_param)) {
            $body_parameters[] = [
                "type" => "text",
                "text" => $trimmed_param
            ];
        }
    }
    
    $messageBody = [[
        "messaging_product" => "whatsapp",
        "to" => $contactPhoneNumber,
        "type" => "template",
        "template" => [
            "name" => $templateName,
            "components" => [[
                "type" => "body",
                "parameters" => $body_parameters
            ]],
            "language" => [
                "code" => "es"
            ]
        ]
    ]];
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . $authToken
    ];

    // --- 3. EJECUTAR LA LLAMADA A LA API CON cURL ---
    $ch = curl_init($easypods_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageBody));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $easypods_response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Opcional: Guardar la respuesta y el código de estado para depuración
    file_put_contents(
        'easypods_log.txt', 
        "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
        "Status Code: " . $httpcode . "\n" .
        "Response: " . $easypods_response . "\n\n", 
        FILE_APPEND
    );

    // --- 4. RESPONDER A JOURNEY BUILDER ---
    echo json_encode(['success' => true]);
?>
