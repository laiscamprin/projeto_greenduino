<?php 

$exibir_tabela = false; 
$mensagem_erro = "";

if(isset($_POST['botao-selecao']) && !empty($_POST['data_inicial'])) {
     $con = mysqli_connect('localhost', 'root', '1234', 'greenduino_db');
     // tratamento bem simples caso o mysql de erro, assim é mais facil de identificar
        if(!$con){
            $mensagem_erro = "Erro na conexão do Banco. Erro: " . mysqli_connect_error();
        } else {
            $exibir_tabela = true;
        }

        $tipo_tabela = $_POST['botao-selecao']; // aqui pega o conteudo do botao, essencial para decidir a tabela
        $data_inicial = $_POST['data_inicial'];
        $data_final = $_POST['data_final'];
        if(empty($data_final)){ $data_final = $data_inicial;}

        $select_tabela = "";
        switch ($tipo_tabela) { 
            case 'tudo':  
                $select_tabela = "SELECT data, hora, temperatura, umidade_ar, umidade_solo, irrigacao FROM registros WHERE data BETWEEN '$data_inicial' AND '$data_final' ORDER BY data ASC, hora ASC";
                break;  
            case 'temperatura': 
                $select_tabela = "SELECT data, hora, temperatura FROM registros WHERE data BETWEEN '$data_inicial' AND '$data_final' ORDER BY data ASC, hora ASC";
                break;
            case 'umidade_ar':
                $select_tabela = "SELECT data, hora, umidade_ar FROM registros WHERE data BETWEEN '$data_inicial' AND '$data_final' ORDER BY data ASC, hora ASC";
                break;
            case 'umidade_solo':
                $select_tabela = "SELECT data, hora, umidade_solo FROM registros WHERE data BETWEEN '$data_inicial' AND '$data_final' ORDER BY data ASC, hora ASC";
                break;
            case 'irrigacao':
                $select_tabela = "SELECT data, hora, irrigacao FROM registros WHERE data BETWEEN '$data_inicial' AND '$data_final' ORDER BY data ASC, hora ASC";
                break;
            default:  
                $select_tabela = "SELECT data, hora, temperatura, umidade_ar, umidade_solo, irrigacao FROM registros WHERE data BETWEEN '$data_inicial' AND '$data_final' ORDER BY data ASC, hora ASC";
                break;
        } 

        $resultado = mysqli_query($con, $select_tabela);
        //tratamento de erro para o resultado?

}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
    <title>DashBoard - Estufa</title>
</head>
<body>
    <h1>DashBoard</h1>
    <p id="nome-planta"></p>
    <img src="planta.jpg">

    <form action="conexao.php" method="POST">
        <label for="data_inicial">Selecione a Data inicial</label>
        <input type="date" name="data_inicial" id="data-inicial" >

        <label for="data_final">Selecione a Data final(opcional)</label>
        <input type="date" name="data_final" id="data-final" >
        <button type="submit" id="botao-enviar">Pesquisar</button>

        <button type="submit" name="botao-selecao" value="tudo">Todos</button>
        <button type="submit" name="botao-selecao" value="temperatura">Temperatura</button>
        <button type="submit" name="botao-selecao" value="umidade_ar">Umidade do Ar</button>
        <button type="submit" name="botao-selecao" value="umidade_solo">Umidade do Solo</button>
        <button type="submit" name="botao-selecao" value="irrigacao">Irrigação</button>
    </form>

    <div id="grafico-tabela" class="grafico-tabela">
        <canvas id="canva-grafico" class="canva-grafico"> </canvas>

    </div>
</body>
</html>

<?php
// agr é hora da arte, ou seja, exibir os resultados

     if($exibir_tabela) :?>

 

     <?php if(!empty($mensagem_erro)) : ?>
        <div class="mensagem-erro"><?php echo $mensagem_erro ?> </div>
      <?php else: ?>

            <table>
                <tr>
                    <th> Data</th>
                    <th> Hora</th>
                    <?php 
                    switch($tipo_tabela){
                        case'tudo':
                        echo '<th>Temperatura (°C)</th><th>Umidade do Ar (%)</th><th>Umidade do Solo (%)</th><th>Irrigação</th>'; 
                        break;

                        case 'temperatura':
                        echo '<th>Temperatura (°C)</th>'; 
                        break;

                        case 'umidade_ar':
                        echo '<th>Umidade do Ar (%)</th>';
                        break;
                        
                        case 'umidade_solo':
                        echo '<th> Umidade do Solo (%)</th>';
                        break;

                        case 'irrigacao': 
                        echo '<th> Irrigação</th>';
                        break;

                        default:
                        echo '<th>Temperatura (°C)</th><th>Umidade do Ar (%)</th><th>Umidade do Solo (%)</th><th>Irrigação</th>'; 
                        break;
                    }
                    ?>
            </tr>
            
        <?php

         foreach($resultado as $exibirInfo): ?>

           <tr>
                <td> <?php echo date('d/m/Y', strtotime($exibirInfo['data'])) ;?> </td>
                <td> <?php echo $exibirInfo['hora'] ;?></td>
                    <?php 
                       switch($tipo_tabela) {
                            case 'tudo':
                                echo '<td>' .$exibirInfo['temperatura'] .'°C</td>'
                                     .'<td>' .$exibirInfo['umidade_ar'] .'%</td>'
                                     .'<td>' .$exibirInfo['umidade_solo'] . '% </td>'
                                     .'<td>' .$exibirInfo['irrigacao'] .'</td>';
                                break;

                            case 'temperatura':
                                echo '<td>' .$exibirInfo['temperatura'] .'°C </td>';
                                break;
                            
                            case 'umidade_ar':
                                echo '<td>' .$exibirInfo['umidade_ar'] .'% </td>';
                                break;

                            case 'umidade_solo':
                                echo '<td>' .$exibirInfo['umidade_solo'] .'% </td>';
                                break;

                            case 'irrigacao':
                                echo '<td>' .$exibirInfo['irrigacao'] .'% </td>';
                                break;

                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            </table>

        <?php endif; ?>
    <?php endif; ?>

  <script>
    // exemplo da conversão do php para json, para receber em js
    const labels = <?php echo json_encode($labels); ?>;
    const tipoTabela = '<?php echo $tipo_tabela; ?>';
</script>
hhhhh
