<?php
/**
 * Conexão com o banco de dados.
 * Suporta variáveis de ambiente para deploy (Railway, Docker, etc.)
 */

// Prioriza variáveis do Railway/Docker, senão usa localhost
$servidor = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$usuario  = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$senha    = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$banco    = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'escambo';
$porta    = getenv('MYSQLPORT') ?: '3306';

// Tenta a conexão
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco, $porta);

if (!$conexao) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

// Configura charset para evitar problemas com acentuação
mysqli_set_charset($conexao, "utf8mb4");
