<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["cargo"] !== "Coordenador" && $_SESSION["cargo"] !== "Diretor" && $_SESSION["cargo"] !== "Administrador")) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"] ?? '');
    $sobrenome = trim($_POST["sobrenome"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $senha = trim($_POST["senha"] ?? '');
    $data_nascimento = $_POST["data_nascimento"] ?? '';
    $rf = trim($_POST["rf"] ?? '');
    $cargo = $_POST["cargo"] ?? '';
    $edit_id = isset($_POST["edit_id"]) ? (int)$_POST["edit_id"] : null;

    // Validações básicas
    if (empty($nome) || empty($sobrenome) || empty($email) || empty($data_nascimento) || empty($rf) || empty($cargo)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }

    try {
        // Verificar se o RF já existe
        $check_rf_sql = "SELECT id FROM funcionarios WHERE rf = ? AND id != ?";
        $check_rf_stmt = $conn->prepare($check_rf_sql);
        $check_id = $edit_id ?? 0;
        $check_rf_stmt->bind_param("si", $rf, $check_id);
        $check_rf_stmt->execute();
        $check_rf_result = $check_rf_stmt->get_result();

        // Verificar se o e-mail já existe
        $check_email_sql = "SELECT id FROM funcionarios WHERE email = ? AND id != ?";
        $check_email_stmt = $conn->prepare($check_email_sql);
        $check_email_stmt->bind_param("si", $email, $check_id);
        $check_email_stmt->execute();
        $check_email_result = $check_email_stmt->get_result();

        if ($check_rf_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'O Registro Funcional (RF) já está em uso por outro funcionário.']);
            exit;
        }
        if ($check_email_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'O e-mail já está em uso por outro funcionário.']);
            exit;
        }

        if ($edit_id) {
            // Edição
            $sql = "UPDATE funcionarios SET nome = ?, sobrenome = ?, email = ?, data_nascimento = ?, rf = ?, cargo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $nome, $sobrenome, $email, $data_nascimento, $rf, $cargo, $edit_id);
            if ($stmt->execute()) {
                if (!empty($senha)) {
                    $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
                    $sql_senha = "UPDATE funcionarios SET senha = ? WHERE id = ?";
                    $stmt_senha = $conn->prepare($sql_senha);
                    $stmt_senha->bind_param("si", $hashed_senha, $edit_id);
                    $stmt_senha->execute();
                    $stmt_senha->close();
                }
                echo json_encode(['success' => true, 'message' => 'Funcionário atualizado com sucesso!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar funcionário: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            // Cadastro
            if (empty($senha)) {
                echo json_encode(['success' => false, 'message' => 'A senha é obrigatória para novos funcionários.']);
                exit;
            }
            $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO funcionarios (nome, sobrenome, email, senha, data_nascimento, rf, cargo) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $nome, $sobrenome, $email, $hashed_senha, $data_nascimento, $rf, $cargo);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Funcionário cadastrado com sucesso!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar funcionário: ' . $stmt->error]);
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
$conn->close();
?>