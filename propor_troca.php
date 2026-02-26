<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/conexao.php';
$idUsuario = (int)$_SESSION['usuario']['id'];
$erro = ''; $sucesso = '';
$stmt = mysqli_prepare($conexao, "SELECT livro_id AS id_livro, livro_titulo AS titulo FROM livro WHERE usuario_id = ? AND disponivel = 1 ORDER BY livro_titulo");
mysqli_stmt_bind_param($stmt,'i',$idUsuario); mysqli_stmt_execute($stmt);
$meusLivros = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);
$stmt = mysqli_prepare($conexao, "SELECT l.livro_id AS id_livro, l.livro_titulo AS titulo, u.usuario_nome FROM livro l JOIN usuario u ON l.usuario_id = u.id_usuario WHERE l.usuario_id != ? AND l.disponivel = 1 ORDER BY l.livro_titulo");
mysqli_stmt_bind_param($stmt,'i',$idUsuario); mysqli_stmt_execute($stmt);
$outrosLivros = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idDesejado = intval($_POST['livro_desejado'] ?? 0);
    $idOferecido = intval($_POST['livro_oferecido'] ?? 0);
    $mensagem = trim($_POST['mensagem'] ?? '');
    if (!$idDesejado || !$idOferecido) { $erro = 'Escolha ambos os livros.'; }
    else {
        $stmt = mysqli_prepare($conexao, "SELECT usuario_id FROM livro WHERE livro_id = ? AND disponivel = 1");
        mysqli_stmt_bind_param($stmt,'i',$idDesejado); mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
        if (!$row) { $erro = 'Livro nao encontrado ou indisponivel.'; }
        else {
            $idDest = (int)$row['usuario_id'];
            $sql = "INSERT INTO troca (troca_status,usuario_id_proponente,usuario_id_destinatario,livro_livro_id_desejado,livro_livro_oferecido,mensagem) VALUES ('pendente',?,?,?,?,?)";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt,'iiiss',$idUsuario,$idDest,$idDesejado,$idOferecido,$mensagem);
            $ok = mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
            if ($ok) { $sucesso = 'Proposta enviada com sucesso!'; }
            else { $erro = 'Erro ao enviar proposta.'; }
        }
    }
}
?>
<!DOCTYPE html><html lang="pt-BR">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Propor Troca - Escambo</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include __DIR__ . '/inc/navbar.php'; ?>
<main class="flex-1 flex items-start justify-center p-6">
<div class="bg-white max-w-lg w-full rounded-2xl shadow-lg p-8 mt-4">
<h1 class="text-2xl font-bold text-gray-800 mb-6">Propor Troca de Livro</h1>
<?php if($erro):?><div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm"><?=htmlspecialchars($erro)?></div><?php endif;?>
<?php if($sucesso):?><div class="bg-green-50 border border-green-300 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm"><?=htmlspecialchars($sucesso)?></div><?php endif;?>
<?php if(empty($meusLivros)):?>
<div class="bg-amber-50 border border-amber-300 text-amber-700 px-4 py-3 rounded-lg text-sm">
Voce nao possui livros disponiveis. <a href="adicionar_livro.php" class="font-semibold underline">Adicionar um livro</a>.
</div>
<?php elseif(empty($outrosLivros)):?>
<div class="bg-blue-50 border border-blue-300 text-blue-700 px-4 py-3 rounded-lg text-sm">
Nao ha livros de outros usuarios disponiveis no momento.
</div>
<?php else:?>
<form method="POST" class="space-y-4">
<div><label for="livro_desejado" class="block text-sm font-semibold text-gray-700 mb-1">Livro que voce deseja</label>
<select id="livro_desejado" name="livro_desejado" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
<option value="">-- Escolha um livro --</option>
<?php foreach($outrosLivros as $l):?>
<option value="<?=(int)$l['id_livro']?>"><?=htmlspecialchars($l['titulo'])?> (de <?=htmlspecialchars($l['usuario_nome'])?>)</option>
<?php endforeach;?>
</select></div>
<div><label for="livro_oferecido" class="block text-sm font-semibold text-gray-700 mb-1">Seu livro para oferecer</label>
<select id="livro_oferecido" name="livro_oferecido" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
<option value="">-- Escolha um dos seus livros --</option>
<?php foreach($meusLivros as $l):?>
<option value="<?=(int)$l['id_livro']?>"><?=htmlspecialchars($l['titulo'])?></option>
<?php endforeach;?>
</select></div>
<div><label for="mensagem" class="block text-sm font-semibold text-gray-700 mb-1">Mensagem <span class="text-gray-400 font-normal">(opcional)</span></label>
<textarea id="mensagem" name="mensagem" rows="3" placeholder="Deixe uma mensagem..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea></div>
<div class="flex justify-between items-center pt-2">
<a href="painel.php" class="text-gray-500 hover:underline text-sm">Voltar ao Painel</a>
<button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold text-sm">Enviar Proposta</button>
</div>
</form>
<?php endif;?>
</div></main>
<footer class="text-center text-xs text-gray-400 py-4">&copy; <?=date('Y')?> Escambo</footer>
</body></html>
