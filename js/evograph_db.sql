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
    FOREIGN KEY (turma_id) REFERENCES turmas(id)
);

-- Inserções na tabela funcionarios
INSERT INTO funcionarios (email, senha, nome, sobrenome, data_nascimento, rf, cargo) VALUES
    ('professor@escola.com', '$2y$10$exemploHashAqui', 'João', 'Silva', '1980-05-15', 'RF001', 'Professor'),
    ('coordenador@escola.com', '$2y$10$exemploHashAqui', 'Maria', 'Oliveira', '1975-08-22', 'RF002', 'Coordenador'),
    ('professor2@escola.com', '$2y$10$exemploHashAqui', 'Pedro', 'Santos', '1985-03-10', 'RF003', 'Professor'),
    ('professor3@escola.com', '$2y$10$exemploHashAqui', 'Ana', 'Costa', '1990-11-30', 'RF004', 'Professor'),
    ('diretor@escola.com', '$2y$10$exemploHashAqui', 'Carlos', 'Mendes', '1970-01-25', 'RF005', 'Diretor');

-- Inserções na tabela turmas (mínimo de 2 por professor)
INSERT INTO turmas (nome, ano, professor_id) VALUES
    ('5º Ano A', 5, 1),  -- João Silva
    ('6º Ano B', 6, 1),  -- João Silva
    ('5º Ano C', 5, 3),  -- Pedro Santos
    ('6º Ano D', 6, 3),  -- Pedro Santos
    ('7º Ano E', 7, 4),  -- Ana Costa
    ('8º Ano F', 8, 4);  -- Ana Costa

-- Inserções na tabela alunos (mínimo de 5 por turma)
-- Turma 1: 5º Ano A (João Silva)
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
    ('João', 'Silva Jr', '2010-04-12', 'MAT001', 'João Silva', 'Clara Silva', 1),
    ('Maria', 'Oliveira', '2011-07-25', 'MAT002', 'Carlos Oliveira', 'Maria Oliveira', 1),
    ('Lucas', 'Pereira', '2010-06-15', 'MAT003', 'Luis Pereira', 'Beatriz Pereira', 1),
    ('Sofia', 'Almeida', '2011-02-10', 'MAT004', 'Rafael Almeida', 'Julia Almeida', 1),
    ('Gabriel', 'Rocha', '2010-09-30', 'MAT005', 'Marcos Rocha', 'Ana Rocha', 1);

-- Turma 2: 6º Ano B (João Silva)
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
    ('Pedro', 'Santos', '2009-09-18', 'MAT006', 'Paulo Santos', 'Lucia Santos', 2),
    ('Beatriz', 'Lima', '2011-03-22', 'MAT007', 'João Lima', 'Fernanda Lima', 2),
    ('Carlos', 'Souza', '2008-12-10', 'MAT008', 'Roberto Souza', 'Juliana Souza', 2),
    ('Julia', 'Mendes', '2009-11-05', 'MAT009', 'André Mendes', 'Carla Mendes', 2),
    ('Rafael', 'Gomes', '2010-01-15', 'MAT010', 'Eduardo Gomes', 'Laura Gomes', 2);

-- Turma 3: 5º Ano C (Pedro Santos)
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
    ('Ana', 'Costa', '2012-01-05', 'MAT011', 'Marcos Costa', 'Sofia Costa', 3),
    ('Mariana', 'Fernandes', '2011-08-20', 'MAT012', 'José Fernandes', 'Cláudia Fernandes', 3),
    ('Thiago', 'Barbosa', '2010-05-12', 'MAT013', 'Ricardo Barbosa', 'Patrícia Barbosa', 3),
    ('Larissa', 'Ribeiro', '2011-04-18', 'MAT014', 'Antônio Ribeiro', 'Renata Ribeiro', 3),
    ('Felipe', 'Nunes', '2010-07-25', 'MAT015', 'Bruno Nunes', 'Vanessa Nunes', 3);

-- Turma 4: 6º Ano D (Pedro Santos)
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
    ('Camila', 'Dias', '2009-12-01', 'MAT016', 'Fábio Dias', 'Elaine Dias', 4),
    ('Gustavo', 'Martins', '2010-03-15', 'MAT017', 'Sérgio Martins', 'Tatiana Martins', 4),
    ('Isabela', 'Araújo', '2011-06-22', 'MAT018', 'Marcelo Araújo', 'Cristina Araújo', 4),
    ('Matheus', 'Cavalcanti', '2010-09-10', 'MAT019', 'Leandro Cavalcanti', 'Débora Cavalcanti', 4),
    ('Letícia', 'Moraes', '2011-02-28', 'MAT020', 'Rodrigo Moraes', 'Fernanda Moraes', 4);

-- Turma 5: 7º Ano E (Ana Costa)
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
    ('Bruno', 'Lopes', '2008-11-30', 'MAT021', 'Paulo Lopes', 'Mônica Lopes', 5),
    ('Clara', 'Freitas', '2009-05-18', 'MAT022', 'Jorge Freitas', 'Silvia Freitas', 5),
    ('Diego', 'Vargas', '2008-07-12', 'MAT023', 'Renato Vargas', 'Lúcia Vargas', 5),
    ('Eduarda', 'Campos', '2009-03-25', 'MAT024', 'Fernando Campos', 'Helena Campos', 5),
    ('Vinicius', 'Teixeira', '2008-10-05', 'MAT025', 'Daniel Teixeira', 'Priscila Teixeira', 5);

-- Turma 6: 8º Ano F (Ana Costa)
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
    ('Helena', 'Barros', '2007-12-15', 'MAT026', 'Ricardo Barros', 'Viviane Barros', 6),
    ('Igor', 'Farias', '2008-04-20', 'MAT027', 'Alexandre Farias', 'Marina Farias', 6),
    ('Júlia', 'Monteiro', '2007-09-10', 'MAT028', 'Gustavo Monteiro', 'Aline Monteiro', 6),
    ('Leonardo', 'Ramos', '2008-06-30', 'MAT029', 'Sandro Ramos', 'Beatriz Ramos', 6),
    ('Natália', 'Alves', '2007-11-22', 'MAT030', 'Roberto Alves', 'Camila Alves', 6);
