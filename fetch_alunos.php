<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['turma_id'])) {
    $turma_id = $_POST['turma_id'];
    $cargo = $_SESSION["cargo"];

    // Query ajustada para o Diretor (sem restrição de professor_id)
    $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula, a.nome_pai, a.nome_mae 
            FROM alunos a 
            WHERE a.turma_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }
    $stmt->bind_param("i", $turma_id);
    if (!$stmt->execute()) {
        die("Erro ao executar a query: " . $stmt->error);
    }
    $result = $stmt->get_result();

    $html = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
            $data_matricula = $row["data_matricula"] ? date("d/m/Y", strtotime($row["data_matricula"])) : 'N/A';

            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_nascimento) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_matricula) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["nome_pai"] ?? 'N/A') . "</td>";
            $html .= "<td>" . htmlspecialchars($row["nome_mae"] ?? 'N/A') . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='6'>Nenhum aluno cadastrado nesta turma.</td></tr>";
    }

    $stmt->close();
    echo $html;
} else {
    echo "<tr><td colspan='6'>Requisição inválida.</td></tr>";
}

$conn->close();
?>