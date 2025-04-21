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
    email VARCHAR(150),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    dados_json JSON NOT NULL,
    formulario_id VARCHAR(50) DEFAULT NULL,
    pontuacao DECIMAL(5,2) DEFAULT NULL,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE SET NULL
);

-- Tabela perguntas_formulario
CREATE TABLE perguntas_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulario_id VARCHAR(50),
    pergunta_texto TEXT NOT NULL,
    bncc_habilidade VARCHAR(50), -- Exemplo: EF06GE10
    resposta_correta VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Inserindo funcionários (professores, coordenadores, diretor e administrador)
INSERT INTO funcionarios (email, senha, nome, sobrenome, data_nascimento, rf, cargo) VALUES
('administrador@database.com', '$2y$10$exemploHashAqui', 'Adm', 'Db', '2025-01-01', 'RF999', 'Administrador'),
('diretor@escola.com', '$2y$10$exemploHashAqui', 'Carlos', 'Silva', '1975-03-10', 'RF001', 'Diretor'),
('coordenador1@escola.com', '$2y$10$exemploHashAqui', 'Mariana', 'Souza', '1980-07-25', 'RF002', 'Coordenador'),
('coordenador2@escola.com', '$2y$10$exemploHashAqui', 'Roberto', 'Lima', '1982-05-15', 'RF003', 'Coordenador'),
('prof1@escola.com', '$2y$10$exemploHashAqui', 'Ana', 'Oliveira', '1985-02-20', 'RF004', 'Professor'),
('prof2@escola.com', '$2y$10$exemploHashAqui', 'João', 'Pereira', '1983-06-10', 'RF005', 'Professor'),
('prof3@escola.com', '$2y$10$exemploHashAqui', 'Fernanda', 'Martins', '1990-09-30', 'RF006', 'Professor'),
('prof4@escola.com', '$2y$10$exemploHashAqui', 'Pedro', 'Almeida', '1988-11-12', 'RF007', 'Professor'),
('prof5@escola.com', '$2y$10$exemploHashAqui', 'Carla', 'Ferreira', '1987-04-05', 'RF008', 'Professor'),
('prof6@escola.com', '$2y$10$exemploHashAqui', 'Bruno', 'Rodrigues', '1991-12-15', 'RF009', 'Professor');

-- Inserções na tabela turmas
INSERT INTO turmas (nome, ano, professor_id) VALUES
    ('1º Ano A', 1, 5),
    ('1º Ano B', 1, 6),
    ('2º Ano A', 2, 7),
    ('2º Ano B', 2, 8),
    ('3º Ano A', 3, 9),
    ('3º Ano B', 3, 10);

INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, nome_pai, nome_mae, turma_id) VALUES
-- Turma 1º Ano A (id 1)
('João', 'Silva', '2010-01-15', '00000001', 'José Silva', 'Maria Silva', 1),
('Ana', 'Santos', '2010-02-20', '00000002', 'Carlos Santos', 'Luiza Santos', 1),
('Pedro', 'Oliveira', '2010-03-10', '00000003', 'Antônio Oliveira', 'Clara Oliveira', 1),
('Mariana', 'Costa', '2010-04-25', '00000004', 'Paulo Costa', 'Beatriz Costa', 1),
('Lucas', 'Pereira', '2010-05-12', '00000005', 'Marcos Pereira', 'Sofia Pereira', 1),
('Julia', 'Ribeiro', '2010-06-18', '00000006', 'Rafael Ribeiro', 'Fernanda Ribeiro', 1),
('Gabriel', 'Almeida', '2010-07-30', '00000007', 'Ricardo Almeida', 'Patrícia Almeida', 1),
('Isabela', 'Lima', '2010-08-05', '00000008', 'Roberto Lima', 'Camila Lima', 1),
('Matheus', 'Fernandes', '2010-09-22', '00000009', 'Eduardo Fernandes', 'Aline Fernandes', 1),
('Larissa', 'Gomes', '2010-10-14', '00000010', 'Sérgio Gomes', 'Vanessa Gomes', 1),

-- Turma 1º Ano B (id 2)
('Bruno', 'Mendes', '2010-01-25', '00000011', 'André Mendes', 'Tatiana Mendes', 2),
('Clara', 'Barros', '2010-02-28', '00000012', 'Gustavo Barros', 'Renata Barros', 2),
('Diego', 'Rocha', '2010-03-15', '00000013', 'Felipe Rocha', 'Juliana Rocha', 2),
('Elisa', 'Nunes', '2010-04-10', '00000014', 'Thiago Nunes', 'Mônica Nunes', 2),
('Felipe', 'Souza', '2010-05-20', '00000015', 'João Souza', 'Carla Souza', 2),
('Giovana', 'Dias', '2010-06-05', '00000016', 'Pedro Dias', 'Lúcia Dias', 2),
('Henrique', 'Martins', '2010-07-12', '00000017', 'Marcelo Martins', 'Ana Martins', 2),
('Ingrid', 'Carvalho', '2010-08-18', '00000018', 'Luiz Carvalho', 'Beatriz Carvalho', 2),
('Jonas', 'Farias', '2010-09-30', '00000019', 'Ronaldo Farias', 'Silvia Farias', 2),
('Kelly', 'Moreira', '2010-10-22', '00000020', 'Vitor Moreira', 'Paula Moreira', 2),

-- Turma 1º Ano C (id 3)
('Laura', 'Teixeira', '2010-01-30', '00000021', 'Jorge Teixeira', 'Helena Teixeira', 3),
('Marcos', 'Araújo', '2010-02-15', '00000022', 'Leandro Araújo', 'Viviane Araújo', 3),
('Natália', 'Castro', '2010-03-25', '00000023', 'Bruno Castro', 'Débora Castro', 3),
('Otávio', 'Monteiro', '2010-04-12', '00000024', 'Fábio Monteiro', 'Raquel Monteiro', 3),
('Patrícia', 'Freitas', '2010-05-18', '00000025', 'Daniel Freitas', 'Célia Freitas', 3),
('Rafael', 'Lopes', '2010-06-20', '00000026', 'Alexandre Lopes', 'Mariana Lopes', 3),
('Sofia', 'Barbosa', '2010-07-05', '00000027', 'Vinícius Barbosa', 'Cláudia Barbosa', 3),
('Tiago', 'Moura', '2010-08-10', '00000028', 'Renato Moura', 'Simone Moura', 3),
('Vitória', 'Campos', '2010-09-15', '00000029', 'Igor Campos', 'Fernanda Campos', 3),
('Wagner', 'Reis', '2010-10-28', '00000030', 'Samuel Reis', 'Adriana Reis', 3),

-- Turma 1º Ano D (id 4)
('Alice', 'Viana', '2010-01-10', '00000031', 'Ricardo Viana', 'Luciana Viana', 4),
('Bernardo', 'Duarte', '2010-02-22', '00000032', 'Eduardo Duarte', 'Marta Duarte', 4),
('Camila', 'Tavares', '2010-03-18', '00000033', 'Paulo Tavares', 'Elaine Tavares', 4),
('Davi', 'Borges', '2010-04-30', '00000034', 'Roberto Borges', 'Sônia Borges', 4),
('Eduarda', 'Melo', '2010-05-15', '00000035', 'Marcos Melo', 'Teresa Melo', 4),
('Fábio', 'Correia', '2010-06-25', '00000036', 'José Correia', 'Rita Correia', 4),
('Gabriela', 'Santana', '2010-07-20', '00000037', 'Antônio Santana', 'Clara Santana', 4),
('Hugo', 'Vieira', '2010-08-05', '00000038', 'Carlos Vieira', 'Beatriz Vieira', 4),
('Isadora', 'Cruz', '2010-09-12', '00000039', 'Rafael Cruz', 'Patrícia Cruz', 4),
('Júlio', 'Pinto', '2010-10-18', '00000040', 'Sérgio Pinto', 'Vanessa Pinto', 4),

-- Turma 1º Ano E (id 5)
('Karla', 'Nogueira', '2010-01-20', '00000041', 'André Nogueira', 'Tatiana Nogueira', 5),
('Leonardo', 'Ramos', '2010-02-10', '00000042', 'Gustavo Ramos', 'Renata Ramos', 5),
('Manuela', 'Cardoso', '2010-03-28', '00000043', 'Felipe Cardoso', 'Juliana Cardoso', 5),
('Nicolas', 'Fonseca', '2010-04-15', '00000044', 'Thiago Fonseca', 'Mônica Fonseca', 5),
('Olívia', 'Machado', '2010-05-22', '00000045', 'João Machado', 'Carla Machado', 5),
('Pablo', 'Siqueira', '2010-06-10', '00000046', 'Pedro Siqueira', 'Lúcia Siqueira', 5),
('Quitéria', 'Aguiar', '2010-07-18', '00000047', 'Marcelo Aguiar', 'Ana Aguiar', 5),
('Rodrigo', 'Bezerra', '2010-08-25', '00000048', 'Luiz Bezerra', 'Beatriz Bezerra', 5),
('Sara', 'Leal', '2010-09-30', '00000049', 'Ronaldo Leal', 'Silvia Leal', 5),
('Tomás', 'Peixoto', '2010-10-05', '00000050', 'Vitor Peixoto', 'Paula Peixoto', 5),

-- Turma 1º Ano F (id 6)
('Ursula', 'Batista', '2010-01-12', '00000051', 'Jorge Batista', 'Helena Batista', 6),
('Victor', 'Lacerda', '2010-02-18', '00000052', 'Leandro Lacerda', 'Viviane Lacerda', 6),
('Wendy', 'Moraes', '2010-03-20', '00000053', 'Bruno Moraes', 'Débora Moraes', 6),
('Xavier', 'Andrade', '2010-04-25', '00000054', 'Fábio Andrade', 'Raquel Andrade', 6),
('Yasmin', 'Brito', '2010-05-10', '00000055', 'Daniel Brito', 'Célia Brito', 6),
('Zeca', 'Cavalcanti', '2010-06-15', '00000056', 'Alexandre Cavalcanti', 'Mariana Cavalcanti', 6),
('Aline', 'Dantas', '2010-07-22', '00000057', 'Vinícius Dantas', 'Cláudia Dantas', 6),
('Breno', 'Esteves', '2010-08-28', '00000058', 'Renato Esteves', 'Simone Esteves', 6),
('Célia', 'Figueiredo', '2010-09-05', '00000059', 'Igor Figueiredo', 'Fernanda Figueiredo', 6),
('Daniel', 'Guimarães', '2010-10-20', '00000060', 'Samuel Guimarães', 'Adriana Guimarães', 6);
