<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

$cargo = $_SESSION["cargo"];
$funcionario_id = $_SESSION["funcionario_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = isset($_POST['turma_id']) ? $_POST['turma_id'] : null;
    $matricula = isset($_POST['matricula']) ? $_POST['matricula'] : null;
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    // Ação para buscar dados de um aluno específico (para o modal de edição e detalhes)
    if ($action === 'fetch_aluno' && $matricula) {
        $context = isset($_POST['context']) ? $_POST['context'] : 'details'; // 'details' por padrão
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.data_matricula, a.nome_pai, a.nome_mae, a.turma_id, t.nome AS turma_nome 
                FROM alunos a 
                LEFT JOIN turmas t ON a.turma_id = t.id 
                WHERE a.matricula = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($context === 'edit') {
                // Formato para input type="date" (yyyy-mm-dd)
                $row['data_nascimento'] = $row['data_nascimento'] ?: 'N/A';
                $row['data_matricula'] = $row['data_matricula'] ?: 'N/A';
            } else {
                // Formato para exibição (dd/mm/yyyy)
                $row['data_nascimento'] = $row['data_nascimento'] ? date("d/m/Y", strtotime($row['data_nascimento'])) : 'N/A';
                $row['data_matricula'] = $row['data_matricula'] ? date("d/m/Y", strtotime($row['data_matricula'])) : 'N/A';
            }
            echo json_encode(['success' => true, 'aluno' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aluno não encontrado.']);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    // Ação de atualização (edição do aluno)
    if ($action === 'update' && $matricula && ($cargo === "Diretor" || $cargo === "Coordenador")) {
        $nome = $_POST['nome'];
        $sobrenome = $_POST['sobrenome'];
        $data_nascimento = $_POST['data_nascimento'];
        $data_matricula = isset($_POST['data_matricula_hidden']) ? $_POST['data_matricula_hidden'] : null;
        $nome_pai = $_POST['nome_pai'] ?: null;
        $nome_mae = $_POST['nome_mae'] ?: null;
        $novo_turma_id = $_POST['turma_id'];

        if ($data_matricula === null) {
            $sql = "SELECT data_matricula FROM alunos WHERE matricula = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $matricula);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $data_matricula = $row['data_matricula'];
            }
            $stmt->close();
        }

        $sql = "UPDATE alunos SET nome = ?, sobrenome = ?, data_nascimento = ?, data_matricula = ?, nome_pai = ?, nome_mae = ?, turma_id = ? WHERE matricula = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssis", $nome, $sobrenome, $data_nascimento, $data_matricula, $nome_pai, $nome_mae, $novo_turma_id, $matricula);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar aluno: ' . $conn->error]);
            $conn->close();
            exit;
        }
    } elseif ($action === 'update' && $cargo !== "Diretor" && $cargo !== "Coordenador") {
        echo json_encode(['success' => false, 'message' => 'Ação não permitida para este cargo.']);
        $conn->close();
        exit;
    }

    // Exclusão (apenas para Diretor)
    if ($matricula && $action === "delete" && $cargo === "Diretor") {
        $sql = "DELETE FROM alunos WHERE matricula = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $matricula);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir aluno: ' . $conn->error]);
            $conn->close();
            exit;
        }
    } elseif ($matricula && $action === "delete" && $cargo !== "Diretor") {
        echo json_encode(['success' => false, 'message' => 'Ação não permitida para este cargo.']);
        $conn->close();
        exit;
    }

    // Após exclusão ou atualização, ou apenas carregamento da tabela
    if ($turma_id) {
        // Buscar o total de alunos (apenas Diretor vê isso)
        $total_alunos = null;
        if ($cargo === "Diretor") {
            $sql = "SELECT COUNT(*) as total_alunos FROM alunos";
            $total_alunos = $conn->query($sql)->fetch_assoc()['total_alunos'];
        }

        // Buscar a quantidade de alunos na turma
        $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $turma_id);
        $stmt->execute();
        $quantidade = $stmt->get_result()->fetch_assoc()['quantidade'];
        $stmt->close();

        // Buscar os dados da tabela de alunos da turma (sem data_matricula)
        if ($cargo === "Professor") {
            $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.turma_id, t.nome AS turma_nome, f.nome AS professor_nome, f.sobrenome AS professor_sobrenome 
                    FROM alunos a 
                    JOIN turmas t ON a.turma_id = t.id 
                    LEFT JOIN funcionarios f ON t.professor_id = f.id 
                    WHERE t.id = ? AND t.professor_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $turma_id, $funcionario_id);
        } else { // Diretor ou Coordenador
            $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.turma_id, t.nome AS turma_nome, f.nome AS professor_nome, f.sobrenome AS professor_sobrenome 
                    FROM alunos a 
                    JOIN turmas t ON a.turma_id = t.id 
                    LEFT JOIN funcionarios f ON t.professor_id = f.id 
                    WHERE a.turma_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $turma_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $html = '';
        $colspan = ($cargo === "Professor") ? 3 : 4; // Ajustado: 3 colunas para Professor, 4 para Diretor (com ações)
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
                $professor = $row['professor_nome'] ? htmlspecialchars($row['professor_nome'] . " " . $row['sobrenome']) : 'Sem professor';

                $html .= "<tr class='aluno-row' data-matricula='" . htmlspecialchars($row['matricula']) . "' data-turma-id='" . htmlspecialchars($row['turma_id']) . "' data-nome='" . htmlspecialchars($row['nome'] . " " . $row['sobrenome']) . "' data-nascimento='" . htmlspecialchars($data_nascimento) . "' data-pai='" . htmlspecialchars($row['nome_pai'] ?? 'N/A') . "' data-mae='" . htmlspecialchars($row['nome_mae'] ?? 'N/A') . "' data-turma-nome='" . htmlspecialchars($row['turma_nome']) . "' data-professor='" . $professor . "'>";
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
            'message' => $matricula ? ($action === "delete" ? 'Aluno excluído com sucesso!' : 'Aluno atualizado com sucesso!') : 'Dados carregados com sucesso.',
            'quantidade_turma' => $quantidade,
            'tabela_alunos' => $html
        ];
        if ($cargo === "Diretor") {
            $response['total_alunos'] = $total_alunos;
        }
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Turma não especificada.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}

$conn->close();
?>