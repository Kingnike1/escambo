<?php
session_start();
define('BASE_URL', '/');
if (isset($_SESSION['usuario'])) { header('Location: painel.php'); exit; }
require_once __DIR__ . '/inc/conexao.php';
$erroLogin = ''; $erroCadastro = ''; $sucessoCadastro = ''; $abaAtiva = 'loginTab';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'login') {
        $email = trim($_POST['usuario'] ?? ''); $senha = $_POST['senha'] ?? '';
        if (!$email || !$senha) { $erroLogin = 'Preencha e-mail e senha.'; }
        else {
            $stmt = mysqli_prepare($conexao, "SELECT id_usuario,usuario_nome,usuario_email,usuario_senha FROM usuario WHERE usuario_email=?");
            mysqli_stmt_bind_param($stmt,'s',$email); mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
            if ($user && password_verify($senha,$user['usuario_senha'])) {
                $_SESSION['usuario']=['id'=>$user['id_usuario'],'nome'=>$user['usuario_nome'],'email'=>$user['usuario_email']];
                header('Location: painel.php'); exit;
            } else { $erroLogin = 'E-mail ou senha incorretos.'; }
        }
    }
    if ($acao === 'cadastro') {
        $abaAtiva='cadastroTab';
        $nome=trim($_POST['nome']??''); $email=trim($_POST['email']??''); $endereco=trim($_POST['endereco']??'');
        $senha=$_POST['senha']??''; $senha2=$_POST['senha2']??'';
        if (!$nome||!$email||!$endereco||!$senha||!$senha2) { $erroCadastro='Preencha todos os campos.'; }
        elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $erroCadastro='E-mail inválido.'; }
        elseif (strlen($senha)<6) { $erroCadastro='Senha deve ter ao menos 6 caracteres.'; }
        elseif ($senha!==$senha2) { $erroCadastro='As senhas não coincidem.'; }
        else {
            $stmt=mysqli_prepare($conexao,"SELECT id_usuario FROM usuario WHERE usuario_email=?");
            mysqli_stmt_bind_param($stmt,'s',$email); mysqli_stmt_execute($stmt); mysqli_stmt_store_result($stmt);
            $existe=mysqli_stmt_num_rows($stmt)>0; mysqli_stmt_close($stmt);
            if ($existe) { $erroCadastro='Este e-mail já está cadastrado.'; }
            else {
                $hash=password_hash($senha,PASSWORD_BCRYPT);
                $stmt=mysqli_prepare($conexao,"INSERT INTO usuario(usuario_nome,usuario_email,usuario_senha,usuario_endereco) VALUES(?,?,?,?)");
                mysqli_stmt_bind_param($stmt,'ssss',$nome,$email,$hash,$endereco);
                $ok=mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
                if ($ok) { $sucessoCadastro='Cadastro realizado! Faça login.'; $abaAtiva='loginTab'; }
                else { $erroCadastro='Erro ao cadastrar. Tente novamente.'; }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Entrar — Escambo</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
function mostrarTab(id){
  ['loginTab','cadastroTab'].forEach(t=>document.getElementById(t).classList.add('hidden'));
  document.getElementById(id).classList.remove('hidden');
  ['loginTabBtn','cadastroTabBtn'].forEach(b=>{
    document.getElementById(b).classList.remove('border-b-2','border-indigo-600','font-bold','text-indigo-700');
    document.getElementById(b).classList.add('text-gray-400');
  });
  const btn=id==='loginTab'?'loginTabBtn':'cadastroTabBtn';
  document.getElementById(btn).classList.remove('text-gray-400');
  document.getElementById(btn).classList.add('border-b-2','border-indigo-600','font-bold','text-indigo-700');
}
window.onload=()=>mostrarTab('<?= $abaAtiva ?>');
</script>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-indigo-100 min-h-screen flex flex-col">
<header class="bg-indigo-700 text-white py-4 text-center shadow">
  <a href="index.php" class="text-2xl font-bold hover:text-indigo-200">📚 Escambo</a>
  <p class="text-indigo-200 text-sm mt-1">Sistema de Troca de Livros</p>
</header>
<main class="flex-1 flex items-center justify-center p-6">
  <div class="bg-white max-w-md w-full rounded-2xl shadow-lg p-8">
    <div class="flex justify-center mb-6 space-x-10 text-base cursor-pointer select-none border-b pb-3">
      <div id="loginTabBtn" onclick="mostrarTab('loginTab')" class="pb-1 transition">Entrar</div>
      <div id="cadastroTabBtn" onclick="mostrarTab('cadastroTab')" class="pb-1 transition">Cadastrar</div>
    </div>
    <div id="loginTab">
      <?php if($erroLogin):?><div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm text-center"><?=htmlspecialchars($erroLogin)?></div><?php endif;?>
      <?php if($sucessoCadastro):?><div class="bg-green-50 border border-green-300 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm text-center"><?=htmlspecialchars($sucessoCadastro)?></div><?php endif;?>
      <form method="POST" class="space-y-4" autocomplete="off">
        <input type="hidden" name="acao" value="login"/>
        <div><label for="usuario" class="block text-sm font-semibold text-gray-700 mb-1">E-mail</label>
        <input id="usuario" name="usuario" type="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?=isset($_POST['usuario'])&&($_POST['acao']??'')==='login'?htmlspecialchars($_POST['usuario']):''?>"/></div>
        <div><label for="senha" class="block text-sm font-semibold text-gray-700 mb-1">Senha</label>
        <input id="senha" name="senha" type="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/></div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm">Entrar</button>
      </form>
    </div>
    <div id="cadastroTab" class="hidden">
      <?php if($erroCadastro):?><div class="bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm text-center"><?=htmlspecialchars($erroCadastro)?></div><?php endif;?>
      <form method="POST" class="space-y-4" autocomplete="off">
        <input type="hidden" name="acao" value="cadastro"/>
        <div><label for="nome" class="block text-sm font-semibold text-gray-700 mb-1">Nome completo</label>
        <input id="nome" name="nome" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?=isset($_POST['nome'])?htmlspecialchars($_POST['nome']):''?>"/></div>
        <div><label for="email" class="block text-sm font-semibold text-gray-700 mb-1">E-mail</label>
        <input id="email" name="email" type="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?=isset($_POST['email'])?htmlspecialchars($_POST['email']):''?>"/></div>
        <div><label for="endereco" class="block text-sm font-semibold text-gray-700 mb-1">Endereço</label>
        <input id="endereco" name="endereco" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?=isset($_POST['endereco'])?htmlspecialchars($_POST['endereco']):''?>"/></div>
        <div><label for="senha_cad" class="block text-sm font-semibold text-gray-700 mb-1">Senha <span class="text-gray-400 font-normal">(mín. 6 caracteres)</span></label>
        <input id="senha_cad" name="senha" type="password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/></div>
        <div><label for="senha2" class="block text-sm font-semibold text-gray-700 mb-1">Confirmar Senha</label>
        <input id="senha2" name="senha2" type="password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/></div>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-semibold text-sm">Criar Conta</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
