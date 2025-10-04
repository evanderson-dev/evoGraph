<?php
// fetch_presencas.php (nova API para buscar presenças por turma e data)
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

$allowed_cargos = ['Coordenador', 'Diretor', 'Administrador'];
if (!in_array($_SESSION["cargo"], $allowed_cargos)) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado para este cargo.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $turma_id = filter_var($_POST['turma_id'] ?? '', FILTER_VALIDATE_INT);
    $data = $_POST['data'] ?? date('Y-m-d');

    if (!$turma_id || !$data) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos.']);
        exit;
    }

    try {
        // Buscar presenças com JOIN para nome do aluno
        $query = "SELECT p.id, p.aluno_matricula, p.data, p.presente, a.nome AS nome_aluno, a.sobrenome
                  FROM presencas p
                  INNER JOIN alunos a ON p.aluno_matricula = a.matricula
                  WHERE p.turma_id = ? AND p.data = ?
                  ORDER BY a.nome";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $turma_id, $data);
        $stmt->execute();
        $result = $stmt->get_result();
        $presencas = [];
        while ($row = $result->fetch_assoc()) {
            $presencas[] = [
                'id' => $row['id'],
                'matricula' => $row['aluno_matricula'],
                'data' => $row['data'],
                'presente' => (bool)$row['presente'],
                'nome_aluno' => $row['nome_aluno'] . ' ' . $row['sobrenome']
            ];
        }
        $stmt->close();

        echo json_encode(['success' => true, 'presencas' => $presencas]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar presenças: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}

$conn->close();
?>