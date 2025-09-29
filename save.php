<?php
    header('Content-Type: application/json');
    // Journey Builder llama a este endpoint cada vez que se guarda el Journey.
    echo json_encode(['success' => true]);
?>