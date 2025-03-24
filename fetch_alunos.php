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
    $funcionario_id = $_SESSION["funcionario_id"];

    // Definir a query com base no cargo
    if ($cargo === "Professor") {
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula 
                FROM alunos a 
                JOIN turmas t ON a.turma_id = t.id 
                WHERE t.id = ? AND t.professor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $turma_id, $funcionario_id);
        $colspan = 4;
    } else { // Diretor ou Coordenador
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula, a.nome_pai, a.nome_mae 
                FROM alunos a 
                WHERE a.turma_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $turma_id);
        $colspan = 7;
    }

    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }
    if (!$stmt->execute()) {
        die("Erro ao executar a query: " . $stmt->error);
    }
    $result = $stmt->get_result();

    $html = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
            $data_matricula = $row["data_matricula"] ? date("d/m/Y", strtotime($row["data_matricula"])) : 'N/A';

            $html .= "<tr class='aluno-row' data-matricula='" . htmlspecialchars($row['matricula']) . "' data-nome='" . htmlspecialchars($row['nome'] . " " . $row['sobrenome']) . "' data-nascimento='" . htmlspecialchars($data_nascimento) . "' data-matricula='" . htmlspecialchars($data_matricula) . "' data-pai='" . htmlspecialchars($row['nome_pai'] ?? 'N/A') . "' data-mae='" . htmlspecialchars($row['nome_mae'] ?? 'N/A') . "'>";
            $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_nascimento) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_matricula) . "</td>";
            if ($cargo !== "Professor") {
                $html .= "<td>" . htmlspecialchars($row["nome_pai"] ?? 'N/A') . "</td>";
                $html .= "<td>" . htmlspecialchars($row["nome_mae"] ?? 'N/A') . "</td>";
                $html .= "<td>";
                $html .= "<button class='action-btn edit-btn' title='Editar' onclick=\"editAluno('" . htmlspecialchars($row['matricula']) . "')\"><i class='fa-solid fa-pen-to-square'></i></button>";
                $html .= "<button class='action-btn delete-btn' title='Excluir' onclick=\"showDeleteModal('" . htmlspecialchars($row['matricula']) . "')\"><i class='fa-solid fa-trash'></i></button>";
                $html .= "</td>";
            }
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='$colspan'>Nenhum aluno cadastrado nesta turma.</td></tr>";
    }

    $stmt->close();
    echo $html;
} else {
    echo "<tr><td colspan='7'>Requisição inválida.</td></tr>";
}

$conn->close();
?>