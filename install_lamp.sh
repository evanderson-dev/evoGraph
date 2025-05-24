#!/bin/bash

# Script para instalar e configurar LAMP (Apache, MySQL, PHP) no Ubuntu Server
# Inclui criação de banco de dados e usuário MySQL
# Autor: evanderson.coder@gmail.com

# Como Usar o Script
# 1. Salvar o Script:
#   Crie ou substitua o arquivo install_lamp.sh:
#   nano install_lamp.sh
# 2. Tornar Executável:
#   Dê permissão de execução:
#   chmod +x install_lamp.sh
# 3. Executar o Script:
#   Rode como superusuário:
#   sudo ./install_lamp.sh

# 1. Atualizar o sistema
echo "Atualizando o sistema..."
sudo apt update && sudo apt upgrade -y

# 2. Instalar o Git
echo "Instalando o Git..."
sudo apt install git -y

# Verificar a versão do Git instalada
echo "Verificando a versão do Git..."
git --version

# 3. Instalar o Apache
echo "Instalando o Apache..."
sudo apt install apache2 -y

# 4. Iniciar e habilitar o Apache
echo "Iniciando e habilitando o Apache..."
sudo systemctl start apache2
sudo systemctl enable apache2

# 5. Instalar o MySQL
echo "Instalando o MySQL..."
sudo apt install mysql-server -y

# 6. Iniciar e habilitar o MySQL
echo "Iniciando e habilitando o MySQL..."
sudo systemctl start mysql
sudo systemctl enable mysql

# 7. Executar configuração segura do MySQL
echo "Executando configuração segura do MySQL..."
# Respostas automatizadas para mysql_secure_installation:
# - Não configurar senha root (padrão Ubuntu usa auth_socket)
# - Remover usuários anônimos: Sim
# - Desabilitar login root remoto: Sim
# - Remover banco de testes: Sim
# - Recarregar privilégios: Sim
printf "\nn\ny\ny\ny\ny\n" | sudo mysql_secure_installation

# 8. Criar banco de dados, usuário MySQL e tabelas
echo "Criando banco de dados 'evograph_db', usuário 'admEvoGraph' e tabelas..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS evograph_db;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'admEvoGraph'@'localhost' IDENTIFIED BY 'evoGraph123';"
sudo mysql -e "GRANT ALL PRIVILEGES ON evograph_db.* TO 'admEvoGraph'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Criar tabelas no banco evograph_db
sudo mysql -e "USE evograph_db;
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    rf VARCHAR(20) UNIQUE NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    cargo VARCHAR(50) NOT NULL DEFAULT 'Professor'
);"

sudo mysql -e "USE evograph_db;
CREATE TABLE IF NOT EXISTS turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    ano INT NOT NULL,
    professor_id INT,
    FOREIGN KEY (professor_id) REFERENCES funcionarios(id)
);"

sudo mysql -e "USE evograph_db;
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    data_matricula DATETIME DEFAULT CURRENT_TIMESTAMP,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    nome_pai VARCHAR(100),
    nome_mae VARCHAR(100),
    turma_id INT,
    foto VARCHAR(255) DEFAULT NULL,
    email VARCHAR(150) UNIQUE DEFAULT NULL,
    FOREIGN KEY (turma_id) REFERENCES turmas(id)
);"

sudo mysql -e "USE evograph_db;
CREATE TABLE IF NOT EXISTS respostas_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT,
    formulario_id VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    dados_json JSON NOT NULL,
    pontuacao DECIMAL(5,2) DEFAULT NULL,
    INDEX idx_email (email),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"

sudo mysql -e "USE evograph_db;
CREATE TABLE IF NOT EXISTS perguntas_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulario_id VARCHAR(50),
    bncc_habilidade VARCHAR(50),
    pergunta_texto TEXT NOT NULL,
    resposta_correta VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);"

# Adicionar coluna bncc_habilidade_id à tabela respostas_formulario
sudo mysql -e "USE evograph_db;
ALTER TABLE respostas_formulario
ADD COLUMN bncc_habilidade_id INT DEFAULT NULL,
ADD CONSTRAINT fk_bncc_habilidade FOREIGN KEY (bncc_habilidade_id) REFERENCES habilidades_bncc(id) ON DELETE SET NULL;"

# Modificar a tabela perguntas_formulario para usar bncc_habilidade_id
sudo mysql -e "USE evograph_db;
ALTER TABLE perguntas_formulario
CHANGE COLUMN bncc_habilidade bncc_habilidade_id INT DEFAULT NULL,
ADD CONSTRAINT fk_perguntas_bncc_habilidade FOREIGN KEY (bncc_habilidade_id) REFERENCES habilidades_bncc(id) ON DELETE SET NULL;"

# Tabela para armazenar os anos escolares
sudo mysql -e "USE evograph_db;
CREATE TABLE anos_escolares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    ordem INT NOT NULL
);"

# Tabela para armazenar as disciplinas
sudo mysql -e "USE evograph_db;
CREATE TABLE disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);"

# Tabela para armazenar as habilidades BNCC
sudo mysql -e "USE evograph_db;
CREATE TABLE habilidades_bncc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ano_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    descricao TEXT NOT NULL,
    FOREIGN KEY (ano_id) REFERENCES anos_escolares(id) ON DELETE RESTRICT,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE RESTRICT
);"

# 9. Instalar o PHP e módulos necessários
echo "Instalando o PHP e módulos..."
sudo apt install php libapache2-mod-php php-mysql php-cli php-common php-json php-opcache php-mbstring php-zip php-gd -y

# 10. Reiniciar o Apache para carregar o PHP
echo "Reiniciando o Apache..."
sudo systemctl restart apache2

# 11. Clonar o repositório evoGraph diretamente em /var/www/html
echo "Clonando o repositório evoGraph em /var/www/html..."
# Remover arquivos existentes em /var/www/html (ex.: info.php) para evitar conflitos
sudo rm -rf /var/www/html/*
cd /var/www/html
sudo git clone https://github.com/evanderson-dev/evoGraph.git .

# 12. Ajustar permissões do diretório web
echo "Ajustando permissões do diretório /var/www/html..."
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

# 13. Criar um arquivo de teste PHP (opcional, caso o repositório não sobrescreva)
echo "Criando arquivo de teste PHP (info.php)..."
sudo bash -c 'echo "<?php phpinfo(); ?>" > /var/www/html/info.php'

# 14. Obter IPs público e local
echo "Obtendo endereços IP do servidor..."
# IP Público (se houver conectividade com a internet)
PUBLIC_IP=$(curl -s --connect-timeout 5 ifconfig.me || echo "Não disponível")
# IP Local (primeiro IP da interface ativa)
LOCAL_IP=$(hostname -I | awk '{print $1}' || echo "Não disponível")

# 15. Exibir informações finais
echo "Instalação concluída!"
echo "Git instalado. Use 'git --version' para verificar a versão."
echo "Repositório evoGraph clonado em /var/www/html."
if [ "$PUBLIC_IP" != "Não disponível" ]; then
    echo "Acesse a aplicação em: http://$PUBLIC_IP/"
    echo "Acesse o teste PHP em: http://$PUBLIC_IP/info.php (se não sobrescrito pelo repositório)"
fi
echo "Acesse a aplicação localmente em: http://$LOCAL_IP/"
echo "Acesse o teste PHP localmente em: http://$LOCAL_IP/info.php (se não sobrescrito pelo repositório)"
echo "Banco de dados 'evograph_db' criado com as tabelas: funcionarios, turmas, alunos, respostas_formulario, perguntas_formulario."
echo "Usuário MySQL 'admEvoGraph' criado com privilégios no banco 'evograph_db'."
echo "MySQL configurado com segurança (usuários anônimos removidos, login root remoto desabilitado, banco de testes removido)."