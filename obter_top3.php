<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

try {
    // Substitui a VIEW vw_top3 — funciona no InfinityFree
    $sql = "
        SELECT
            nome,
            MAX(pontuacao) AS pontuacao
        FROM ranking
        GROUP BY nome
        ORDER BY pontuacao DESC
        LIMIT 3
    ";

    $stmt = $pdo->query($sql);
    $top3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normaliza tipos (garante int no JSON)
    foreach ($top3 as &$row) {
        $row['nome'] = (string) $row['nome'];
        $row['pontuacao'] = (int) $row['pontuacao'];
    }

    echo json_encode($top3, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar ranking", "detalhe" => $e->getMessage()]);
}