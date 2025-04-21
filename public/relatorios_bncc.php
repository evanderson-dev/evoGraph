<?php
require_once "db_connection.php";
$query = "SELECT JSON_EXTRACT(dados_json, '$.\"Série:\"') AS serie, AVG(pontuacao) AS media
          FROM respostas_formulario
          WHERE formulario_id = 'Avaliacao_Geografia_2025'
          GROUP BY serie";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Relatórios BNCC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h3>Desempenho por Série</h3>
    <canvas id="graficoMedia"></canvas>
    <script>
        const ctx = document.getElementById('graficoMedia').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php while ($row = $result->fetch_assoc()) { echo "'" . $row['serie'] . "',"; } ?>],
                datasets: [{
                    label: 'Média de Pontuação',
                    data: [<?php $result->data_seek(0); while ($row = $result->fetch_assoc()) { echo $row['media'] . ","; } ?>]
                }]
            }
        });
    </script>
</body>
</html>