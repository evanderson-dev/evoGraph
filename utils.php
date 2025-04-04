<?php
require_once 'db_connection.php';

function getTotalAlunos($conn, $cargo) {
    if ($cargo !== "Diretor" && $cargo !== "Administrador") {
        return null;
    }
    $sql = "SELECT COUNT(*) as total_alunos FROM alunos";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_assoc()['total_alunos'];
    }
    return null; // Retorna null em caso de erro
}

function getQuantidadeTurma($conn, $turma_id) {
    $sql = "SELECT COUNT(*) as quantidade FROM alunos WHERE turma_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $quantidade = $stmt->get_result()->fetch_assoc()['quantidade'];
    $stmt->close();
    return $quantidade;
}

function generateTabelaAlunos($conn, $turma_id, $cargo, $funcionario_id) {
    if ($cargo === "Professor") {
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.turma_id, t.nome AS turma_nome, f.nome AS professor_nome, f.sobrenome AS professor_sobrenome 
                FROM alunos a 
                JOIN turmas t ON a.turma_id = t.id 
                LEFT JOIN funcionarios f ON t.professor_id = f.id 
                WHERE t.id = ? AND t.professor_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return "<tr><td colspan='3'>Erro na preparação da query</td></tr>";
        }
        $stmt->bind_param("ii", $turma_id, $funcionario_id);
    } else {
        $sql = "SELECT a.nome, a.sobrenome, a.data_nascimento, a.matricula, a.turma_id, t.nome AS turma_nome, f.nome AS professor_nome, f.sobrenome AS professor_sobrenome 
                FROM alunos a 
                JOIN turmas t ON a.turma_id = t.id 
                LEFT JOIN funcionarios f ON t.professor_id = f.id 
                WHERE a.turma_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return "<tr><td colspan='4'>Erro na preparação da query</td></tr>";
        }
        $stmt->bind_param("i", $turma_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '';
    $colspan = ($cargo === "Professor") ? 3 : 4;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data_nascimento = $row["data_nascimento"] ? date("d/m/Y", strtotime($row["data_nascimento"])) : 'N/A';
            $professor = $row['professor_nome'] ? htmlspecialchars($row['professor_nome'] . " " . $row['professor_sobrenome']) : 'Sem professor';

            $html .= "<tr class='aluno-row' data-matricula='" . htmlspecialchars($row['matricula']) . "' data-turma-id='" . htmlspecialchars($row['turma_id']) . "'>";
            $html .= "<td>" . htmlspecialchars($row["nome"] . " " . $row["sobrenome"]) . "</td>";
            $html .= "<td>" . htmlspecialchars($data_nascimento) . "</td>";
            $html .= "<td>" . htmlspecialchars($row["matricula"]) . "</td>";
            if ($cargo === "Diretor" || $cargo === "Administrador") {
                $html .= "<td>";
                $html .= "<button class='action-btn edit-btn' title='Editar' onclick=\"openEditAlunoModal('" . htmlspecialchars($row['matricula']) . "', '" . htmlspecialchars($row['turma_id']) . "')\"><i class='fa-solid fa-pen-to-square'></i></button>";
                $html .= "<button class='action-btn delete-btn' title='Excluir' onclick=\"showDeleteAlunoModal('" . htmlspecialchars($row['matricula']) . "', '" . htmlspecialchars($row['turma_id']) . "')\"><i class='fa-solid fa-trash'></i></button>";
                $html .= "</td>";
            }
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='$colspan'>Nenhum aluno cadastrado nesta turma.</td></tr>";
    }
    $stmt->close();
    return $html;
}
?>