<?php
header("refresh:10");
$con = mysqli_connect('localhost', 'root', '****', 'exemplo_serial');
$resultados = mysqli_query($con, "SELECT*FROM registros");

?>
<?php // parte de exibir a tabela de informações?>
<table border="1">
    <tr>
        <td>ID</td>
        <td>Data</td>
        <td>Hora</td>
        <td>Informação</td>
</tr>
<?php
while ($exibir = mysqli_fetch_array($resultados)){ 
    ?>        
    <tr>
    <td><?php echo $exibir['id'] ; ?> </td>     
    <td><?php echo $exibir['data'] ; ?> </td>  
    <td><?php echo $exibir['hora'] ; ?> </td>      
    <td><?php echo $exibir['informacao'] ; ?> </td>    
    </tr>
    
    <?php
  
} 
?> 
