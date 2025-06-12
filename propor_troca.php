<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];
$erro = '';
$sucesso = '';

/**
 * Buscar livros do usuário logado
 */
$sqlMeusLivros = "
  SELECT livro_id AS id_livro, livro_titulo AS titulo
  FROM livro
  WHERE usuario_id = ?
";
$stmt = mysqli_prepare($conexao, $sqlMeusLivros);
mysqli_stmt_bind_param($stmt, "i", $idUsuario);

mysqli_stmt_execute($stmt);
$resultMeusLivros = mysqli_stmt_get_result($stmt);
$meusLivros = mysqli_fetch_all($resultMeusLivros, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

/**
 * Buscar livros de outros usuários
 */
$sqlOutrosLivros = "
  SELECT l.livro_id AS id_livro, l.livro_titulo AS titulo, u.usuario_nome
  FROM livro l
  JOIN usuario u ON l.usuario_id = u.id_usuario
  WHERE l.usuario_id != ?
";
$stmt = mysqli_prepare($conexao, $sqlOutrosLivros);
mysqli_stmt_bind_param($stmt, "i", $idUsuario);

mysqli_stmt_execute($stmt);
$resultOutrosLivros = mysqli_stmt_get_result($stmt);
$outrosLivros = mysqli_fetch_all($resultOutrosLivros, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

/**
 * Processar envio do formulário
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idLivroDesejado = intval($_POST['livro_desejado'] ?? 0);
  $idLivroOferecido = intval($_POST['livro_oferecido'] ?? 0);

  if (!$idLivroDesejado || !$idLivroOferecido) {
    $erro = "Escolha ambos os livros para propor a troca.";
  } else {
    // Buscar dono do livro desejado
  $sqlDestinatario = "SELECT usuario_id FROM livro WHERE livro_id = ?";
    $stmt = mysqli_prepare($conexao, $sqlDestinatario);
    mysqli_stmt_bind_param($stmt, "i", $idLivroDesejado);
    mysqli_stmt_execute($stmt);
    $resultDestinatario = mysqli_stmt_get_result($stmt);
    $rowDestinatario = mysqli_fetch_assoc($resultDestinatario);
    mysqli_stmt_close($stmt);

    if (!$rowDestinatario) {
      $erro = "Livro desejado inválido.";
    } else {
      $usuarioIdDestinatario = $rowDestinatario['usuario_id'];
      $dataHoje = date('Y-m-d');

      $sqlInserir = "
        INSERT INTO troca (
          troca_data, troca_status, usuario_id_proponente, usuario_id_destinatario,
          livro_livro_id_desejado, livro_livro_oferecido
        ) VALUES (?, 'pendente', ?, ?, ?, ?)
      ";
      $stmt = mysqli_prepare($conexao, $sqlInserir);
      mysqli_stmt_bind_param(
        $stmt,
        "siiii",
        $dataHoje,
        $idUsuario,
        $usuarioIdDestinatario,
        $idLivroDesejado,
        $idLivroOferecido
      );
      $ok = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);

      if ($ok) {
        $sucesso = "Proposta de troca enviada com sucesso!";
      } else {
        $erro = "Erro ao enviar proposta. Tente novamente.";
      }
    }
  }
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Propor Troca de Livro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded shadow p-6 max-w-lg w-full">
        <h1 class="text-2xl font-bold mb-6">Propor Troca de Livro</h1>

        <?php if ($erro): ?>
            <div class="bg-red-100 text-red-700 border border-red-400 rounded px-4 py-2 mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="bg-green-100 text-green-700 border border-green-400 rounded px-4 py-2 mb-4"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="livro_desejado" class="block font-semibold mb-1">Livro Desejado (de outro usuário)</label>
                <select id="livro_desejado" name="livro_desejado" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Escolha um livro --</option>
                    <?php foreach ($outrosLivros as $livro): ?>
                        <option value="<?= $livro['id_livro'] ?>">
                            <?= htmlspecialchars($livro['titulo']) ?> (Dono: <?= htmlspecialchars($livro['usuario_nome']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="livro_oferecido" class="block font-semibold mb-1">Seu Livro para Oferecer</label>
                <select id="livro_oferecido" name="livro_oferecido" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">-- Escolha um dos seus livros --</option>
                    <?php foreach ($meusLivros as $livro): ?>
                        <option value="<?= $livro['id_livro'] ?>">
                            <?= htmlspecialchars($livro['titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Propor Troca</button>
        </form>
    </div>
</body>
</html>
