<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor" && $_SESSION["cargo"] !== "Administrador")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["turma_id"])) {
    $turma_id = trim($_POST["turma_id"] ?? '');

    if (empty($turma_id)) {
        echo json_encode(['success' => false, 'message' => 'ID da turma não fornecido.']);
        exit;
    }

    try {
        $sql_count = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $turma_id);
        $stmt_count->execute();
        $quantidade = $stmt_count->get_result()->fetch_assoc()['quantidade'];
        $stmt_count->close();

        if ($quantidade > 0) {
            echo json_encode(['success' => false, 'message' => 'Não é possível excluir uma turma com alunos matriculados.']);
            exit;
        }

        $deleteSql = "DELETE FROM turmas WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $turma_id);

        if ($deleteStmt->execute()) {
            $deleteStmt->close();

            $sql = "SELECT t.id, t.nome, t.ano, f.nome AS professor_nome, f.sobrenome 
                    FROM turmas t 
                    LEFT JOIN funcionarios f ON t.professor_id = f.id";
            $result = $conn->query($sql);
            if ($result === false) {
                throw new Exception('Erro na consulta de turmas: ' . $conn->error);
            }

            $turmas_html = '';
            while ($row = $result->fetch_assoc()) {
                $sql_count = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
                $stmt_count = $conn->prepare($sql_count);
                $stmt_count->bind_param("i", $row['id']);
                $stmt_count->execute();
                $count_result = $stmt_count->get_result();
                $quantidade = $count_result->fetch_assoc()['quantidade'];
                $stmt_count->close();

                $turmas_html .= "<div class='box-turmas-single' data-turma-id='{$row['id']}'>";
                $turmas_html .= "<h3>{$row['nome']}</h3>";
                $turmas_html .= "<p>Professor: " . ($row['professor_nome'] ? htmlspecialchars($row['professor_nome'] . " " . $row['sobrenome']) : "Sem professor") . "</p>";
                $turmas_html .= "<p>{$quantidade} alunos</p>";
                $turmas_html .= "<button class='action-btn delete-btn' title='Excluir Turma' onclick='showDeleteTurmaModal({$row['id']})'>";
                $turmas_html .= "<i class='fa-solid fa-trash'></i>";
                $turmas_html .= "</button>";
                $turmas_html .= "</div>";
            }

            $total_result = $conn->query("SELECT COUNT(*) as total_turmas FROM turmas");
            if ($total_result === false) {
                throw new Exception('Erro ao contar turmas: ' . $conn->error);
            }
            $total_turmas = $total_result->fetch_assoc()['total_turmas'];

            echo json_encode([
                'success' => true,
                'message' => 'Turma excluída com sucesso!',
                'turmas_html' => $turmas_html,
                'total_turmas' => $total_turmas
            ]);
        } else {
            throw new Exception('Erro ao excluir turma: ' . $deleteStmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
$conn->close();
?>