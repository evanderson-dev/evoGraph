<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["cargo"] !== "Diretor") {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['matricula']) && isset($_POST['turma_id'])) {
    $matricula = $_POST['matricula'];
    $turma_id = $_POST['turma_id'];

    // Excluir o aluno
    $sql = "DELETE FROM alunos WHERE matricula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricula);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        // Buscar o total de alunos
        $sql = "SELECT COUNT(*) as total_alunos FROM alunos";
        $total_alunos = $conn->query($sql)->fetch_assoc()['total_alunos'];

        // Buscar a quantidade de alunos na turma
        $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $turma_id);
        $stmt->execute();
        $quantidade = $stmt->get_result()->fetch_assoc()['quantidade'];
        $stmt->close();

        // Buscar os dados da tabela de alunos da turma
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula, a.nome_pai, a.nome_mae 
                FROM alunos a 
                WHERE a.turma_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $turma_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $html = '';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
                $data_matricula = $row["data_matricula"] ? date("d/m/Y", strtotime($row["data_matricula"])) : 'N/A';
                $data_matricula_com_hora = $row["data_matricula"] ? date("d/m/Y H:i", strtotime($row["data_matricula"])) : 'N/A';

                $html .= "<tr class='aluno-row' data-matricula='" . htmlspecialchars($row['matricula']) . "' data-turma-id='" . htmlspecialchars($turma_id) . "' data-nome='" . htmlspecialchars($row['nome'] . " " . $row['sobrenome']) . "' data-nascimento='" . htmlspecialchars($data_nascimento) . "' data-matricula-data='" . htmlspecialchars($data_matricula_com_hora) . "' data-pai='" . htmlspecialchars($row['nome_pai'] ?? 'N/A') . "' data-mae='" . htmlspecialchars($row['nome_mae'] ?? 'N/A') . "'>";
                $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
                $html .= "<td>" . htmlspecialchars($data_nascimento) . "</td>";
                $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
                $html .= "<td>" . htmlspecialchars($data_matricula) . "</td>";
                $html .= "<td>";
                $html .= "<button class='action-btn edit-btn' title='Editar' onclick=\"editAluno('" . htmlspecialchars($row['matricula']) . "')\"><i class='fa-solid fa-pen-to-square'></i></button>";
                $html .= "<button class='action-btn delete-btn' title='Excluir' onclick=\"showDeleteModal('" . htmlspecialchars($row['matricula']) . "', '" . htmlspecialchars($turma_id) . "')\"><i class='fa-solid fa-trash'></i></button>";
                $html .= "</td>";
                $html .= "</tr>";
            }
        } else {
            $html .= "<tr><td colspan='5'>Nenhum aluno cadastrado nesta turma.</td></tr>";
        }
        $stmt->close();

        // Retornar todos os dados em JSON
        echo json_encode([
            'success' => true,
            'message' => 'Aluno excluído com sucesso!',
            'total_alunos' => $total_alunos,
            'quantidade_turma' => $quantidade,
            'tabela_alunos' => $html
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir aluno: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}

$conn->close();
?>