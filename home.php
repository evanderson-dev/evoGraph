<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/js/all.min.css" rel="stylesheet" />
    <title>evoGraph Home</title>
</head>
<body>

<header>
    <div class="info-header">
        <div class="logo">
            <h3>evoGraph</h3>
        </div>
    </div>
    <div class="info-header">
        <i class="fa-solid fa-envelope"></i>
        <i class="fa-solid fa-bell"></i>
        <i class="fa-solid fa-user"></i>
        <img src="https://avatars.githubusercontent.com/u/94180306?s=40&v=4" alt="User" class="user-icon">
    </div>
</header><!--FIM HEADER-->

<section class="main">
    <div class="sidebar">
        <a class="sidebar-active" href="#"><i class="fa-solid fa-house"></i> Home</a>
        <a href="#"><i class="fa-solid fa-chart-bar"></i> Relatórios</a> 
        <a href="#"><i class="fa-solid fa-cog"></i> Configurações</a>
        <a href="#"><i class="fa-solid fa-sign-out"></i> Sair</a>
        <div class="separator"></div><br>
    </div><!--FIM SIDEBAR-->
    <div class="content">
        <div class="titulo-secao">
            <h2>Dashboard Professor</h2><br>
            <div class="separator"></div><br>
            <p><i class="fa-solid fa-house"></i> / Minhas Turmas</p>
        </div>

        <div class="box-info">
            <?php
            // Exemplo de dados dinâmicos
            $turmas = [
                ["nome" => "Infantil 1 A", "quantidade" => 20],
                ["nome" => "Infantil 1 B", "quantidade" => 20],
                ["nome" => "Infantil 1 C", "quantidade" => 100]
            ];

            foreach ($turmas as $turma) {
                echo "<div class='box-info-single'>";
                echo "<h3>{$turma['nome']}</h3>";
                echo "<p>{$turma['quantidade']}</p>";
                echo "</div>";
            }
            ?>
        </div>

        <div class="feed">
            <?php
            foreach ($turmas as $turma) {
                echo "<div class='feed-single'>";
                echo "<div class='feed-text'>";
                echo "<i class='fa-solid fa-house'></i>";
                echo "<span>{$turma['nome']}</span>";
                echo "</div>";
                echo "<div class='feed-time'>";
                echo "<h3>Texto</h3>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>

    </div><!--FIM CONTENT-->
</section><!--FIM MAIN-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/js/all.min.js"></script>
    
</body>
</html>
