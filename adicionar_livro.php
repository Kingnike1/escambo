<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/conexao.php';

$idUsuario = (int)$_SESSION['usuario']['id'];
$erro      = '';
$sucesso   = '';
$titulo = $autor = $genero = $descricao = '';

$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) { mkdir($uploadsDir, 0755, true); }

$tiposPermitidos = ['image/jpeg','image/png','image/gif','image/webp'];
$tamanhoMax      = 5 * 1024 * 1024;

function salvarImagem(array $file, string $dir, array $tipos, int $max) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if (!in_array($file['type'], $tipos)) return false;
    if ($file['size'] > $max) return false;
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $nome = uniqid('livro_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $nome)) return false;
    return $nome;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo']    ?? '');
    $autor     = trim($_POST['autor']     ?? '');
    $genero    = trim($_POST['genero']    ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!$titulo || !$autor || !$genero) {
        $erro = 'Preencha os campos obrigatórios: título, autor e gênero.';
    } elseif (empty($_FILES['imagem1']['name']) || empty($_FILES['imagem2']['name'])) {
        $erro = 'Envie as duas imagens do livro.';
    } else {
        $imagem1 = salvarImagem($_FILES['imagem1'], $uploadsDir, $tiposPermitidos, $tamanhoMax);
        $imagem2 = salvarImagem($_FILES['imagem2'], $uploadsDir, $tiposPermitidos, $tamanhoMax);
        if (!$imagem1 || !$imagem2) {
            $erro = 'Erro ao enviar imagens. Use JPEG, PNG, GIF ou WebP (máx. 5 MB cada).';
            if ($imagem1) @unlink($uploadsDir . $imagem1);
        } else {
            $sql  = "INSERT INTO livro (livro_titulo,livro_autor,livro_genero,livro_descricao,foto1_livro,foto2_livro,usuario_id) VALUES (?,?,?,?,?,?,?)";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt,'ssssssi',$titulo,$autor,$genero,$descricao,$imagem1,$imagem2,$idUsuario);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($ok) {
                $sucesso = 'Livro adicionado com sucesso!';
                $titulo = $autor = $genero = $descricao = '';
            } else {
                $erro = 'Erro ao salvar no banco de dados.';
                @unlink($uploadsDir . $imagem1); @unlink($uploadsDir . $imagem2);
            }
        }
    }
}
$generos = ['Ficção Científica','Fantasia','Romance','Terror','Suspense','Aventura','Biografia','História','Autoajuda','Filosofia','Poesia','Infantil','Outro'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Adicionar Livro — Escambo</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include __DIR__ . '/inc/navbar.php'; ?>
<main class="flex-1 flex items-start justify-center p-6">
  <div class="bg-white max-w-lg w-full rounded-2xl shadow-lg p-8 mt-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Adicionar Livro</h1>
    <?php if($erro):?><div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm"><?=htmlspecialchars($erro)?></div><?php endif;?>
    <?php if($sucesso):?><div class="bg-green-50 border border-green-300 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm"><?=htmlspecialchars($sucesso)?></div><?php endif;?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="titulo">Título *</label>
        <input id="titulo" name="titulo" type="text" required value="<?=htmlspecialchars($titulo)?>"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="autor">Autor *</label>
        <input id="autor" name="autor" type="text" required value="<?=htmlspecialchars($autor)?>"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="genero">Gênero *</label>
        <select id="genero" name="genero" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">-- Selecione --</option>
          <?php foreach($generos as $g):?>
          <option value="<?=htmlspecialchars($g)?>" <?=$genero===$g?'selected':''?>><?=htmlspecialchars($g)?></option>
          <?php endforeach;?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" rows="3"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?=htmlspecialchars($descricao)?></textarea>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="imagem1">Imagem 1 * <span class="text-gray-400 font-normal">(JPEG/PNG/GIF/WebP, máx. 5 MB)</span></label>
        <input id="imagem1" name="imagem1" type="file" accept="image/*" required class="w-full text-sm"/>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="imagem2">Imagem 2 * <span class="text-gray-400 font-normal">(JPEG/PNG/GIF/WebP, máx. 5 MB)</span></label>
        <input id="imagem2" name="imagem2" type="file" accept="image/*" required class="w-full text-sm"/>
      </div>
      <div class="flex justify-between items-center pt-2">
        <a href="painel.php" class="text-gray-500 hover:underline text-sm">Voltar ao Painel</a>
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm">Salvar Livro</button>
      </div>
    </form>
  </div>
</main>
<footer class="text-center text-xs text-gray-400 py-4">&copy; <?=date('Y')?> Escambo</footer>
</body>
</html>
