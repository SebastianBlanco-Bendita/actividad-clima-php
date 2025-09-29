<?php
    header('Content-Type: application/json');
    // Journey Builder llama a este endpoint cuando el Journey se activa.
    echo json_encode(['success' => true]);
?>