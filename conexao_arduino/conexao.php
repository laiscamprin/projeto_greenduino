<?php 

$exibir_tabela = false; 
$mensagem_erro = "";
$labels_dh = [];
$valores = [];
$valores_temperatura = []; 
$valores_umidade_ar = [];   
$valores_umidade_solo = [];
$valores_irrigacao = [];    


if(isset($_POST['botao-selecao']) && !empty($_POST['data_inicial'])) {
     $con = mysqli_connect('localhost', 'root', '1234', 'greenduino_db');
     
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
        $resultado_array = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        

        foreach($resultado_array as $linha) {
            // Labels: data + hora formatada (ex: "01/01 10:00")
            $labels_dh[] = date('d/m/Y H:i', strtotime($linha['data'] . ' ' . $linha['hora']));

            if($tipo_tabela === 'tudo'){
                $valores_temperatura[] = (float)($linha['temperatura']);  
                $valores_umidade_ar[] = (float)($linha['umidade_ar']);  
                $valores_umidade_solo[] = (float)($linha['umidade_solo']);
                $valores_irrigacao[] = (float)($linha['irrigacao'] ?? 0);
            } else{
            
            // Valores: baseado no tipo (switch necessário para pegar o campo certo)
            switch($tipo_tabela) {

                case 'temperatura':
                    $valores[] = (float)$linha['temperatura'];
                    break;
                case 'umidade_ar':
                    $valores[] = (float)$linha['umidade_ar'];
                    break;
                case 'umidade_solo':
                    $valores[] = (float)$linha['umidade_solo'];
                    break;
                case 'irrigacao':
                    $valores[] = (float)$linha['irrigacao']; 
                    break;
            }
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>DashBoard - Estufa</title>
</head>
<body>
    <h1>DashBoard</h1>
    <p id="nome-planta"></p>
    <!--<img src="planta.jpg">-->

    <form action="conexao.php" method="POST">
        <label for="data_inicial">Selecione a Data inicial</label>
        <input type="date" name="data_inicial" id="data-inicial" >

        <label for="data_final">Selecione a Data final(opcional)</label>
        <input type="date" name="data_final" id="data-final" >
        <button type="submit" name="botao-selecao" value="tudo">Pesquisar</button>
        <button type="submit" name="botao-selecao" value="temperatura">Temperatura</button>
        <button type="submit" name="botao-selecao" value="umidade_ar">Umidade do Ar</button>
        <button type="submit" name="botao-selecao" value="umidade_solo">Umidade do Solo</button>
        <button type="submit" name="botao-selecao" value="irrigacao">Irrigação</button>
    </form>

    <div id="div-botao-grafico" class="div-botao-grafico">
        <button id="botao-grafico" value="line"  onclick="tipoGrafico('line');">Linha  </button>
        <button id="botao-grafico" value="radar" onclick="tipoGrafico('radar');">Radar </button>
        <button id="botao-grafico" value="barra" onclick="tipoGrafico('bar');">Barra </button>    
    </div>

    <div id="grafico-tabela" class="grafico-tabela">
        <canvas id="canva-grafico" class="canva-grafico" width="800" height="400"> </canvas>
    </div>


<?php

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
                        case 'tudo':
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

         foreach($resultado_array as $exibirInfo): ?>

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
                                echo '<td>' .$exibirInfo['irrigacao'] .' </td>';
                                break;

                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            </table>

        <?php endif; ?>
    <?php endif; ?>


<?php if($exibir_tabela && !empty($labels_dh)) : ?>
<script>

    let grafico;
     const dadosPHP = {
             labels: <?php echo json_encode($labels_dh);?>,  
             valores: <?php echo json_encode($valores);?>,
             tipo: <?php echo json_encode($tipo_tabela);?>,
             valores_temperatura:<?php echo json_encode($valores_temperatura);?>,
             valores_umidade_solo:<?php echo json_encode($valores_umidade_solo);?>,
             valores_umidade_ar:<?php echo json_encode($valores_umidade_ar);?>,
             valores_irrigacao:<?php echo json_encode($valores_irrigacao);?>

             };

         const canvas = document.getElementById('canva-grafico');
        
        
        function tipoGrafico(tipoG){
            if(grafico){ grafico.destroy(); } 
            if(dadosPHP.labels && dadosPHP.labels.length >0){
            criarGrafico(dadosPHP,tipoG );}
            else{
                alert('Nenhum dados encontrado');
            }
        }

        function criarGrafico(dadosPHP, tipoG) {

            if(dadosPHP.tipo !== 'tudo'){
            grafico = new Chart(canvas, {
                type: tipoG,
                data: {
                    labels: dadosPHP.labels,
                    datasets: [{
                    label: dadosPHP.tipo,
                    data: dadosPHP.valores,
                    borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                    y: {
                     beginAtZero: true
                    }
                    },
                    maintainAspectRatio: false
                }
            });
        
            } else{
                grafico = new Chart(canvas, {
                type: tipoG,
                data: {
                    labels: dadosPHP.labels,
                    datasets: [
                    {
                        label: "Temperatura (°C)",
                        data: dadosPHP.valores_temperatura,
                        borderColor:'#ff0000',
                        borderWidth: 1
                    },
                    {
                        label:"Umidade do Ar (%)",
                        data:dadosPHP.valores_umidade_ar,
                        borderColor:'#0000ff',
                        borderWidth: 1
                    },
                    {
                        label:"Umidade do Solo(%)",
                        data: dadosPHP.valores_umidade_solo,
                        borderColor:'#ffa500',
                        borderWidth: 1
                    },
                    {
                        label:"Irrigação",
                        data:dadosPHP.valores_irrigacao,
                        borderColor:'#000000',
                        borderWidth: 1
                    }
                  ]
                },
                options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
            });

            }


}

           window.addEventListener('load', function() {
        if (dadosPHP.labels && dadosPHP.labels.length > 0) {
            criarGrafico(dadosPHP, 'line');
        }
    });

    console.log(dadosPHP.valores_temperatura);
    </script>
 <?php endif; ?>

</body>
</html>
