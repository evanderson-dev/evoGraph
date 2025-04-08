<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $sobrenome = trim($_POST['sobrenome'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $data_matricula = trim($_POST['data_matricula_hidden'] ?? null);
    $nome_pai = trim($_POST['nome_pai'] ?? null);
    $nome_mae = trim($_POST['nome_mae'] ?? null);
    $email = trim($_POST['email'] ?? null); // Novo campo email
    $turma_id = trim($_POST['turma_id'] ?? null);

    // Verificar se todos os campos obrigatórios estão presentes
    if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($matricula) || empty($turma_id)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos.']);
        exit;
    }

    // Processar o upload da foto
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'img/fotos_alunos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Criar pasta se não existir
        }

        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(['success' => false, 'message' => 'Formato de arquivo inválido. Use JPG, JPEG ou PNG.']);
            exit;
        }

        $foto_path = $upload_dir . $matricula . '.' . $file_ext;
        if (!move_uploaded_file($file_tmp, $foto_path)) {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar a foto no servidor.']);
            exit;
        }
    }

    // Atualizar o aluno no banco
    $sql = "UPDATE alunos SET nome = ?, sobrenome = ?, data_nascimento = ?, email = ?, nome_pai = ?, nome_mae = ?, turma_id = ?";
    if ($foto_path) {
        $sql .= ", foto = ?";
    }
    $sql .= " WHERE matricula = ?";

    $stmt = $conn->prepare($sql);
    $params = [$nome, $sobrenome, $data_nascimento, $email, $nome_pai, $nome_mae, $turma_id];
    if ($foto_path) {
        $params[] = $foto_path;
    }
    $params[] = $matricula;

    $stmt->bind_param(str_repeat('s', count($params)), ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o aluno no banco: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}

$conn->close();
?>