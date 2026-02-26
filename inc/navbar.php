<?php
/**
 * Barra de navegação compartilhada entre todas as páginas.
 * Deve ser incluída após session_start().
 */
$logado = isset($_SESSION['usuario']);
$nomeUsuario = $logado ? htmlspecialchars($_SESSION['usuario']['nome']) : '';
?>
<nav class="bg-indigo-700 text-white shadow-md">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="index.php" class="text-xl font-bold tracking-wide hover:text-indigo-200 transition">
            📚 Escambo
        </a>
        <div class="flex items-center gap-4 text-sm font-medium">
            <a href="index.php" class="hover:text-indigo-200 transition">Livros</a>
            <?php if ($logado): ?>
                <a href="painel.php" class="hover:text-indigo-200 transition">Painel</a>
                <a href="adicionar_livro.php" class="hover:text-indigo-200 transition">Adicionar</a>
                <a href="propor_troca.php" class="hover:text-indigo-200 transition">Propor Troca</a>
                <a href="ver_trocas.php" class="hover:text-indigo-200 transition">Minhas Trocas</a>
                <span class="text-indigo-200">|</span>
                <span class="text-indigo-100">Olá, <?= $nomeUsuario ?></span>
                <a href="logout.php"
                   class="bg-white text-indigo-700 px-3 py-1 rounded hover:bg-indigo-100 transition font-semibold">
                    Sair
                </a>
            <?php else: ?>
                <a href="login.php"
                   class="bg-white text-indigo-700 px-3 py-1 rounded hover:bg-indigo-100 transition font-semibold">
                    Entrar / Cadastrar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
