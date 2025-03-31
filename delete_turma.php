<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
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

            // Incluir o HTML atualizado das turmas
            ob_start();
            include 'fetch_turmas_dashboard.php';
            $turmas_html = ob_get_clean();

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