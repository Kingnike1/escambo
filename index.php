<?php
session_start();
require_once __DIR__ . '/inc/conexao.php';

$busca  = trim($_GET['busca']  ?? '');
$genero = trim($_GET['genero'] ?? '');

// Buscar generos distintos para o filtro
$stmt = mysqli_prepare($conexao, "SELECT DISTINCT livro_genero FROM livro ORDER BY livro_genero");
mysqli_stmt_execute($stmt);
$generos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Montar query dinamica
$sql = "SELECT l.livro_id, l.livro_titulo, l.livro_autor, l.livro_genero, l.livro_descricao, l.foto1_livro, u.usuario_nome
        FROM livro l JOIN usuario u ON l.usuario_id = u.id_usuario
        WHERE l.disponivel = 1";
$params = [];
$types  = '';

if ($busca !== '') {
    $sql .= " AND (l.livro_titulo LIKE ? OR l.livro_autor LIKE ?)";
    $like = '%' . $busca . '%';
    $params[] = $like; $params[] = $like;
    $types .= 'ss';
}
if ($genero !== '') {
    $sql .= " AND l.livro_genero = ?";
    $params[] = $genero;
    $types .= 's';
}
$sql .= " ORDER BY l.criado_em DESC LIMIT 60";

$stmt = mysqli_prepare($conexao, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$livros = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Escambo - Troca de Livros</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include __DIR__ . '/inc/navbar.php'; ?>

<!-- Hero -->
<section class="bg-indigo-700 text-white py-12 px-4 text-center">
  <h1 class="text-4xl font-extrabold mb-2">Troque Livros com Outros Leitores</h1>
  <p class="text-indigo-200 text-lg mb-6">Encontre o livro que voce quer e ofereça um seu em troca.</p>
  <?php if(!isset($_SESSION['usuario'])):?>
  <a href="login.php" class="bg-white text-indigo-700 font-bold px-6 py-3 rounded-xl hover:bg-indigo-50 transition text-sm">
    Comece Agora — e Gratis!
  </a>
  <?php endif;?>
</section>

<!-- Filtros -->
<section class="max-w-5xl mx-auto w-full px-4 py-6">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Buscar</label>
      <input type="text" name="busca" value="<?=htmlspecialchars($busca)?>" placeholder="Titulo ou autor..."
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
    </div>
    <div class="min-w-40">
      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Genero</label>
      <select name="genero" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">Todos</option>
        <?php foreach($generos as $g):?>
        <option value="<?=htmlspecialchars($g['livro_genero'])?>" <?=$genero===$g['livro_genero']?'selected':''?>>
          <?=htmlspecialchars($g['livro_genero'])?>
        </option>
        <?php endforeach;?>
      </select>
    </div>
    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm">
      Filtrar
    </button>
    <?php if($busca||$genero):?>
    <a href="index.php" class="text-gray-500 hover:underline text-sm py-2">Limpar</a>
    <?php endif;?>
  </form>
</section>

<!-- Livros -->
<main class="flex-1 max-w-5xl mx-auto w-full px-4 pb-10">
  <?php if(empty($livros)):?>
  <div class="text-center py-20 text-gray-400">
    <p class="text-2xl mb-2">Nenhum livro encontrado.</p>
    <?php if(isset($_SESSION['usuario'])):?>
    <a href="adicionar_livro.php" class="text-indigo-600 font-semibold hover:underline">Seja o primeiro a cadastrar um livro!</a>
    <?php else:?>
    <a href="login.php" class="text-indigo-600 font-semibold hover:underline">Cadastre-se e adicione seus livros!</a>
    <?php endif;?>
  </div>
  <?php else:?>
  <p class="text-sm text-gray-400 mb-4"><?=count($livros)?> livro(s) encontrado(s)</p>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach($livros as $l):?>
    <div class="bg-white rounded-xl shadow hover:shadow-md transition border border-gray-100 overflow-hidden flex flex-col">
      <?php if($l['foto1_livro'] && file_exists(__DIR__.'/uploads/'.$l['foto1_livro'])):?>
      <img src="uploads/<?=htmlspecialchars($l['foto1_livro'])?>" alt="Capa" class="w-full h-40 object-cover"/>
      <?php else:?>
      <div class="w-full h-40 bg-indigo-50 flex items-center justify-center text-5xl">📖</div>
      <?php endif;?>
      <div class="p-4 flex flex-col flex-1">
        <p class="font-bold text-gray-800 truncate"><?=htmlspecialchars($l['livro_titulo'])?></p>
        <p class="text-sm text-gray-500">Autor: <?=htmlspecialchars($l['livro_autor'])?></p>
        <span class="inline-block mt-1 text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full w-fit"><?=htmlspecialchars($l['livro_genero'])?></span>
        <?php if($l['livro_descricao']):?>
        <p class="text-xs text-gray-400 mt-2 line-clamp-2"><?=htmlspecialchars($l['livro_descricao'])?></p>
        <?php endif;?>
        <p class="text-xs text-gray-400 mt-auto pt-3">Dono: <?=htmlspecialchars($l['usuario_nome'])?></p>
        <?php if(isset($_SESSION['usuario'])):?>
        <a href="propor_troca.php" class="mt-2 block text-center bg-green-600 text-white text-sm py-1.5 rounded-lg hover:bg-green-700 transition font-semibold">
          Propor Troca
        </a>
        <?php else:?>
        <a href="login.php" class="mt-2 block text-center bg-indigo-600 text-white text-sm py-1.5 rounded-lg hover:bg-indigo-700 transition font-semibold">
          Entrar para Trocar
        </a>
        <?php endif;?>
      </div>
    </div>
    <?php endforeach;?>
  </div>
  <?php endif;?>
</main>
<footer class="text-center text-xs text-gray-400 py-4">&copy; <?=date('Y')?> Escambo — Sistema de Troca de Livros</footer>
</body>
</html>
