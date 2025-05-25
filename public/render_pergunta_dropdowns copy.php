<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once 'db_connection.php';

// Receber os dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$index = isset($input['index']) ? (int)$input['index'] : 0;
$pergunta = isset($input['pergunta']) ? htmlspecialchars($input['pergunta']) : 'Pergunta sem título';

ob_start();
?>
<div class="form-group-importar pergunta-group">
    <div class="pergunta-label">
        <label>Pergunta <?php echo ($index + 1); ?>: <?php echo $pergunta; ?></label>
    </div>
    <div class="dropdown-group">
        <div class="col-18">
            <label for="bnccAno_<?php echo $index; ?>">Ano Escolar:</label>
            <select id="bnccAno_<?php echo $index; ?>" name="bnccAno_<?php echo $index; ?>" required>
                <option value="">Selecione o ano</option>
                <?php
                $query = "SELECT id, nome FROM anos_escolares ORDER BY ordem";
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $ano_id = htmlspecialchars($row['id']);
                        $ano_nome = htmlspecialchars($row['nome']);
                        echo "<option value=\"$ano_id\">$ano_nome</option>";
                    }
                } else {
                    echo "<option value=\"\">Nenhum ano escolar disponível</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-18">
            <label for="bnccDisciplina_<?php echo $index; ?>">Disciplina:</label>
            <select id="bnccDisciplina_<?php echo $index; ?>" name="bnccDisciplina_<?php echo $index; ?>" disabled required>
                <option value="">Selecione a disciplina</option>
            </select>
        </div>
        <div class="col-18">
            <label for="bnccHabilidade_<?php echo $index; ?>">Habilidade BNCC:</label>
            <select id="bnccHabilidade_<?php echo $index; ?>" name="bnccHabilidade_<?php echo $index; ?>" disabled required>
                <option value="">Selecione a habilidade</option>
            </select>
        </div>
    </div>
</div>
<?php
$conn->close();
echo ob_get_clean();
?>