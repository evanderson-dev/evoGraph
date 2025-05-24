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

-- Inserir alguns dados de exemplo
INSERT INTO anos_escolares (nome, ordem) VALUES
    ('6º Ano', 6),
    ('7º Ano', 7),
    ('8º Ano', 8);

INSERT INTO disciplinas (nome) VALUES
    ('Geografia'),
    ('Matemática'),
    ('História');

-- Inserir habilidades BNCC para o 7º Ano de Geografia
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (2, 1, 'EF07GE01', 'Avaliar, por meio de exemplos extraídos dos meios de comunicação, ideias e estereótipos acerca das paisagens e da formação territorial do Brasil.'),
    (2, 1, 'EF07GE02', 'Analisar a influência dos fluxos econômicos e populacionais na formação socioeconômica e territorial do Brasil, compreendendo os conflitos e as tensões históricas e contemporâneas.'),
    (2, 1, 'EF07GE03', 'Selecionar argumentos que reconheçam as territorialidades dos povos indígenas originários, das comunidades remanescentes de quilombos, de povos das florestas e do cerrado, de ribeirinhos e caiçaras, entre outros grupos sociais do campo e da cidade, como direitos legais dessas comunidades.'),
    (2, 1, 'EF07GE04', 'Analisar a distribuição territorial da população brasileira, considerando a diversidade étnico-cultural (indígena, africana, europeia e asiática), assim como aspectos de renda, sexo e idade nas regiões brasileiras.'),
    (2, 1, 'EF07GE05', 'Analisar fatos e situações representativas das alterações ocorridas entre o período mercantilista e o advento do capitalismo.'),
    (2, 1, 'EF07GE06', 'Discutir em que medida a produção, a circulação e o consumo de mercadorias provocam impactos ambientais, assim como influem na distribuição de riquezas, em diferentes lugares.'),
    (2, 1, 'EF07GE07', 'Analisar a influência e o papel das redes de transporte e comunicação na configuração do território brasileiro.'),
    (2, 1, 'EF07GE08', 'Estabelecer relações entre os processos de industrialização e inovação tecnológica com as transformações socioeconômicas do território brasileiro.'),
    (2, 1, 'EF07GE09', 'Interpretar e elaborar mapas temáticos e históricos, inclusive utilizando tecnologias digitais, com informações demográficas e econômicas do Brasil (cartogramas), identificando padrões espaciais, regionalizações e analogias espaciais.'),
    (2, 1, 'EF07GE10', 'Elaborar e interpretar gráficos de barras, gráficos de setores e histogramas, com base em dados socioeconômicos das regiões brasileiras.'),
    (2, 1, 'EF07GE11', 'Caracterizar dinâmicas dos componentes físico-naturais no território nacional, bem como sua distribuição e biodiversidade (Florestas Tropicais, Cerrados, Caatingas, Campos Sulinos e Matas de Araucária).'),
    (2, 1, 'EF07GE12', 'Comparar unidades de conservação existentes no Município de residência e em outras localidades brasileiras, com base na organização do Sistema Nacional de Unidades de Conservação (SNUC).');

-- Inserir habilidades BNCC para o 7º Ano de Matemática
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (2, 2, 'EF07MA01', 'Resolver e elaborar problemas que envolvam as quatro operações com números inteiros e racionais.'),
    (2, 2, 'EF07MA02', 'Resolver e elaborar problemas que envolvam porcentagens e proporções.'),
    (2, 2, 'EF07MA03', 'Resolver e elaborar problemas que envolvam medidas de comprimento, área e volume.'),
    (2, 2, 'EF07MA04', 'Resolver e elaborar problemas que envolvam a leitura e interpretação de gráficos e tabelas.');

-- Inserir habilidades BNCC para o 7º Ano de História
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (2, 3, 'EF07HI01', 'Analisar a formação do Brasil colonial e suas relações com o mundo.'),
    (2, 3, 'EF07HI02', 'Compreender a importância da escravidão na formação da sociedade brasileira.'),
    (2, 3, 'EF07HI03', 'Analisar os processos de independência e as transformações sociais e políticas no Brasil.'),
    (2, 3, 'EF07HI04', 'Compreender a importância da Revolução Industrial e suas consequências sociais e econômicas.');

-- Inserir habilidades BNCC para o 6º Ano de Geografia
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (1, 1, 'EF06GE01', 'Identificar e analisar as características das paisagens naturais e culturais do Brasil.'),
    (1, 1, 'EF06GE02', 'Compreender a importância da água e dos recursos hídricos para a vida humana.'),
    (1, 1, 'EF06GE03', 'Analisar os impactos ambientais das atividades humanas no território brasileiro.'),
    (1, 1, 'EF06GE04', 'Compreender a importância da preservação ambiental e das áreas de proteção.');

-- Inserir habilidades BNCC para o 6º Ano de Matemática
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (1, 2, 'EF06MA01', 'Resolver e elaborar problemas que envolvam as quatro operações com números naturais.'),
    (1, 2, 'EF06MA02', 'Resolver e elaborar problemas que envolam frações e decimais.'),
    (1, 2, 'EF06MA03', 'Resolver e elaborar problemas que envolvam medidas de comprimento, área e volume.'),
    (1, 2, 'EF06MA04', 'Resolver e elaborar problemas que envolvam a leitura e interpretação de gráficos e tabelas.');

-- Inserir habilidades BNCC para o 6º Ano de História
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (1, 3, 'EF06HI01', 'Analisar a formação do Brasil pré-colonial e suas relações com os povos indígenas.'),
    (1, 3, 'EF06HI02', 'Compreender a importância da colonização portuguesa e suas consequências sociais e econômicas.'),
    (1, 3, 'EF06HI03', 'Analisar os processos de resistência indígena e africana no Brasil colonial.'),
    (1, 3, 'EF06HI04', 'Compreender a importância da Revolução Francesa e suas consequências para o mundo.');

-- Inserir habilidades BNCC para o 8º Ano de Geografia
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (3, 1, 'EF08GE01', 'Analisar a formação do território brasileiro e suas relações com o mundo.'),
    (3, 1, 'EF08GE02', 'Compreender a importância da agricultura e da pecuária na formação da sociedade brasileira.'),
    (3, 1, 'EF08GE03', 'Analisar os impactos ambientais das atividades econômicas no território brasileiro.'),
    (3, 1, 'EF08GE04', 'Compreender a importância da diversidade cultural e étnica no Brasil.');

-- Inserir habilidades BNCC para o 8º Ano de Matemática
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (3, 2, 'EF08MA01', 'Resolver e elaborar problemas que envolvam as quatro operações com números racionais.'),
    (3, 2, 'EF08MA02', 'Resolver e elaborar problemas que envolam porcentagens e proporções.'),
    (3, 2, 'EF08MA03', 'Resolver e elaborar problemas que envolvam medidas de comprimento, área e volume.'),
    (3, 2, 'EF08MA04', 'Resolver e elaborar problemas que envolvam a leitura e interpretação de gráficos e tabelas.');

-- Inserir habilidades BNCC para o 8º Ano de História
INSERT INTO habilidades_bncc (ano_id, disciplina_id, codigo, descricao) VALUES
    (3, 3, 'EF08HI01', 'Analisar a formação do Brasil contemporâneo e suas relações com o mundo.'),
    (3, 3, 'EF08HI02', 'Compreender a importância da industrialização e suas consequências sociais e econômicas.'),
    (3, 3, 'EF08HI03', 'Analisar os processos de resistência social e política no Brasil contemporâneo.'),
    (3, 3, 'EF08HI04', 'Compreender a importância da globalização e suas consequências para o mundo.');

