<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova-turma"])) {
    $nome = trim($_POST["nome"] ?? '');
    $ano = trim($_POST["ano"] ?? '');
    $funcionario_id = trim($_POST["professor_id"] ?? '');

    if (empty($nome) || empty($ano) || empty($funcionario_id)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        $insertSql = "INSERT INTO turmas (nome, ano, professor_id) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sii", $nome, $ano, $funcionario_id);

        if ($insertStmt->execute()) {
            $insertStmt->close();

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
                'message' => 'Turma cadastrada com sucesso!',
                'turmas_html' => $turmas_html,
                'total_turmas' => $total_turmas
            ]);
        } else {
            throw new Exception('Erro ao cadastrar turma: ' . $insertStmt->error);
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