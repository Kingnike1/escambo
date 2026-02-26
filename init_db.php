<?php
require_once __DIR__ . '/inc/conexao.php';

$sql = file_get_contents(__DIR__ . '/codigo-banco.sql');

// Divide o SQL por ponto e vírgula para executar cada comando separadamente
// Remove comentários e linhas vazias
$commands = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = 0;

foreach ($commands as $command) {
    if (!empty($command)) {
        if (mysqli_query($conexao, $command)) {
            $success++;
        } else {
            echo "Erro no comando: " . mysqli_error($conexao) . "\n";
            $errors++;
        }
    }
}

echo "Inicialização concluída: $success comandos executados, $errors erros.\n";
