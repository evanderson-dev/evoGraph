<?php
// salvar_chamada.php (novo arquivo backend para salvar as presenças)
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['cargo'] !== 'Professor') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

$turma_id = filter_var($_POST['turma_id'] ?? '', FILTER_VALIDATE_INT);
$data = $_POST['data'] ?? date('Y-m-d');
$presencas_json = $_POST['presencas'] ?? '[]';

if (!$turma_id || !$data) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$presencas = json_decode($presencas_json, true);
if (!is_array($presencas) || empty($presencas)) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma presença para salvar.']);
    exit;
}

try {
    $conn->begin_transaction();

    foreach ($presencas as $p) {
        $matricula = $conn->real_escape_string($p['matricula']);
        $presente = $p['presente'] ? 1 : 0;

        // Verificar se já existe (UPSERT)
        $sql_check = "SELECT id FROM presencas WHERE aluno_matricula = ? AND turma_id = ? AND data = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sis", $matricula, $turma_id, $data);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Atualizar
            $sql = "UPDATE presencas SET presente = ? WHERE aluno_matricula = ? AND turma_id = ? AND data = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isis", $presente, $matricula, $turma_id, $data);
        } else {
            // Inserir
            $sql = "INSERT INTO presencas (aluno_matricula, turma_id, data, presente) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $matricula, $turma_id, $data, $presente);
        }

        if (!$stmt->execute()) {
            throw new Exception('Erro ao salvar presença para ' . $matricula . ': ' . $stmt->error);
        }
        $stmt->close();
        $stmt_check->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Chamada salva com sucesso.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>