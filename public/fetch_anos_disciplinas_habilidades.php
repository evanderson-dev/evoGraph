<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connection.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'anos_disciplinas') {
        $anos_escolares = [];
        $disciplinas = [];

        // Fetch anos escolares
        $result = $conn->query("SELECT id, nome FROM anos_escolares ORDER BY ordem, nome");
        while ($row = $result->fetch_assoc()) {
            $anos_escolares[] = $row;
        }

        // Fetch disciplinas
        $result = $conn->query("SELECT id, nome FROM disciplinas ORDER BY nome");
        while ($row = $result->fetch_assoc()) {
            $disciplinas[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'anos_escolares' => $anos_escolares,
                'disciplinas' => $disciplinas
            ]
        ]);
    } elseif ($action === 'disciplinas') {
        $ano_id = filter_var($_GET['ano_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$ano_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID do ano inválido.']);
            exit;
        }
        $disciplinas = [];
        $query = "SELECT DISTINCT d.id, d.nome 
                  FROM disciplinas d 
                  INNER JOIN habilidades_bncc h ON d.id = h.disciplina_id 
                  WHERE h.ano_id = ? 
                  ORDER BY d.nome";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $ano_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $disciplinas[] = $row;
        }
        $stmt->close();
        echo json_encode(['status' => 'success', 'data' => $disciplinas]);
    } elseif ($action === 'habilidades') {
        $ano_id = filter_var($_GET['ano_id'] ?? '', FILTER_VALIDATE_INT);
        $disciplina_id = filter_var($_GET['disciplina_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$ano_id || !$disciplina_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID do ano ou disciplina inválido.']);
            exit;
        }
        $habilidades = [];
        $query = "SELECT codigo, descricao 
                  FROM habilidades_bncc 
                  WHERE ano_id = ? AND disciplina_id = ? 
                  ORDER BY codigo";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $ano_id, $disciplina_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $habilidades[] = $row;
        }
        $stmt->close();
        echo json_encode(['status' => 'success', 'data' => $habilidades]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ação inválida.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}

$conn->close();
?>