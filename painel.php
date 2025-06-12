<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Simulação de usuário e livros (substituir por dados do banco depois)
$usuario = $_SESSION['usuario'];
$livros = [
    ['titulo' => 'Dom Quixote', 'autor' => 'Miguel de Cervantes'],
    ['titulo' => '1984', 'autor' => 'George Orwell'],
    ['titulo' => 'O Pequeno Príncipe', 'autor' => 'Antoine de Saint-Exupéry'],
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel do Usuário</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">
            Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!
        </h1>

        <div class="flex gap-4 mb-6">
            <a href="adicionar_livro.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                ➕ Adicionar Livro
            </a>
            <a href="propor_troca.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                🔄 Propor Troca
            </a>
            <a href="ver_trocas.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">
                📚 Ver Trocas
            </a>
        </div>

        <h2 class="text-2xl font-semibold text-gray-700 mb-2">📘 Seus Livros</h2>
        <ul class="space-y-3">
            <?php foreach ($livros as $livro): ?>
                <li class="bg-gray-50 p-4 rounded border border-gray-200">
                    <p class="text-lg font-medium"><?= htmlspecialchars($livro['titulo']) ?></p>
                    <p class="text-sm text-gray-500">Autor: <?= htmlspecialchars($livro['autor']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>
</html>
