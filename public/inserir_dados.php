<?php
$host = "localhost";
$username = "admEvoGraph";
$password = "evoGraph123";
$database = "evograph_db";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$database;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    $sql = <<<SQL
        -- Inserindo funcionários (professores, coordenadores, diretor e administrador)
        INSERT INTO funcionarios (email, senha, nome, sobrenome, data_nascimento, rf, cargo)
        VALUES
        ('administrador@database.com', '$2y$10$exemploHashAqui', 'Admnistrador', 'Db', '2025-01-01', 'RF999', 'Administrador'),
        ('diretor@escola.com', '$2y$10$exemploHashAqui', 'Diretor', 'Fictício', '1975-03-10', 'RF001', 'Diretor'),
        ('coordenador@escola.com', '$2y$10$exemploHashAqui', 'Cordenador', 'Fictício', '1980-07-25', 'RF002', 'Coordenador'),
        ('professor@escola.com', '$2y$10$exemploHashAqui', 'Professor', 'Fictício', '1975-03-10', 'RF003', 'Professor'),
        ('2206345@aluno.univesp.br', '$2y$10$exemploHashAqui', 'Marcio', 'Dias', '2000-02-20', '2206345', 'Professor'),
        ('23225670@aluno.univesp.br', '$2y$10$exemploHashAqui', 'Sueanne', 'Ravena', '2000-06-10', '23225670', 'Administrador');

        -- Inserir anos escolares
        INSERT INTO anos_escolares (nome, ordem) VALUES
            ('6º Ano', 6),
            ('7º Ano', 7),
            ('8º Ano', 8);

        -- Inserir disciplinas
        INSERT INTO disciplinas (nome) VALUES
            ('Geografia'),
            ('Matemática'),
            ('História');

        -- Inserções na tabela turmas
        INSERT INTO turmas (nome, ano, professor_id)
        VALUES
        ('7º Ano F', 7, 4),
        ('7º Ano G', 7, 4);
        
        -- Inserir alunos
        INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, turma_id, email)
        VALUES 
        ('Ariane', 'da silva dos santos', '2012-01-01', '119042025', 1, 'aluno.1228874669@educacaopg.sp.gov.br'),
        ('Bruno', 'ribeiro dos santos', '2012-01-01', '219042025', 1, 'aluno.1131563396@educacaopg.sp.gov.br'),
        ('Cristian', 'N/A', '2012-01-01', '319042025', 1, 'aluno.120011808x@educacaopg.sp.gov.br'),
        ('Emillyserra', 'N/A', '2012-01-01', '419042025', 1, 'aluno.115037925x@educacaopg.sp.gov.br'),
        ('Enzo', 'lucca machado nunes', '2012-01-01', '519042025', 1, 'aluno.1163187628@educacaopg.sp.gov.br'),
        ('Felipe', 'Francisco Rodrigues Souza', '2012-01-01', '619042025', 1, 'aluno.1137547972@educacaopg.sp.gov.br'),
        ('Kaique', 'N/A', '2012-01-01', '719042025', 1, 'aluno.115960213x@educacaopg.sp.gov.br'),
        ('Leonardo', 'N/A', '2012-01-01', '819042025', 1, 'aluno.1137549269@educacaopg.sp.gov.br'),
        ('Lorrainy', 'gabrielly caetano de jesus', '2012-01-01', '919042025', 1, 'aluno.120406071x@educacaopg.sp.gov.br'),
        ('Lucas', 'cavalacante', '2012-01-01', '1019042025', 1, 'aluno.1150025177@educacaopg.sp.gov.br'),
        ('Luiza', 'evangelista dos santos.', '2012-01-01', '1119042025', 1, 'aluno.113915977x@educacaopg.sp.gov.br'),
        ('Maria', 'fernanda de souza cardoso', '2012-01-01', '1219042025', 1, 'aluno.1150848662@educacaopg.sp.gov.br'),
        ('Mariana', 'N/A', '2012-01-01', '1319042025', 1, 'aluno.1129348155@educacaopg.sp.gov.br'),
        ('Miguel', 'Costa', '2012-01-01', '1419042025', 1, 'aluno.1135641912@educacaopg.sp.gov.br'),
        ('Nickolly', 'silverio', '2012-01-01', '1519042025', 1, 'aluno.1119927444@educacaopg.sp.gov.br'),
        ('Paulo', 'Cesar', '2012-01-01', '1619042025', 1, 'aluno.1156645773@educacaopg.sp.gov.br'),
        ('Rebeca', 'ramalho neves linhares', '2012-01-01', '1719042025', 1, 'aluno.1135562216@educacaopg.sp.gov.br'),
        ('Ruan', 'barbosa', '2012-01-01', '1819042025', 1, 'aluno.1131213907@educacaopg.sp.gov.br'),
        ('Sophia', 'de Souza Gomes Reis', '2012-01-01', '1919042025', 1, 'aluno.1156641342@educacaopg.sp.gov.br'),
        ('Augusto', 'renato', '2012-01-01', '2019042025', 2, 'aluno.1141508345@educacaopg.sp.gov.br'),
        ('Bruna', 'N/A', '2012-01-01', '2119042025', 2, 'aluno.1164942566@educacaopg.sp.gov.br'),
        ('Bruno', 'dias', '2012-01-01', '2219042025', 2, 'aluno.1144868506@educacaopg.sp.gov.br'),
        ('Eloise', 'vasconcellos alves', '2012-01-01', '2319042025', 2, 'aluno.1141827906@educacaopg.sp.gov.br'),
        ('Fernanda', 'de arruda silva oier', '2012-01-01', '2419042025', 2, 'aluno.1148652267@educacaopg.sp.gov.br'),
        ('Gabrielly', 'Vitoria Alves De Oliveira', '2012-01-01', '2519042025', 2, 'aluno.1126178524@educacaopg.sp.gov.br'),
        ('João', 'alessandro nascimento dos santos', '2012-01-01', '2619042025', 2, 'aluno.1156640180@educacaopg.sp.gov.br'),
        ('Julia', 'Kauany Alves Bueno', '2012-01-01', '2719042025', 2, 'aluno.1139040273@educacaopg.sp.gov.br'),
        ('Julia', 'nunes', '2012-01-01', '2819042025', 2, 'aluno.1137003595@educacaopg.sp.gov.br'),
        ('Kaua', 'thiago', '2012-01-01', '2919042025', 2, 'aluno.1139155404@educacaopg.sp.gov.br'),
        ('Keven', 'N/A', '2012-01-01', '3019042025', 2, 'aluno.1230733607@educacaopg.sp.gov.br'),
        ('Levi', 'rubem leal da silva', '2012-01-01', '3119042025', 2, 'aluno.1258298768@educacaopg.sp.gov.br'),
        ('Livia', 'dias rodrigues', '2012-01-01', '3219042025', 2, 'aluno.1146486996@educacaopg.sp.gov.br'),
        ('Luiz', 'gustavo', '2012-01-01', '3319042025', 2, 'aluno.1141409306@educacaopg.sp.gov.br'),
        ('Melissa', 'Gabrielly lino do Nascimento', '2012-01-01', '3419042025', 2, 'aluno.1158000352@educacaopg.sp.gov.br'),
        ('Nicolly', 'N/A', '2012-01-01', '3519042025', 2, 'aluno.1139574425@educacaopg.sp.gov.br'),
        ('Pedro', 'henrique da silva faria', '2012-01-01', '3619042025', 2, 'aluno.1123874979@educacaopg.sp.gov.br'),
        ('Richard', 'rodrigues da Silva', '2012-01-01', '3719042025', 2, 'aluno.1137551136@educacaopg.sp.gov.br'),
        ('Ryan', 'Pablo Guimarães Coata', '2012-01-01', '3819042025', 2, 'aluno.1136890646@educacaopg.sp.gov.br'),
        ('Samuel', 'Francisco Costa de Souza', '2012-01-01', '3919042025', 2, 'aluno.1138878777@educacaopg.sp.gov.br'),
        ('Thiago', 'torres11', '2012-01-01', '4019042025', 2, 'aluno.1156676058@educacaopg.sp.gov.br'),
        ('Vitoria', 'silva oliveira', '2012-01-01', '4119042025', 2, 'aluno.1129513002@educacaopg.sp.gov.br'),
        ('Wallace', 'N/A', '2012-01-01', '4219042025', 2, 'aluno.1139158570@educacaopg.sp.gov.br'),
        ('Wilson', 'N/A', '2012-01-01', '4319042025', 2, 'aluno.1129663784@educacaopg.sp.gov.br'),
        ('Yara', 'N/A', '2012-01-01', '4419042025', 2, 'aluno.1166730955@educacaopg.sp.gov.br');

        INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, turma_id, email)
        VALUES
        ('Jonathan', 'N/A', '2012-01-01', '122052025', 4, 'aluno.1154507713@educacaopg.sp.gov.br'),
        ('Aimê', 'veira bastos', '2012-01-01', '222052025', 4, 'aluno.1164935847@educacaopg.sp.gov.br'),
        ('Mauricio', 'felix santiago', '2012-01-01', '322052025', 4, 'aluno.1125102305@educacaopg.sp.gov.br'),
        ('Daniel', 'cabral da silva', '2012-01-01', '422052025', 4, 'aluno.1146555404@educacaopg.sp.gov.br'),
        ('Emanuella', 'nascimento de camargo', '2012-01-01', '522052025', 4, 'aluno.113904087x@educacaopg.sp.gov.br'),
        ('David', 'ribeiro dos santos', '2012-01-01', '622052025', 4, 'aluno.1146642234@educacaopg.sp.gov.br'),
        ('Vitinho', 'N/A', '2012-01-01', '722052025', 4, 'aluno.1140407685@educacaopg.sp.gov.br'),
        ('Pietro', 'felipe brito silva', '2012-01-01', '822052025', 4, 'aluno.1131546593@educacaopg.sp.gov.br'),
        ('André', 'victor lima de oliveira', '2012-01-01', '922052025', 4, 'aluno.1134642763@educacaopg.sp.gov.br'),
        ('Guilherme', 'N/A', '2012-01-01', '1022052025', 4, 'aluno.1143135404@educacaopg.sp.gov.br'),
        ('Thales', 'gabriel moreira lopes', '2012-01-01', '1122052025', 4, 'aluno.1136890725@educacaopg.sp.gov.br'),
        ('Isabelle', 'moura do nascimento', '2012-01-01', '1222052025', 4, 'aluno.1149637493@educacaopg.sp.gov.br'),
        ('Mirela', 'vieira ramos', '2012-01-01', '1322052025', 4, 'aluno.1163188232@educacaopg.sp.gov.br'),
        ('Manuella', 'viana angelo martinelli', '2012-01-01', '1422052025', 4, 'aluno.1163113554@educacaopg.sp.gov.br'),
        ('Diogo', 'rebelo de araujo', '2012-01-01', '1522052025', 4, 'aluno.1137541477@educacaopg.sp.gov.br'),
        ('Gabrielly', 'affonso bezerra', '2012-01-01', '1622052025', 4, 'aluno.1141905498@educacaopg.sp.gov.br'),
        ('Lorena', 'pichinin torres', '2012-01-01', '1722052025', 4, 'aluno.1143037960@educacaopg.sp.gov.br'),
        ('Isabella', 'cardoso do nascimento', '2012-01-01', '1822052025', 4, 'aluno.1148684177@educacaopg.sp.gov.br'),
        ('Alexia', 'kanashiro de carvalho da silva', '2012-01-01', '1922052025', 4, 'aluno.1137546487@educacaopg.sp.gov.br'),
        ('Douglas', 'neris da silva dos santos', '2012-01-01', '2022052025', 7, 'aluno.114874373x@educacaopg.sp.gov.br'),
        ('Vitoria', 'gomes nogueira da silva', '2012-01-01', '2122052025', 7, 'aluno.1149020337@educacaopg.sp.gov.br'),
        ('Laura', 'de araujo botero', '2012-01-01', '2222052025', 7, 'aluno.1156645931@educacaopg.sp.gov.br'),
        ('Daiane', 'ferreira da silva', '2012-01-01', '2322052025', 7, 'aluno.1227422891@educacaopg.sp.gov.br'),
        ('Dhafine', 'sofia cruz da silva', '2012-01-01', '2422052025', 7, 'aluno.1145891585@educacaopg.sp.gov.br'),
        ('Thaina', 'aguiar correia', '2012-01-01', '2522052025', 7, 'aluno.1140535389@educacaopg.sp.gov.br'),
        ('Beatriz', 'santana santos', '2012-01-01', '2622052025', 7, 'aluno.1149737554@educacaopg.sp.gov.br'),
        ('Raphael', 'alves da silva', '2012-01-01', '2722052025', 7, 'aluno.1137005373@educacaopg.sp.gov.br'),
        ('Kimberly', 'gabriely silva', '2012-01-01', '2822052025', 7, 'aluno.1126247704@educacaopg.sp.gov.br'),
        ('Emilly', 'vitoria correia de jesus', '2012-01-01', '2922052025', 7, 'aluno.1137277737@educacaopg.sp.gov.br'),
        ('Daniel', 'N/A', '2012-01-01', '3022052025', 7, 'aluno.1140117142@educacaopg.sp.gov.br'),
        ('Gabriel', 'luiz silva canavarros dos santos', '2012-01-01', '3122052025', 7, 'aluno.1138242901@educacaopg.sp.gov.br'),
        ('Sophya', 'dias', '2012-01-01', '3222052025', 7, 'aluno.1148743716@educacaopg.sp.gov.br'),
        ('Gabryel', 'pavão', '2012-01-01', '3322052025', 7, 'aluno.1139040637@educacaopg.sp.gov.br'),
        ('Pamela', 'silva de matos', '2012-01-01', '3422052025', 7, 'aluno.1208034352@educacaopg.sp.gov.br'),
        ('Kemilly', 'barbosa oliveira', '2012-01-01', '3522052025', 7, 'aluno.1164824818@educacaopg.sp.gov.br'),
        ('Sophia', 'beatrizz simoes santana', '2012-01-01', '3622052025', 7, 'aluno.1119849251@educacaopg.sp.gov.br'),
        ('Pedro', 'henrique', '2012-01-01', '3722052025', 7, 'aluno.1134251786@educacaopg.sp.gov.br'),
        ('Breno', 'brito dos santos', '2012-01-01', '3822052025', 7, 'aluno.1139055999@educacaopg.sp.gov.br'),
        ('Luan', 'N/A', '2012-01-01', '3922052025', 7, 'aluno.1122691245@educacaopg.sp.gov.br'),
        ('Lavinia', 'sousa nunes', '2012-01-01', '4022052025', 7, 'aluno.1218924421@educacaopg.sp.gov.br'),
        ('Milena', 'jesus de medeiros', '2012-01-01', '4122052025', 7, 'aluno.1131215990@educacaopg.sp.gov.br'),
        ('Sarah', 'samela inacio da silva', '2012-01-01', '4222052025', 7, 'aluno.1145815418@educacaopg.sp.gov.br'),
        ('Heloisa', 'ribeiro pwixinho', '2012-01-01', '4322052025', 7, 'aluno.1130997704@educacaopg.sp.gov.br'),
        ('Miguel', 'de oliveira pagani', '2012-01-01', '4422052025', 8, 'aluno.115838306x@educacaopg.sp.gov.br'),
        ('Gustavo', 'N/A', '2012-01-01', '4522052025', 8, 'aluno.1148593202@educacaopg.sp.gov.br'),
        ('Julia', 'pereira neris', '2012-01-01', '4622052025', 8, 'aluno.1159784231@educacaopg.sp.gov.br'),
        ('Davi', 'vieira patrício dos santos', '2012-01-01', '4722052025', 8, 'aluno.1142775690@educacaopg.sp.gov.br'),
        ('Yuri', 'melo', '2012-01-01', '4822052025', 8, 'aluno.1136918772@educacaopg.sp.gov.br'),
        ('Emilly', 'isabelly', '2012-01-01', '4922052025', 8, 'aluno.1139051787@educacaopg.sp.gov.br'),
        ('Maria', 'valentina de almeida lopes', '2012-01-01', '5022052025', 8, 'aluno.1151561848@educacaopg.sp.gov.br'),
        ('Gabriel', 'conceição dos santos', '2012-01-01', '5122052025', 8, 'aluno.1137548514@educacaopg.sp.gov.br'),
        ('Isabella', 'ramos', '2012-01-01', '5222052025', 8, 'aluno.120380829x@educacaopg.sp.gov.br'),
        ('Luiz', 'carlos do santos', '2012-01-01', '5322052025', 8, 'aluno.1146589244@educacaopg.sp.gov.br'),
        ('Gabriel', 'fernandes pedroso', '2012-01-01', '5422052025', 8, 'aluno.1139055434@educacaopg.sp.gov.br'),
        ('Yasmin', 'maria', '2012-01-01', '5522052025', 8, 'aluno.1133964138@educacaopg.sp.gov.br'),
        ('Adryan', 'kaique pereira de andrade', '2012-01-01', '5622052025', 8, 'aluno.1204918478@educacaopg.sp.gov.br'),
        ('Manuella', 'vitoria rocha de souza', '2012-01-01', '5722052025', 8, 'aluno.1164350857@educacaopg.sp.gov.br'),
        ('Erick', 'vinicius ezidio fonseca', '2012-01-01', '5822052025', 8, 'aluno.1157506672@educacaopg.sp.gov.br'),
        ('Sophya', 'ellen', '2012-01-01', '5922052025', 8, 'aluno.1153950182@educacaopg.sp.gov.br'),
        ('Ana', 'julia', '2012-01-01', '6022052025', 8, 'aluno.1127756126@educacaopg.sp.gov.br'),
        ('Lucero', 'brisa', '2012-01-01', '6122052025', 8, 'aluno.1163113293@educacaopg.sp.gov.br'),
        ('Khemilly', 'fernanda sousa de jesus', '2012-01-01', '6222052025', 8, 'aluno.1160601756@educacaopg.sp.gov.br'),
        ('Evellyn', 'carvalho licá da silva', '2012-01-01', '6322052025', 8, 'aluno.114239069x@educacaopg.sp.gov.br'),
        ('Isabela', 'flores', '2012-01-01', '6422052025', 8, 'aluno.1131635863@educacaopg.sp.gov.br'),
        ('Rayssa', 'N/A', '2012-01-01', '6522052025', 9, 'aluno.1164939397@educacaopg.sp.gov.br'),
        ('Yasmim', 'gabrielle da silva costa', '2012-01-01', '6622052025', 9, 'aluno.1156645591@educacaopg.sp.gov.br'),
        ('Richard', 'alexandre guimarães de oliveira', '2012-01-01', '6722052025', 9, 'aluno.1145740108@educacaopg.sp.gov.br'),
        ('Andre', 'felipe', '2012-01-01', '6822052025', 9, 'aluno.1140408859@educacaopg.sp.gov.br'),
        ('Enzo', 'davy santos de araujo', '2012-01-01', '6922052025', 9, 'aluno.1150067998@educacaopg.sp.gov.br'),
        ('Miguel', 'hungria', '2012-01-01', '7022052025', 9, 'aluno.1150067901@educacaopg.sp.gov.br'),
        ('Kleber', 'ricardo luiz fernandes', '2012-01-01', '7122052025', 9, 'aluno.1150929583@educacaopg.sp.gov.br'),
        ('Murilo', 'N/A', '2012-01-01', '7222052025', 9, 'aluno.1129662238@educacaopg.sp.gov.br'),
        ('Bianca', 'cristina da silva lima', '2012-01-01', '7322052025', 9, 'aluno.1158729704@educacaopg.sp.gov.br'),
        ('Felipe', 'morgado', '2012-01-01', '7422052025', 9, 'aluno.1166934081@educacaopg.sp.gov.br'),
        ('Lucas', 'roberto dos santos balardao', '2012-01-01', '7522052025', 9, 'aluno.116312820x@educacaopg.sp.gov.br'),
        ('Bernardo', 'rezende de sousa', '2012-01-01', '7622052025', 9, 'aluno.1140000755@educacaopg.sp.gov.br'),
        ('Pedro', 'henrique freitas de souza', '2012-01-01', '7722052025', 9, 'aluno.1129349147@educacaopg.sp.gov.br'),
        ('Manuela', 'fortunato de lima', '2012-01-01', '7822052025', 9, 'aluno.1139561625@educacaopg.sp.gov.br'),
        ('Vitoria', 'sofia dias de oliveira', '2012-01-01', '7922052025', 9, 'aluno.1139041253@educacaopg.sp.gov.br'),
        ('Sophia', 'N/A', '2012-01-01', '8022052025', 9, 'aluno.1139041356@educacaopg.sp.gov.br'),
        ('Antonio', 'everton freitas de oliveira', '2012-01-01', '8122052025', 9, 'aluno.1144921661@educacaopg.sp.gov.br'),
        ('Luiz', 'miguel', '2012-01-01', '8222052025', 9, 'aluno.1146492625@educacaopg.sp.gov.br'),
        ('Amanda', 'barros de oliveira', '2012-01-01', '8322052025', 9, 'aluno.1158248283@educacaopg.sp.gov.br'),
        ('Emanuele', 'cristina de araujo silva', '2012-01-01', '8422052025', 9, 'aluno.1154743147@educacaopg.sp.gov.br'),
        ('Maria', 'eduarda santana cruz', '2012-01-01', '8522052025', 9, 'aluno.1129666864@educacaopg.sp.gov.br'),
        ('Maria', 'luiza santana rocha', '2012-01-01', '8622052025', 9, 'aluno.1203633014@educacaopg.sp.gov.br'),
        ('Luana', 'pereira dos santos silveira', '2012-01-01', '8722052025', 9, 'aluno.1203711657@educacaopg.sp.gov.br');

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

    SQL;

    $pdo->exec($sql);

    echo "✅ Dados inseridos com sucesso!";
    } catch (PDOException $e) {
        echo "❌ Erro ao inserir: " . $e->getMessage();
    }
?>
