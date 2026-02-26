<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/conexao.php';

$idUsuario   = (int)$_SESSION['usuario']['id'];
$nomeUsuario = $_SESSION['usuario']['nome'];

// Buscar livros do usuário
$stmt = mysqli_prepare($conexao,
    "SELECT livro_id, livro_titulo, livro_autor, livro_genero, disponivel, criado_em
     FROM livro WHERE usuario_id = ? ORDER BY criado_em DESC");
mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
mysqli_stmt_execute($stmt);
$meusLivros = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Contar trocas pendentes recebidas
$stmt = mysqli_prepare($conexao,
    "SELECT COUNT(*) as total FROM troca WHERE usuario_id_destinatario = ? AND troca_status = 'pendente'");
mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
mysqli_stmt_execute($stmt);
$rowPend = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$trocasPendentes = (int)$rowPend['total'];
mysqli_stmt_close($stmt);

// Contar trocas aceitas
$stmt = mysqli_prepare($conexao,
    "SELECT COUNT(*) as total FROM troca
     WHERE (usuario_id_proponente = ? OR usuario_id_destinatario = ?) AND troca_status = 'aceita'");
mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $idUsuario);
mysqli_stmt_execute($stmt);
$rowAceitas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$trocasAceitas = (int)$rowAceitas['total'];
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Painel — Escambo</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include __DIR__ . '/inc/navbar.php'; ?>
<main class="flex-1 max-w-5xl mx-auto w-full px-4 py-8">
  <h1 class="text-3xl font-bold text-gray-800 mb-2">Olá, <?=htmlspecialchars($nomeUsuario)?>! 👋</h1>
  <p class="text-gray-500 mb-6">Bem-vindo ao seu painel de controle.</p>

  <!-- Cards de resumo -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-indigo-600 text-white rounded-xl p-5 shadow">
      <p class="text-sm font-medium opacity-80">Meus Livros</p>
      <p class="text-4xl font-bold mt-1"><?=count($meusLivros)?></p>
    </div>
    <div class="bg-amber-500 text-white rounded-xl p-5 shadow">
      <p class="text-sm font-medium opacity-80">Trocas Pendentes</p>
      <p class="text-4xl font-bold mt-1"><?=$trocasPendentes?></p>
    </div>
    <div class="bg-green-600 text-white rounded-xl p-5 shadow">
      <p class="text-sm font-medium opacity-80">Trocas Realizadas</p>
      <p class="text-4xl font-bold mt-1"><?=$trocasAceitas?></p>
    </div>
  </div>

  <!-- Ações rápidas -->
  <div class="flex flex-wrap gap-3 mb-8">
    <a href="adicionar_livro.php" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm">
      ➕ Adicionar Livro
    </a>
    <a href="propor_troca.php" class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition font-semibold text-sm">
      🔄 Propor Troca
    </a>
    <a href="ver_trocas.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 transition font-semibold text-sm">
      📋 Ver Trocas
    </a>
    <a href="index.php" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 transition font-semibold text-sm">
      🔍 Explorar Livros
    </a>
  </div>

  <!-- Lista de livros -->
  <h2 class="text-xl font-bold text-gray-700 mb-4">📚 Meus Livros</h2>
  <?php if (empty($meusLivros)): ?>
    <div class="bg-white border border-dashed border-gray-300 rounded-xl p-10 text-center text-gray-400">
      <p class="text-lg mb-2">Você ainda não cadastrou nenhum livro.</p>
      <a href="adicionar_livro.php" class="text-indigo-600 font-semibold hover:underline">Adicionar meu primeiro livro →</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($meusLivros as $livro): ?>
        <div class="bg-white rounded-xl shadow p-4 border border-gray-100 hover:shadow-md transition">
          <p class="font-bold text-gray-800 truncate"><?=htmlspecialchars($livro['livro_titulo'])?></p>
          <p class="text-sm text-gray-500 mt-1">Autor: <?=htmlspecialchars($livro['livro_autor'])?></p>
          <p class="text-xs text-gray-400 mt-1">Gênero: <?=htmlspecialchars($livro['livro_genero'])?></p>
          <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full <?=$livro['disponivel']?'bg-green-100 text-green-700':'bg-gray-100 text-gray-500'?>">
            <?=$livro['disponivel']?'Disponível':'Em troca'?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<footer class="text-center text-xs text-gray-400 py-4">
  &copy; <?=date('Y')?> Escambo — Sistema de Troca de Livros
</footer>
</body>
</html>
