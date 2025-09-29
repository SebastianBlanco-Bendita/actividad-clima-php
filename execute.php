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
    
    // CAMBIO 1: URL actualizada para coincidir exactamente con la documentación (con el espacio).
    // cURL se encargará de codificar el espacio como %20.
    $easypods_url = "https://api-qa.easypods.co/EasyPods WebhookQA/api/Outbound/{$channelNumber}/SendBulkMessages"; [cite_start]// [cite: 1]

    // Preparar los parámetros para el template
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
    
    // CAMBIO 2: La estructura del Body ahora es un array que contiene UN objeto de mensaje.
    // La documentación especifica que el body es un array de mensajes: [{...}, {...}].
    // Para un solo envío, la estructura correcta es un array con un único objeto: [{...}]
    $messageObject = [
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
    ];

    $messageBody = [$messageObject]; [cite_start]// El body es un array que contiene el objeto del mensaje. [cite: 1]
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . [cite_start]$authToken // [cite: 1]
    ];

    // --- 3. EJECUTAR LA LLAMADA A LA API CON cURL ---
    $ch = curl_init($easypods_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); [cite_start]// [cite: 1]
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageBody));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $easypods_response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log mejorado para depuración
    file_put_contents(
        'easypods_log.txt', 
        "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
        "Endpoint: " . $easypods_url . "\n" .
        "Request Body: " . json_encode($messageBody) . "\n" .
        "Status Code: " . $httpcode . "\n" .
        "Response: " . $easypods_response . "\n\n", 
        FILE_APPEND
    );

    // --- 4. RESPONDER A JOURNEY BUILDER ---
    echo json_encode(['success' => true]);
?>
