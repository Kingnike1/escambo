
# 📚 Escambo - Sistema de Troca de Livros

**Escambo** é um sistema web simples desenvolvido com PHP procedural, HTML, CSS e JavaScript básico, que permite a usuários cadastrarem livros e proporem trocas com outros usuários.

---

## 🚀 Funcionalidades

- Cadastro e login de usuários
- Cadastro de livros com fotos e descrição
- Visualização dos livros disponíveis
- Propostas de troca entre usuários
- Área do usuário com painel de controle
- Listagem das trocas feitas e recebidas

---

## 🛠️ Tecnologias Utilizadas

- **Frontend:** HTML, CSS, JavaScript básico
- **Backend:** PHP procedural
- **Banco de Dados:** MySQL (InnoDB)
- **Servidor local:** XAMPP, WAMP, Laragon, ou similar

---

## 🗂️ Estrutura de Pastas

```

/escambo
│
├── index.php                → Página inicial (lista de livros)
├── login.php                → Tela de login
├── cadastro.php             → Cadastro de usuário
├── painel.php               → Painel do usuário logado
├── adicionar-livro.php      → Cadastro de livros
├── propor-troca.php         → Criar proposta de troca
├── minhas-trocas.php        → Listar trocas do usuário
│
├── inc/
│   ├── conexao.php          → Conexão com o banco de dados
│   └── auth.php             → Verificação de login
│
├── assets/
│   ├── css/style.css        → Estilo visual da aplicação
│   └── js/script.js         → Scripts de interação

````

---

## 🧱 Estrutura do Banco de Dados

O projeto utiliza um banco de dados MySQL com as seguintes tabelas:

- `usuario` — informações dos usuários
- `livro` — livros cadastrados com título, autor, gênero, fotos e descrição
- `troca` — histórico de trocas entre usuários com status e data

> O script completo do banco está na pasta `/sql` ou pode ser importado do arquivo `escambo.sql`.

---

## ▶️ Como Rodar o Projeto

1. Clone este repositório:
```bash
   git clone https://github.com/seu-usuario/escambo.git
````

2. Coloque o projeto dentro da pasta do seu servidor local (ex: `htdocs/escambo` no XAMPP)

3. Crie o banco de dados no MySQL com o nome `escambo` e importe o arquivo SQL

4. Ajuste as configurações de conexão em `/inc/conexao.php`:

   ```php
   $host = "localhost";
   $db = "escambo";
   $user = "root";
   $pass = "123";
   ```

5. Acesse no navegador:

   ```
   http://localhost/escambo
   ```

---

## ✅ Próximas Etapas

* [ ] Upload real das fotos de livros
* [ ] Filtro de busca por título ou gênero
* [ ] Página pública para visualizar livros sem login
* [ ] Sistema de mensagens ou chat entre usuários
* [ ] Melhorias no layout com Tailwind ou Bootstrap (opcional)

---

## 👨‍💻 Desenvolvido por

**Pablo**
Contato: \[pablo.rodrigues1@estudante.ifgoiano.edu.br\]


