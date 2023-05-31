<?php
$dir = dirname(__DIR__, 2);

include $dir.'/src/func/func.php';
include $dir . '/config/scandata_conf.php';

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'g9dbuser', 'g9password', 'g9db');
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Recuperar registros da tabela schedule_tests
$sql = "SELECT * FROM schedule_tests WHERE date <= NOW()";
$result = $conn->query($sql);

$tester = new SimpleAPI('localhost', 'g9dbuser', 'g9password', 'g9db'); 

if ($result->num_rows > 0) {

 while ($row = $result->fetch_assoc()) {
    $schedule_id = $row["schedule_id"]; 
    $client =  $row["cliente_id"];
    $test = $row["test_id"];
    $tResulID = $row["testes_id"];
    $exeDate = $row["date"];
    $test_del = $tester->deleteScheduleTest($schedule_id);
    if ($test_del['code'] === '208') {
        return $test_del;
    }
    $tester->scheduleTest($client,$test,$tResulID,$exeDate);
    }
}

// Fechar a conexão com o banco de dados
$conn->close();
?>
