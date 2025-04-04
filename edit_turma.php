<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor" && $_SESSION["cargo"] !== "Administrador")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["turma_id"])) {
    $turma_id = (int)$_POST["turma_id"];
    $nome = trim($_POST["nome"] ?? '');
    $ano = trim($_POST["ano"] ?? '');
    $professor_id = trim($_POST["professor_id"] ?? '');

    if (empty($nome) || empty($ano) || empty($professor_id)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        $updateSql = "UPDATE turmas SET nome = ?, ano = ?, professor_id = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("siii", $nome, $ano, $professor_id, $turma_id);

        if ($updateStmt->execute()) {
            $updateStmt->close();

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
                $quantidade = $stmt_count->get_result()->fetch_assoc()['quantidade'];
                $stmt_count->close();

                $turmas_html .= "<div class='box-turmas-single' data-turma-id='{$row['id']}'>";
                $turmas_html .= "<h3>" . htmlspecialchars($row['nome']) . "</h3>";
                $turmas_html .= "<p>Professor: " . ($row['professor_nome'] ? htmlspecialchars($row['professor_nome'] . " " . $row['sobrenome']) : "Sem professor") . "</p>";
                $turmas_html .= "<p>{$quantidade} alunos</p>";
                if ($_SESSION["cargo"] === "Coordenador" || $_SESSION["cargo"] === "Diretor" || $_SESSION["cargo"] === "Administrador") {
                    $turmas_html .= "<button class='action-btn edit-btn' title='Editar Turma' onclick='showEditTurmaModal({$row['id']})'>";
                    $turmas_html .= "<i class='fa-solid fa-pen-to-square'></i>";
                    $turmas_html .= "</button>";
                    $turmas_html .= "<button class='action-btn delete-btn' title='Excluir Turma' onclick='showDeleteTurmaModal({$row['id']})'>";
                    $turmas_html .= "<i class='fa-solid fa-trash'></i>";
                    $turmas_html .= "</button>";
                }
                $turmas_html .= "</div>";
            }

            $total_result = $conn->query("SELECT COUNT(*) as total_turmas FROM turmas");
            if ($total_result === false) {
                throw new Exception('Erro ao contar turmas: ' . $conn->error);
            }
            $total_turmas = $total_result->fetch_assoc()['total_turmas'];

            echo json_encode([
                'success' => true,
                'message' => 'Turma editada com sucesso!',
                'turmas_html' => $turmas_html,
                'total_turmas' => $total_turmas
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception('Erro ao editar turma: ' . $updateStmt->error);
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