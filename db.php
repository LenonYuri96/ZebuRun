<?php
// Configuração oficial para o servidor InfinityFree
$host = "sql302.infinityfree.com";
$port = 3306;
$dbname = "if0_43229925_zebubattle";
$user = "if0_43229925";
$pass = "102696Lys";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["erro" => "Falha na conexão: " . $e->getMessage()]);
    exit;
}
?>