<?php
session_start();

require_once 'db_connection.php';

$sql = "SELECT t.id, t.nome, t.ano, f.nome AS professor_nome, f.sobrenome 
        FROM turmas t 
        LEFT JOIN funcionarios f ON t.professor_id = f.id";
$result = $conn->query($sql);

$turmas_html = '';
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sql_count = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $row['id']);
        $stmt_count->execute();
        $quantidade = $stmt_count->get_result()->fetch_assoc()['quantidade'];
        $stmt_count->close();

        $turmas_html .= "<div class='box-turmas-single' data-turma-id='{$row['id']}'>";
        $turmas_html .= "<h3>{$row['nome']} ({$row['ano']})</h3>";
        $turmas_html .= "<p>Professor: " . ($row['professor_nome'] ? htmlspecialchars($row['professor_nome'] . " " . $row['sobrenome']) : "Sem professor") . "</p>";
        $turmas_html .= "<p>{$quantidade} alunos</p>";
        if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor") {
            $turmas_html .= "<button class='action-btn delete-btn' title='Excluir Turma' onclick='showDeleteTurmaModal({$row['id']})'>";
            $turmas_html .= "<i class='fa-solid fa-trash'></i>";
            $turmas_html .= "</button>";
        }
        $turmas_html .= "</div>";
    }
} else {
    $turmas_html = "<p>Nenhuma turma cadastrada.</p>";
}

echo $turmas_html;
$conn->close();
?>