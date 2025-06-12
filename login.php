<?php
session_start();

require 'conexao.php';

if (isset($_SESSION['usuario'])) {
    header('Location: painel.php');
    exit;
}

$erroLogin = '';
$erroCadastro = '';
$sucessoCadastro = '';
$abaAtiva = 'loginTab'; // para controlar qual aba mostrar depois do POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'login') {
        // LOGIN
        $usuario = trim($_POST['usuario'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if (!$usuario || !$senha) {
            $erroLogin = 'Preencha usuário e senha.';
        } else {
            // Login pelo email
            $sql = "SELECT * FROM usuario WHERE usuario_email = ? LIMIT 1";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "s", $usuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($senha, $user['usuario_senha'])) {
                $_SESSION['usuario'] = [
                    'id' => $user['id_usuario'],
                    'nome' => $user['usuario_nome'],
                    'email' => $user['usuario_email'],
                ];
                header('Location: painel.php');
                exit;
            } else {
                $erroLogin = 'Usuário ou senha inválidos.';
            }
        }
        $abaAtiva = 'loginTab';
    } elseif (isset($_POST['acao']) && $_POST['acao'] === 'cadastro') {
        // CADASTRO
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $senha2 = trim($_POST['senha2'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');

        if (!$nome || !$email || !$senha || !$senha2 || !$endereco) {
            $erroCadastro = 'Preencha todos os campos.';
        } elseif ($senha !== $senha2) {
            $erroCadastro = 'As senhas não conferem.';
        } else {
            // Verificar se email já existe
            $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario_email = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);

            if ($row['total'] > 0) {
                $erroCadastro = 'Usuário já existe.';
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuario (usuario_nome, usuario_email, usuario_senha, usuario_endereco) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conexao, $sql);
                mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $senhaHash, $endereco);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($ok) {
                    $sucessoCadastro = 'Cadastro realizado com sucesso! Agora faça login.';
                } else {
                    $erroCadastro = 'Erro ao cadastrar usuário.';
                }
            }
        }
        $abaAtiva = 'cadastroTab';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Login / Cadastro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function mostrarTab(tab) {
            document.getElementById('loginTab').classList.add('hidden');
            document.getElementById('cadastroTab').classList.add('hidden');
            document.getElementById(tab).classList.remove('hidden');

            document.getElementById('loginTabBtn').classList.remove('border-b-2', 'border-blue-600', 'font-bold');
            document.getElementById('cadastroTabBtn').classList.remove('border-b-2', 'border-blue-600', 'font-bold');
            document.getElementById(tab + 'Btn').classList.add('border-b-2', 'border-blue-600', 'font-bold');
        }
        window.onload = function () {
            // Mostrar aba correta após submit
            mostrarTab('<?= $abaAtiva ?>');
        };
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white max-w-md w-full rounded-lg shadow p-6">
        <div class="flex justify-center mb-6 space-x-10 text-lg cursor-pointer select-none">
            <div id="loginTabBtn" onclick="mostrarTab('loginTab')" class="border-b-2 border-blue-600 font-bold pb-1">Entrar</div>
            <div id="cadastroTabBtn" onclick="mostrarTab('cadastroTab')" class="text-gray-500 hover:text-gray-700 pb-1">Cadastrar</div>
        </div>

        <!-- FORM LOGIN -->
        <div id="loginTab">
            <?php if ($erroLogin): ?>
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($erroLogin) ?></div>
            <?php endif; ?>
            <form method="POST" class="space-y-4" autocomplete="off">
                <input type="hidden" name="acao" value="login" />
                <div>
                    <label for="usuario" class="block font-semibold mb-1">E-mail</label>
                    <input id="usuario" name="usuario" type="email" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : '' ?>" />
                </div>
                <div>
                    <label for="senha" class="block font-semibold mb-1">Senha</label>
                    <input id="senha" name="senha" type="password" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Entrar</button>
            </form>
        </div>

        <!-- FORM CADASTRO -->
        <div id="cadastroTab" class="hidden">
            <?php if ($erroCadastro): ?>
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($erroCadastro) ?></div>
            <?php endif; ?>
            <?php if ($sucessoCadastro): ?>
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($sucessoCadastro) ?></div>
            <?php endif; ?>
            <form method="POST" class="space-y-4" autocomplete="off">
                <input type="hidden" name="acao" value="cadastro" />
                <div>
                    <label for="nome" class="block font-semibold mb-1">Nome completo</label>
                    <input id="nome" name="nome" type="text" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" />
                </div>
                <div>
                    <label for="email" class="block font-semibold mb-1">E-mail</label>
                    <input id="email" name="email" type="email" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" />
                </div>
                <div>
                    <label for="endereco" class="block font-semibold mb-1">Endereço</label>
                    <input id="endereco" name="endereco" type="text" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="<?= isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : '' ?>" />
                </div>
                <div>
                    <label for="senha" class="block font-semibold mb-1">Senha</label>
                    <input id="senha" name="senha" type="password" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="senha2" class="block font-semibold mb-1">Confirmar Senha</label>
                    <input id="senha2" name="senha2" type="password" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <button type="submit"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">Cadastrar</button>
            </form>
        </div>
    </div>
</body>

</html>
