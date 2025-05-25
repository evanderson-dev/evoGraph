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