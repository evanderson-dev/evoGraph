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

    // Buscar dados dos alunos
    $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula 
            FROM alunos a 
            JOIN turmas t ON a.turma_id = t.id 
            WHERE t.id = ? AND t.professor_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }
    $stmt->bind_param("ii", $turma_id, $funcionario_id);
    if (!$stmt->execute()) {
        die("Erro ao executar a query: " . $stmt->error);
    }
    $result = $stmt->get_result();

    $html = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Formatar datas para DD/MM/YYYY
            $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
            $data_matricula = $row["data_matricula"] ? date("d/m/Y", strtotime($row["data_matricula"])) : 'N/A';

            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_nascimento) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_matricula) . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='4'>Nenhum aluno cadastrado nesta turma.</td></tr>";
    }

    $stmt->close();
    echo $html;
} else {
    echo "<tr><td colspan='4'>Requisição inválida.</td></tr>";
}

$conn->close();
?>