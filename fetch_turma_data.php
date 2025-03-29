<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

$cargo = $_SESSION["cargo"];
$funcionario_id = $_SESSION["funcionario_id"];

header('Content-Type: application/json'); // Garantir que o cabeçalho seja JSON

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['turma_id'])) {
    $turma_id = $_POST['turma_id'];

    // Buscar o total de alunos (apenas Diretor)
    $total_alunos = null;
    if ($cargo === "Diretor") {
        $sql = "SELECT COUNT(*) as total_alunos FROM alunos";
        $result = $conn->query($sql);
        if ($result) {
            $total_alunos = $result->fetch_assoc()['total_alunos'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao contar total de alunos: ' . $conn->error]);
            exit;
        }
    }

    // Buscar a quantidade de alunos na turma
    $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da query: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $quantidade = $stmt->get_result()->fetch_assoc()['quantidade'];
    $stmt->close();

    // Buscar os dados da tabela de alunos da turma
    if ($cargo === "Professor") {
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.turma_id, t.nome AS turma_nome, f.nome AS professor_nome, f.sobrenome AS professor_sobrenome 
                FROM alunos a 
                JOIN turmas t ON a.turma_id = t.id 
                LEFT JOIN funcionarios f ON t.professor_id = f.id 
                WHERE t.id = ? AND t.professor_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Erro na preparação da query: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("ii", $turma_id, $funcionario_id);
    } else {
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.turma_id, t.nome AS turma_nome, f.nome AS professor_nome, f.sobrenome AS professor_sobrenome 
                FROM alunos a 
                JOIN turmas t ON a.turma_id = t.id 
                LEFT JOIN funcionarios f ON t.professor_id = f.id 
                WHERE a.turma_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Erro na preparação da query: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $turma_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '';
    $colspan = ($cargo === "Professor") ? 3 : 4;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
            $professor = $row['professor_nome'] ? htmlspecialchars($row['professor_nome'] . " " . $row['professor_sobrenome']) : 'Sem professor';

            $html .= "<tr class='aluno-row' data-matricula='" . htmlspecialchars($row['matricula']) . "' data-turma-id='" . htmlspecialchars($row['turma_id']) . "'>";
            $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_nascimento) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
            if ($cargo === "Diretor") {
                $html .= "<td>";
                $html .= "<button class='action-btn edit-btn' title='Editar' onclick=\"openEditModal('" . htmlspecialchars($row['matricula']) . "', '" . htmlspecialchars($turma_id) . "')\"><i class='fa-solid fa-pen-to-square'></i></button>";
                $html .= "<button class='action-btn delete-btn' title='Excluir' onclick=\"showDeleteModal('" . htmlspecialchars($row['matricula']) . "', '" . htmlspecialchars($turma_id) . "')\"><i class='fa-solid fa-trash'></i></button>";
                $html .= "</td>";
            }
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='$colspan'>Nenhum aluno cadastrado nesta turma.</td></tr>";
    }
    $stmt->close();

    $response = [
        'success' => true,
        'quantidade_turma' => $quantidade,
        'tabela_alunos' => $html
    ];
    if ($cargo === "Diretor") {
        $response['total_alunos'] = $total_alunos;
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Turma não especificada ou requisição inválida.']);
}

$conn->close();
?>