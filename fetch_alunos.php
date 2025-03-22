<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['turma_id'])) {
    $turma_id = $_POST['turma_id'];
    $funcionario_id = $_SESSION["funcionario_id"];

    // Verificar se a turma pertence ao professor logado (segurança)
    $sql = "SELECT a.nome, a.sobrenome, a.matricula 
            FROM alunos a 
            JOIN turmas t ON a.turma_id = t.id 
            WHERE t.id = ? AND t.professor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $turma_id, $funcionario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='2'>Nenhum aluno cadastrado nesta turma.</td></tr>";
    }

    $stmt->close();
    echo $html;
}

$conn->close();
?>