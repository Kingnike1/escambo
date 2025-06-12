<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require 'conexao.php'; // arquivo que cria $pdo (PDO) ou $conn (mysqli)

$usuario_id = $_SESSION['usuario']['id'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados do formulário
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    // Validação simples
    if (!$titulo || !$autor || !$genero) {
        $erro = "Preencha os campos obrigatórios: título, autor e gênero.";
    } elseif (empty($_FILES['imagem1']['name']) || empty($_FILES['imagem2']['name'])) {
        $erro = "Por favor, envie as duas imagens do livro.";
    } else {
        // Upload das imagens
        $uploadsDir = 'uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Função para salvar imagem e gerar nome único
        function salvarImagem($file) {
            global $uploadsDir;
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid('livro_') . '.' . $ext;
            $caminho = $uploadsDir . $nomeArquivo;
            if (move_uploaded_file($file['tmp_name'], $caminho)) {
                return $nomeArquivo;
            }
            return false;
        }

        $imagem1 = salvarImagem($_FILES['imagem1']);
        $imagem2 = salvarImagem($_FILES['imagem2']);

        if (!$imagem1 || !$imagem2) {
            $erro = "Erro ao enviar as imagens.";
        } else {
            // Insert no banco (usando PDO)
            $sql = "INSERT INTO livros (usuario_id, titulo, autor, genero, descricao, imagem1, imagem2) 
                    VALUES (:usuario_id, :titulo, :autor, :genero, :descricao, :imagem1, :imagem2)";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':titulo' => $titulo,
                ':autor' => $autor,
                ':genero' => $genero,
                ':descricao' => $descricao,
                ':imagem1' => $imagem1,
                ':imagem2' => $imagem2,
            ]);
            if ($ok) {
                $sucesso = "Livro adicionado com sucesso!";
                // Limpar campos
                $titulo = $autor = $genero = $descricao = '';
            } else {
                $erro = "Erro ao salvar no banco de dados.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Adicionar Livro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white max-w-lg w-full rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">➕ Adicionar Livro</h1>

        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block font-semibold mb-1" for="titulo">Título *</label>
                <input id="titulo" name="titulo" type="text" required value="<?= htmlspecialchars($titulo ?? '') ?>"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="autor">Autor *</label>
                <input id="autor" name="autor" type="text" required value="<?= htmlspecialchars($autor ?? '') ?>"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="genero">Gênero *</label>
                <input id="genero" name="genero" type="text" required value="<?= htmlspecialchars($genero ?? '') ?>"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($descricao ?? '') ?></textarea>
            </div>

            <div>
                <label class="block font-semibold mb-1" for="imagem1">Imagem 1 *</label>
                <input id="imagem1" name="imagem1" type="file" accept="image/*" required
                    class="w-full" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="imagem2">Imagem 2 *</label>
                <input id="imagem2" name="imagem2" type="file" accept="image/*" required
                    class="w-full" />
            </div>

            <div class="flex justify-between items-center pt-4">
                <a href="painel.php" class="text-gray-600 hover:underline">← Voltar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                    Salvar Livro
                </button>
            </div>
        </form>
    </div>
</body>
</html>
