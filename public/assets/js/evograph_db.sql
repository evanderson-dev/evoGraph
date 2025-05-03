-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS evograph_db;
USE evograph_db;

-- Tabela funcionarios
CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    rf VARCHAR(20) UNIQUE NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    cargo VARCHAR(50) NOT NULL DEFAULT 'Professor'
);

-- Tabela turmas
CREATE TABLE turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    ano INT NOT NULL,
    professor_id INT,
    FOREIGN KEY (professor_id) REFERENCES funcionarios(id)
);

-- Tabela alunos
CREATE TABLE alunos (
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
);

-- Tabela respostas_formulario
CREATE TABLE respostas_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT,
    formulario_id VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    dados_json JSON NOT NULL,
    pontuacao DECIMAL(5,2) DEFAULT NULL,
    INDEX idx_email (email),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela perguntas_formulario
CREATE TABLE perguntas_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulario_id VARCHAR(50),
    bncc_habilidade VARCHAR(50),
    pergunta_texto TEXT NOT NULL,
    resposta_correta VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);