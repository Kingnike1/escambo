<?php
/**
 * Conexão com o banco de dados MySQL/MariaDB
 * Utiliza MySQLi com tratamento de erros e charset UTF-8.
 */

$servidor = getenv('DB_HOST') ?: 'db';
$usuario  = getenv('DB_USER') ?: 'root';
$senha    = getenv('DB_PASS') ?: '123';
$banco    = getenv('DB_NAME') ?: 'escambo';

$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conexao) {
    http_response_code(503);
    die('Erro de conexão com o banco de dados: ' . mysqli_connect_error());
}

mysqli_set_charset($conexao, 'utf8mb4');
