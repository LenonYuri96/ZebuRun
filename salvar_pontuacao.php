<?php
/**
 * ZEBUBATTLE — salvar_pontuacao.php
 * Compatível com InfinityFree (sem VIEW, sem PROCEDURE)
 * 1 linha por jogador, guarda só a MAIOR pontuação
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

/* ---------- 1. Lê e valida entrada ---------- */
$raw = file_get_contents('php://input');
$dados = json_decode($raw, true);

if (!$dados || empty($dados['nome']) || !isset($dados['pontuacao'])) {
    http_response_code(400);
    echo json_encode(["status" => "ignorado", "motivo" => "dados_invalidos"]);
    exit;
}

$nome = substr(strip_tags(trim($dados['nome'])), 0, 15);
$pontos = intval($dados['pontuacao']);

if ($pontos <= 0 || $nome === '') {
    echo json_encode(["status" => "ignorado", "motivo" => "pontuacao_ou_nome_invalido"]);
    exit;
}

/* ---------- 2. Grava no ranking ---------- */
$modo = 'desconhecido';

try {
    /* 2a. Tentativa ideal: UPSERT (precisa de UNIQUE KEY em `nome`) */
    $stmt = $pdo->prepare("
        INSERT INTO ranking (nome, pontuacao)
        VALUES (:nome, :pontos)
        ON DUPLICATE KEY UPDATE
            pontuacao = GREATEST(pontuacao, VALUES(pontuacao))
    ");
    $stmt->execute([':nome' => $nome, ':pontos' => $pontos]);
    $modo = 'upsert';

} catch (PDOException $e) {
    /* 2b. Fallback: sem UNIQUE, faz SELECT + INSERT/UPDATE manual */
    try {
        $check = $pdo->prepare("
            SELECT id, pontuacao
            FROM ranking
            WHERE nome = :nome
            ORDER BY pontuacao DESC
            LIMIT 1
        ");
        $check->execute([':nome' => $nome]);
        $existente = $check->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            if ($pontos > (int) $existente['pontuacao']) {
                $up = $pdo->prepare("UPDATE ranking SET pontuacao = :pontos WHERE id = :id");
                $up->execute([':pontos' => $pontos, ':id' => $existente['id']]);
                $modo = 'update';
            } else {
                $modo = 'sem_alteracao';
            }
        } else {
            $ins = $pdo->prepare("INSERT INTO ranking (nome, pontuacao) VALUES (:nome, :pontos)");
            $ins->execute([':nome' => $nome, ':pontos' => $pontos]);
            $modo = 'insert';
        }
    } catch (PDOException $e2) {
        http_response_code(500);
        echo json_encode([
            "status" => "erro",
            "detalhe" => $e2->getMessage()
        ]);
        exit;
    }
}

/* ---------- 3. Atualiza estatísticas gerais ---------- */
try {
    /* total_partidas += 1 e recorde se aplicável */
    $pdo->prepare("
        UPDATE estatisticas_jogo
        SET
            total_partidas = total_partidas + 1,
            recorde_historico = GREATEST(recorde_historico, :pts)
        WHERE id = 1
    ")->execute([':pts' => $pontos]);

} catch (PDOException $e) {
    /* Estatística é opcional — não quebra o salvamento principal */
}

/* ---------- 4. Resposta ---------- */
echo json_encode([
    "status" => "sucesso",
    "modo" => $modo,
    "nome" => $nome,
    "pontos" => $pontos
]);