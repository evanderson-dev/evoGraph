# evoGraph - Sistema de Gerenciamento Escolar

O **evoGraph** é um sistema web desenvolvido em PHP para gerenciamento escolar, permitindo que professores, coordenadores e diretores visualizem e administrem turmas, alunos e funcionários de forma intuitiva. O projeto utiliza MySQL como banco de dados e oferece uma interface responsiva com dashboard dinâmico e funcionalidades específicas por cargo.

## Funcionalidades

- **Autenticação**: Login seguro com sessão baseada em cargos (Professor, Coordenador, Diretor).
- **Dashboard**:
  - **Professor**: Visualiza suas turmas com contagem de alunos e lista de alunos por turma.
  - **Coordenador/Diretor**: Visualiza todas as turmas com informações de professores e alunos, além de opções de cadastro.
- **Sidebar Retrátil**: Menu hambúrguer com links para Home, Relatórios, Configurações, Cadastros (condicional) e Logout.
- **Alteração de Senha**: Usuários podem redefinir suas senhas via requisição AJAX.
- **Responsividade**: Interface adaptada para diferentes tamanhos de tela.

## Tecnologias Utilizadas

- **Backend**: PHP 8.x com PDO para conexão ao banco.
- **Frontend**: HTML5, CSS3, JavaScript (jQuery para AJAX).
- **Banco de Dados**: MySQL.
- **Estilização**: Font Awesome para ícones, CSS personalizado.
- **Versionamento**: Git.

## Requisitos

- **Servidor Web**: Apache (ex.: via XAMPP ou servidor Linux).
- **PHP**: Versão 7.4 ou superior com extensão `pdo_mysql` habilitada.
- **MySQL**: Versão 5.7 ou superior.
- **Navegador**: Chrome, Firefox ou qualquer navegador moderno.

## Estrutura do Projeto

evograph/
├── css/
│   └── style.css         # Estilos gerais e responsivos
├── js/
│   └── script.js         # Scripts JavaScript (ex.: AJAX para reset de senha)
├── dashboard.php         # Painel principal por cargo
├── db_connection.php     # Conexão ao banco MySQL
├── reset_password.php    # Script para alteração de senha
├── fetch_alunos.php      # Retorna alunos por turma (AJAX)
├── cadastro_turma.php    # Cadastro de turmas (Coordenador/Diretor)
├── cadastro_funcionario.php # Cadastro de funcionários (Coordenador/Diretor)
├── cadastro_aluno.php    # Cadastro de alunos (Coordenador/Diretor)
├── index.html            # Página inicial (login)
├── logout.php            # Script de logout
└── README.md             # Documentação do projeto


## Instalação

### 1. Clonar o Repositório
```bash
git clone https://github.com/evanderson-dev/evoGraph.git
cd evograph
```

### 2. Configurar o Banco de Dados
```bash
CREATE DATABASE evograph_db CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'evograph_user'@'localhost' IDENTIFIED BY 'sua_senha';
GRANT ALL PRIVILEGES ON evograph_db.* TO 'evograph_user'@'localhost';
FLUSH PRIVILEGES;
```
### 3. Configurar o Arquivo de Conexão
Edite o arquivo `db_connection.php` com as credenciais do banco de dados:
```php
<?php
$host = 'localhost';
$db = 'evograph_db';
$user = 'evograph_user';
$pass = 'sua_senha';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
```
### 4. Executar o Projeto
### Abra o navegador e acesse `http://localhost/evograph` para iniciar o sistema.
### Configurar o Banco de Dados
### Crie o banco de dados no MySQL:

```bash
CREATE DATABASE evograph_db CHARACTER SET utf8 COLLATE utf8_general_ci;
```
### Crie o usuário e conceda permissões:

```bash
CREATE USER 'evograph_user'@'localhost' IDENTIFIED BY 'sua_senha';
GRANT ALL PRIVILEGES ON evograph_db.* TO 'evograph_user'@'localhost';
FLUSH PRIVILEGES;
```

### Crie as tabelas:
```bash
USE evograph_db;

CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    sobrenome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255),
    cargo VARCHAR(50)
);

CREATE TABLE turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    ano INT,
    professor_id INT,
    FOREIGN KEY (professor_id) REFERENCES funcionarios(id)
);

CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    matricula VARCHAR(50) UNIQUE,
    turma_id INT,
    FOREIGN KEY (turma_id) REFERENCES turmas(id)
);
```
