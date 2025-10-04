<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';
require_once 'utils.php'; // Para funções de fetch_turma_data.php

$cargo = $_SESSION["cargo"];
$funcionario_id = $_SESSION["funcionario_id"];

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = isset($_POST['turma_id']) ? (int)$_POST['turma_id'] : null;
    $professor_id = isset($_POST['professor_id']) ? (int)$_POST['professor_id'] : null;
    $action = $_POST['action'] ?? '';

    if ($action === 'list' && $professor_id) {
        // Nova ação: Listar turmas do professor
        $sql = "SELECT id, nome, ano FROM turmas WHERE professor_id = ? ORDER BY ano, nome";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $turmas = [];
        while ($row = $result->fetch_assoc()) {
            $turmas[] = $row;
        }
        $stmt->close();
        echo json_encode(['success' => true, 'turmas' => $turmas]);
        exit;
    } elseif ($action === 'alunos' && $turma_id) {
        // Nova ação: Carregar alunos e presenças da turma para uma data específica
        $data = $_POST['data'] ?? date('Y-m-d');

        // Buscar alunos
        $sql = "SELECT a.id, a.nome, a.sobrenome, a.matricula FROM alunos a WHERE a.turma_id = ? ORDER BY a.nome";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $turma_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $alunos = [];
        while ($row = $result->fetch_assoc()) {
            $alunos[] = $row;
        }
        $stmt->close();

        // Buscar presenças existentes para a data
        $presencas = [];
        $sql = "SELECT aluno_matricula, presente FROM presencas WHERE turma_id = ? AND data = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $turma_id, $data);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $presencas[$row['aluno_matricula']] = (bool)$row['presente'];
        }
        $stmt->close();

        echo json_encode(['success' => true, 'alunos' => $alunos, 'presencas' => $presencas]);
        exit;
    } elseif ($turma_id && $action === 'edit' && ($cargo === "Coordenador" || $cargo === "Diretor" || $cargo === "Administrador")) {
        // Substitui fetch_turma.php
        $sql = "SELECT t.id, t.nome, t.ano, t.professor_id 
                FROM turmas t 
                WHERE t.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $turma_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $turma = $result->fetch_assoc();
            echo json_encode(['success' => true, 'turma' => $turma]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Turma não encontrada.']);
        }
        $stmt->close();
    } elseif ($turma_id && $action === 'details') {
        // Substitui fetch_turma_data.php
        $total_alunos = getTotalAlunos($conn, $cargo);
        $quantidade_turma = getQuantidadeTurma($conn, $turma_id);
        $tabela_alunos = generateTabelaAlunos($conn, $turma_id, $cargo, $funcionario_id);

        if ($quantidade_turma === null || $tabela_alunos === null) {
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados da turma.']);
        } else {
            $response = [
                'success' => true,
                'quantidade_turma' => $quantidade_turma,
                'tabela_alunos' => $tabela_alunos
            ];
            if ($cargo === "Diretor" || $cargo === "Administrador") {
                $response['total_alunos'] = $total_alunos;
            }
            echo json_encode($response);
        }
    } elseif ($action === 'all_turmas') {
        // Nova ação: Listar todas as turmas (para cargos superiores)
        if (!in_array($cargo, ['Coordenador', 'Diretor', 'Administrador'])) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit;
        }
        $sql = "SELECT id, nome, ano FROM turmas ORDER BY ano, nome";
        $result = $conn->query($sql);
        $turmas = [];
        while ($row = $result->fetch_assoc()) {
            $turmas[] = $row;
        }
        echo json_encode(['success' => true, 'turmas' => $turmas]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
    }
} else {
    // Lista de todas as turmas (funcionalidade original)
    if ($cargo !== "Diretor" && $cargo !== "Coordenador" && $cargo !== "Administrador") {
        echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
        exit;
    }

    $sql = "SELECT id, nome, ano FROM turmas";
    $result = $conn->query($sql);
    $turmas = [];
    while ($row = $result->fetch_assoc()) {
        $turmas[] = $row;
    }
    echo json_encode(['success' => true, 'turmas' => $turmas]);
}

$conn->close();
?>