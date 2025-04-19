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
INSERT INTO alunos (nome, sobrenome, data_nascimento, matricula, turma_id, email)
VALUES 
('Ariane', 'da silva dos santos', '2012-01-01', '119042025', 5, 'aluno.1228874669@educacaopg.sp.gov.br'),
('Bruno', 'ribeiro dos santos', '2012-01-01', '219042025', 5, 'aluno.1131563396@educacaopg.sp.gov.br'),
('Cristian', 'N/A', '2012-01-01', '319042025', 5, 'aluno.120011808x@educacaopg.sp.gov.br'),
('Emillyserra', 'N/A', '2012-01-01', '419042025', 5, 'aluno.115037925x@educacaopg.sp.gov.br'),
('Enzo', 'lucca machado nunes', '2012-01-01', '519042025', 5, 'aluno.1163187628@educacaopg.sp.gov.br'),
('Felipe', 'Francisco Rodrigues Souza', '2012-01-01', '619042025', 5, 'aluno.1137547972@educacaopg.sp.gov.br'),
('Kaique', 'N/A', '2012-01-01', '719042025', 5, 'aluno.115960213x@educacaopg.sp.gov.br'),
('Leonardo', 'N/A', '2012-01-01', '819042025', 5, 'aluno.1137549269@educacaopg.sp.gov.br'),
('Lorrainy', 'gabrielly caetano de jesus', '2012-01-01', '919042025', 5, 'aluno.120406071x@educacaopg.sp.gov.br'),
('Lucas', 'cavalacante', '2012-01-01', '1019042025', 5, 'aluno.1150025177@educacaopg.sp.gov.br'),
('Luiza', 'evangelista dos santos.', '2012-01-01', '1119042025', 5, 'aluno.113915977x@educacaopg.sp.gov.br'),
('Maria', 'fernanda de souza cardoso', '2012-01-01', '1219042025', 5, 'aluno.1150848662@educacaopg.sp.gov.br'),
('Mariana', 'N/A', '2012-01-01', '1319042025', 5, 'aluno.1129348155@educacaopg.sp.gov.br'),
('Miguel', 'Costa', '2012-01-01', '1419042025', 5, 'aluno.1135641912@educacaopg.sp.gov.br'),
('Nickolly', 'silverio', '2012-01-01', '1519042025', 5, 'aluno.1119927444@educacaopg.sp.gov.br'),
('Paulo', 'Cesar', '2012-01-01', '1619042025', 5, 'aluno.1156645773@educacaopg.sp.gov.br'),
('Rebeca', 'ramalho neves linhares', '2012-01-01', '1719042025', 5, 'aluno.1135562216@educacaopg.sp.gov.br'),
('Ruan', 'barbosa', '2012-01-01', '1819042025', 5, 'aluno.1131213907@educacaopg.sp.gov.br'),
('Sophia', 'de Souza Gomes Reis', '2012-01-01', '1919042025', 5, 'aluno.1156641342@educacaopg.sp.gov.br'),
('Augusto', 'renato', '2012-01-01', '2019042025', 6, 'aluno.1141508345@educacaopg.sp.gov.br'),
('Bruna', 'N/A', '2012-01-01', '2119042025', 6, 'aluno.1164942566@educacaopg.sp.gov.br'),
('Bruno', 'dias', '2012-01-01', '2219042025', 6, 'aluno.1144868506@educacaopg.sp.gov.br'),
('Eloise', 'vasconcellos alves', '2012-01-01', '2319042025', 6, 'aluno.1141827906@educacaopg.sp.gov.br'),
('Fernanda', 'de arruda silva oier', '2012-01-01', '2419042025', 6, 'aluno.1148652267@educacaopg.sp.gov.br'),
('Gabrielly', 'Vitoria Alves De Oliveira', '2012-01-01', '2519042025', 6, 'aluno.1126178524@educacaopg.sp.gov.br'),
('João', 'alessandro nascimento dos santos', '2012-01-01', '2619042025', 6, 'aluno.1156640180@educacaopg.sp.gov.br'),
('Julia', 'Kauany Alves Bueno', '2012-01-01', '2719042025', 6, 'aluno.1139040273@educacaopg.sp.gov.br'),
('Julia', 'nunes', '2012-01-01', '2819042025', 6, 'aluno.1137003595@educacaopg.sp.gov.br'),
('Kaua', 'thiago', '2012-01-01', '2919042025', 6, 'aluno.1139155404@educacaopg.sp.gov.br'),
('Keven', 'N/A', '2012-01-01', '3019042025', 6, 'aluno.1230733607@educacaopg.sp.gov.br'),
('Levi', 'rubem leal da silva', '2012-01-01', '3119042025', 6, 'aluno.1258298768@educacaopg.sp.gov.br'),
('Livia', 'dias rodrigues', '2012-01-01', '3219042025', 6, 'aluno.1146486996@educacaopg.sp.gov.br'),
('Luiz', 'gustavo', '2012-01-01', '3319042025', 6, 'aluno.1141409306@educacaopg.sp.gov.br'),
('Melissa', 'Gabrielly lino do Nascimento', '2012-01-01', '3419042025', 6, 'aluno.1158000352@educacaopg.sp.gov.br'),
('Nicolly', 'N/A', '2012-01-01', '3519042025', 6, 'aluno.1139574425@educacaopg.sp.gov.br'),
('Pedro', 'henrique da silva faria', '2012-01-01', '3619042025', 6, 'aluno.1123874979@educacaopg.sp.gov.br'),
('Richard', 'rodrigues da Silva', '2012-01-01', '3719042025', 6, 'aluno.1137551136@educacaopg.sp.gov.br'),
('Ryan', 'Pablo Guimarães Coata', '2012-01-01', '3819042025', 6, 'aluno.1136890646@educacaopg.sp.gov.br'),
('Samuel', 'Francisco Costa de Souza', '2012-01-01', '3919042025', 6, 'aluno.1138878777@educacaopg.sp.gov.br'),
('Thiago', 'torres11', '2012-01-01', '4019042025', 6, 'aluno.1156676058@educacaopg.sp.gov.br'),
('Vitoria', 'silva oliveira', '2012-01-01', '4119042025', 6, 'aluno.1129513002@educacaopg.sp.gov.br'),
('Wallace', 'N/A', '2012-01-01', '4219042025', 6, 'aluno.1139158570@educacaopg.sp.gov.br'),
('Wilson', 'N/A', '2012-01-01', '4319042025', 6, 'aluno.1129663784@educacaopg.sp.gov.br'),
('Yara', 'N/A', '2012-01-01', '4419042025', 6, 'aluno.1166730955@educacaopg.sp.gov.br');
SQL;

    $pdo->exec($sql);
    echo "✅ Alunos inseridos com sucesso!";
} catch (PDOException $e) {
    echo "❌ Erro ao inserir: " . $e->getMessage();
}
?>
