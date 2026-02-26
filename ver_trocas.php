<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/inc/conexao.php';
$idUsuario = (int)$_SESSION['usuario']['id'];
$sucesso = ''; $erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'], $_POST['troca_id'])) {
    $acao = $_POST['acao']; $trocaId = intval($_POST['troca_id']);
    $statusNovo = ($acao === 'aceitar') ? 'aceita' : 'recusada';
    $stmt = mysqli_prepare($conexao, "UPDATE troca SET troca_status = ? WHERE troca_id = ? AND usuario_id_destinatario = ? AND troca_status = 'pendente'");
    mysqli_stmt_bind_param($stmt,'sii',$statusNovo,$trocaId,$idUsuario);
    mysqli_stmt_execute($stmt); $afetadas = mysqli_stmt_affected_rows($stmt); mysqli_stmt_close($stmt);
    if ($afetadas > 0) { $sucesso = $acao === 'aceitar' ? 'Troca aceita!' : 'Troca recusada.'; }
    else { $erro = 'Nao foi possivel atualizar a troca.'; }
}
$stmt = mysqli_prepare($conexao,
    "SELECT t.troca_id, t.troca_status, t.troca_data, t.mensagem, ld.livro_titulo AS titulo_desejado, lo.livro_titulo AS titulo_oferecido, u.usuario_nome AS proponente_nome
     FROM troca t JOIN livro ld ON t.livro_livro_id_desejado=ld.livro_id JOIN livro lo ON t.livro_livro_oferecido=lo.livro_id JOIN usuario u ON t.usuario_id_proponente=u.id_usuario
     WHERE t.usuario_id_destinatario = ? ORDER BY t.troca_data DESC");
mysqli_stmt_bind_param($stmt,'i',$idUsuario); mysqli_stmt_execute($stmt);
$trocasRecebidas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);
$stmt = mysqli_prepare($conexao,
    "SELECT t.troca_id, t.troca_status, t.troca_data, t.mensagem, ld.livro_titulo AS titulo_desejado, lo.livro_titulo AS titulo_oferecido, u.usuario_nome AS destinatario_nome
     FROM troca t JOIN livro ld ON t.livro_livro_id_desejado=ld.livro_id JOIN livro lo ON t.livro_livro_oferecido=lo.livro_id JOIN usuario u ON t.usuario_id_destinatario=u.id_usuario
     WHERE t.usuario_id_proponente = ? ORDER BY t.troca_data DESC");
mysqli_stmt_bind_param($stmt,'i',$idUsuario); mysqli_stmt_execute($stmt);
$trocasEnviadas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); mysqli_stmt_close($stmt);
function badgeStatus(string $s): string {
    $m=['pendente'=>'bg-amber-100 text-amber-700','aceita'=>'bg-green-100 text-green-700','recusada'=>'bg-red-100 text-red-700','cancelada'=>'bg-gray-100 text-gray-500'];
    $c=$m[$s]??'bg-gray-100 text-gray-500';
    return "<span class=\"inline-block text-xs px-2 py-0.5 rounded-full font-medium $c\">".ucfirst($s)."</span>";
}
?>
<!DOCTYPE html><html lang="pt-BR">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Minhas Trocas - Escambo</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include __DIR__ . '/inc/navbar.php'; ?>
<main class="flex-1 max-w-5xl mx-auto w-full px-4 py-8">
<h1 class="text-3xl font-bold text-gray-800 mb-6">Minhas Trocas</h1>
<?php if($sucesso):?><div class="bg-green-50 border border-green-300 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm"><?=htmlspecialchars($sucesso)?></div><?php endif;?>
<?php if($erro):?><div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm"><?=htmlspecialchars($erro)?></div><?php endif;?>
<h2 class="text-xl font-semibold text-gray-700 mb-3">Propostas Recebidas</h2>
<?php if(empty($trocasRecebidas)):?><p class="text-gray-400 text-sm mb-8">Nenhuma proposta recebida ainda.</p>
<?php else:?><div class="space-y-3 mb-8">
<?php foreach($trocasRecebidas as $t):?>
<div class="bg-white rounded-xl shadow p-4 border border-gray-100">
<div class="flex flex-wrap items-start justify-between gap-2">
<div>
<p class="font-semibold text-gray-800"><span class="text-indigo-600"><?=htmlspecialchars($t['proponente_nome'])?></span> quer <strong><?=htmlspecialchars($t['titulo_desejado'])?></strong> em troca de <strong><?=htmlspecialchars($t['titulo_oferecido'])?></strong></p>
<?php if($t['mensagem']):?><p class="text-sm text-gray-500 mt-1 italic">"<?=htmlspecialchars($t['mensagem'])?>"</p><?php endif;?>
<p class="text-xs text-gray-400 mt-1"><?=date('d/m/Y H:i', strtotime($t['troca_data']))?></p>
</div>
<div class="flex items-center gap-2">
<?=badgeStatus($t['troca_status'])?>
<?php if($t['troca_status']==='pendente'):?>
<form method="POST" class="inline">
<input type="hidden" name="troca_id" value="<?=(int)$t['troca_id']?>"/>
<button name="acao" value="aceitar" class="bg-green-600 text-white text-xs px-3 py-1 rounded-lg hover:bg-green-700 transition">Aceitar</button>
<button name="acao" value="recusar" class="bg-red-500 text-white text-xs px-3 py-1 rounded-lg hover:bg-red-600 transition ml-1">Recusar</button>
</form>
<?php endif;?>
</div>
</div>
</div>
<?php endforeach;?></div>
<?php endif;?>
<h2 class="text-xl font-semibold text-gray-700 mb-3">Propostas Enviadas</h2>
<?php if(empty($trocasEnviadas)):?><p class="text-gray-400 text-sm">Nenhuma proposta enviada. <a href="propor_troca.php" class="text-indigo-600 font-semibold hover:underline">Propor uma troca</a>.</p>
<?php else:?><div class="space-y-3">
<?php foreach($trocasEnviadas as $t):?>
<div class="bg-white rounded-xl shadow p-4 border border-gray-100">
<div class="flex flex-wrap items-start justify-between gap-2">
<div>
<p class="font-semibold text-gray-800">Voce quer <strong><?=htmlspecialchars($t['titulo_desejado'])?></strong> de <span class="text-indigo-600"><?=htmlspecialchars($t['destinatario_nome'])?></span> oferecendo <strong><?=htmlspecialchars($t['titulo_oferecido'])?></strong></p>
<?php if($t['mensagem']):?><p class="text-sm text-gray-500 mt-1 italic">"<?=htmlspecialchars($t['mensagem'])?>"</p><?php endif;?>
<p class="text-xs text-gray-400 mt-1"><?=date('d/m/Y H:i', strtotime($t['troca_data']))?></p>
</div>
<?=badgeStatus($t['troca_status'])?>
</div>
</div>
<?php endforeach;?></div>
<?php endif;?>
</main>
<footer class="text-center text-xs text-gray-400 py-4">&copy; <?=date('Y')?> Escambo</footer>
</body></html>
