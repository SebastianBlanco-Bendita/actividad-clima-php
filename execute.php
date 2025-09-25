<?php
    // Establece la cabecera para indicar que la respuesta es JSON
    header('Content-Type: application/json');
    // Simplemente responde con éxito. Journey Builder solo necesita un 200 OK.
    echo json_encode(['success' => true]);
?>