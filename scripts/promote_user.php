<?php
// USO: php scripts/promote_user.php usuario@email.com
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ser ejecutado desde la terminal.\n");
}
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/models/Database.php';
if ($argc < 2) {
    echo "Error: Debes proporcionar el email del usuario a promover.\n";
    echo "Uso: php " . $argv[0] . " usuario@email.com\n";
    exit(1);
}
$email = $argv[1];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE usuarios SET role = 'admin' WHERE email = ?");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $db->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "¡Éxito! El usuario con el email '" . $email . "' ha sido promovido a administrador.\n";
    } else {
        echo "No se encontró ningún usuario con el email '" . $email . "'.\n";
    }
    $stmt->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
