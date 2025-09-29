<?php
    // Establece la cabecera para indicar que la respuesta es JSON
    header('Content-Type: application/json');

    // --- 1. LEER LOS DATOS QUE ENVÍA JOURNEY BUILDER ---
    $jsonPayload = file_get_contents('php://input');
    $data = json_decode($jsonPayload, true);

    $inArguments = [];
    foreach ($data['inArguments'] as $arg) {
        $inArguments = array_merge($inArguments, $arg);
    }

    $channelNumber = $inArguments['channelNumber'];
    $authToken = $inArguments['authToken'];
    $templateName = $inArguments['templateName'];
    $templateParamsCSV = $inArguments['templateParams'];
    $contactPhoneNumber = $inArguments['phoneNumber'];

    // --- 2. PREPARAR LA LLAMADA A LA API DE EASYPODS ---
    $easypods_url = "https://api-qa.easypods.co/EasyPodsWebhookQA/api/Outbound/{$channelNumber}/SendBulkMessages";

    $params_array = explode(',', $templateParamsCSV);
    $body_parameters = [];
    foreach ($params_array as $param) {
        $body_parameters[] = [
            "type" => "text",
            "text" => trim($param)
        ];
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
                q
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
    curl_close($ch);

    // (Opcional) Puedes guardar la respuesta en un log para depuración futura
    // file_put_contents('easypods_log.txt', "Respuesta: " . $easypods_response . "\n", FILE_APPEND);

    // --- 4. RESPONDER A JOURNEY BUILDER ---
    // Le decimos a Journey Builder que terminamos nuestro trabajo con éxito.
    echo json_encode(['success' => true]);
?>