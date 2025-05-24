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
    funcionario_id INT,
    bncc_habilidade_id INT,

    INDEX idx_email (email),
    INDEX (aluno_id),
    INDEX (funcionario_id),
    INDEX fk_bncc_habilidade (bncc_habilidade_id),

    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE SET NULL,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE SET NULL,
    FOREIGN KEY (bncc_habilidade_id) REFERENCES habilidades_bncc(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela perguntas_formulario
CREATE TABLE perguntas_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulario_id VARCHAR(50) DEFAULT NULL,
    bncc_habilidade VARCHAR(50) DEFAULT NULL,
    pergunta_texto TEXT NOT NULL,
    resposta_correta VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    bncc_habilidade_id INT DEFAULT NULL,

    INDEX (formulario_id),
    INDEX (bncc_habilidade),

    FOREIGN KEY (bncc_habilidade_id) REFERENCES habilidades_bncc(id) ON DELETE SET NULL
); ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela para armazenar os anos escolares
CREATE TABLE anos_escolares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE, -- Ex.: "6º Ano", "7º Ano"
    ordem INT NOT NULL -- Para ordenação (ex.: 6 para 6º Ano)
);

-- Tabela para armazenar as disciplinas
CREATE TABLE disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE -- Ex.: "Geografia", "Matemática"
);

-- Tabela para armazenar as habilidades BNCC
CREATE TABLE habilidades_bncc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ano_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE, -- Ex.: "EF06GE10"
    descricao TEXT NOT NULL, -- Descrição completa da habilidade
    FOREIGN KEY (ano_id) REFERENCES anos_escolares(id) ON DELETE RESTRICT,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE RESTRICT
);