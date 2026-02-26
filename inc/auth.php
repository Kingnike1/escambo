<?php
/**
 * Verifica se o usuário está autenticado.
 * Se não estiver, redireciona para a página de login.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}
