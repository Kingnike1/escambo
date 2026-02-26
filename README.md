# Escambo - Sistema de Troca de Livros

Sistema web em PHP para troca de livros entre usuarios.

## Funcionalidades

- Login/cadastro com bcrypt
- Painel com dados reais do banco
- Cadastro de livros com upload de imagens
- Pagina publica com busca e filtro por genero
- Propostas de troca com mensagem
- Aceitar/recusar propostas
- Historico de trocas

## Tecnologias

- PHP 8.2 + MySQLi (prepared statements)
- MariaDB 10.11
- Tailwind CSS
- Docker + Docker Compose

## Como Rodar com Docker

Requisitos: Docker e Docker Compose instalados.



## Como Rodar Localmente

1. Coloque em htdocs/escambo (XAMPP) ou equivalente
2. Crie o banco escambo e importe codigo-banco.sql
3. Acesse: http://localhost/escambo

## Seguranca

- Senhas com bcrypt
- Prepared statements em todas as queries
- htmlspecialchars em todas as saidas
- Validacao de tipo e tamanho no upload de imagens
- Sessao com httponly e strict mode

## Estrutura



## Desenvolvido por

Pablo Rodrigues - pablo.rodrigues1@estudante.ifgoiano.edu.br
