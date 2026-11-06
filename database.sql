-- ========================================================
-- PROJETO: ZebuBattle
-- CRIAÇÃO COMPLETA DO BANCO DE DADOS, TABELAS E VIEWS
-- ========================================================
-- 1. CRIAÇÃO DO BANCO DE DADOS
CREATE DATABASE IF NOT EXISTS `zebubattle` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. SELEÇÃO DO BANCO PARA USO
USE `zebubattle`;

-- 3. TABELA DE RANKING (HISTÓRICO DE PONTUAÇÃO)
CREATE TABLE IF NOT EXISTS `ranking` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(15) NOT NULL,
    `pontuacao` INT NOT NULL DEFAULT 0,
    `data_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pontuacao` (`pontuacao` DESC),
    INDEX `idx_nome` (`nome`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4. TABELA DE ESTATÍSTICAS GERAIS (PARTIDAS E SOCOS)
CREATE TABLE IF NOT EXISTS `estatisticas_jogo` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `total_partidas` INT NOT NULL DEFAULT 0,
    `total_socos` INT NOT NULL DEFAULT 0,
    `recorde_historico` INT NOT NULL DEFAULT 0,
    `ultima_atualizacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- INICIALIZA OS CONTADORES
INSERT
    IGNORE INTO `estatisticas_jogo` (
        `id`,
        `total_partidas`,
        `total_socos`,
        `recorde_historico`
    )
VALUES
    (1, 0, 0, 0);

-- 5. VIEW DO TOP 3 DO SERTÃO (AGRUPADO POR JOGADOR)
CREATE
OR REPLACE VIEW `vw_top3` AS
SELECT
    `nome`,
    MAX(`pontuacao`) AS `pontuacao`,
    MAX(`data_registro`) AS `data_registro`
FROM
    `ranking`
GROUP BY
    `nome`
ORDER BY
    `pontuacao` DESC
LIMIT
    3;

-- 6. DADOS INICIAIS DE TESTE (PARA O PÓDIO NÃO FICAR VAZIO)
INSERT INTO
    `ranking` (`nome`, `pontuacao`)
VALUES
    ('Boi Bandido', 1850),
    ('Nelore Brabo', 1420),
    ('Zebu Raçudo', 980),
    ('Caracu Veloz', 750);