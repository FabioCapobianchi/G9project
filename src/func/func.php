<?php
$dir = dirname(__DIR__, 2);
include $dir . '/config/code_list.php';

class SimpleAPI
{
    private $db;
    public function __construct($db_ip, $db_user, $db_password, $db_name)
    {
        $this->db = new mysqli($db_ip, $db_user, $db_password, $db_name);

        // Verificar se a conexão foi bem sucedida
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }
    //QUICK MANUAL
    public function showManual()
    {
        include('manual.html');
        $res['code'] = '209';
        return $res;
    }

    //SHOW ALL CLIENTS  OK!!!
    public function showClients()
    {
        $query = "SELECT * FROM clientes";
        $result = $this->db->query($query);
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $res['code'] = '209';
            $res['data'] = $rows;
        } else {
            $res['code'] = '204';
        }
        return $res;
    }

    //SHOW ALL PROFILES  OK !!!
    public function showProfiles()
    {
        $query = "SELECT * FROM perfis";
        $result = $this->db->query($query);
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $res['code'] = '209';
            $res['data'] = $rows;

        } else {
            $res['code'] = '204';
        }
        return $res;
    }

    //SHOW ALL TESTS  OK !!!
    public function showTests()
    {
        $query = "SELECT * FROM tests";
        $result = $this->db->query($query);

        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $res['code'] = '209';
            $res['data'] = $rows;
        } else {
            $res['code'] = '204';
        }
        return $res;
    }

    //SHOW CLIENT BY ID  OK !!!
    public function getClient($param)
    {
        $id = $param['id'];
        if (empty($id)) {
            $res['code'] = '404';
            $res['tip'] = "Cliente ID is missing. Try action=getClient&id=61";
            return $res;
        } else {
            $query = "SELECT * FROM clientes WHERE cliente_id=?";
            $statement = $this->db->prepare($query);
            $statement->bind_param("i",$id);
        }

        if ($statement->execute()) {
            $result = $statement->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_all(MYSQLI_ASSOC);
                $res['code'] = '209';
                $res['data'] = $row;
                $testId = $this->getClientTest($id);
                if ($testId['code'] === '209') {
                    $res['tip'] = $testId['data'];
                    return $res;
                } else {
                    $res['tip'] = $testId['tip'];
                    return $res;
                }
               
            } else {
                $res['code'] = '204';
                $res['tip'] = "No client found with the provided ID: $id.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    public function getClientTest($id)
    {
        $query = "SELECT testes_id FROM test_result WHERE cliente_id=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            $result = $statement->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_all(MYSQLI_ASSOC);
                $res['code'] = '209';
                $res['data'] = $row;
                return $res;
            } else {
                $res['code'] = '204';
                $res['tip'] = "No test found with the provided ID: $id.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    public function validClient($param)
    {
        $id = $param;
        if (empty($id)) {
            $res['code'] = '404';
            $res['tip'] = "Cliente ID is missing. Try action=getClient&id=61";
            return $res;
        } else {
            $query = "SELECT * FROM clientes WHERE cliente_id=?";
            $statement = $this->db->prepare($query);
            $statement->bind_param("i", $id);
        }

        if ($statement->execute()) {
            $result = $statement->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $res['code'] = '209';
                $res['data'] = $row;
                return $res;
            } else {
                $res['code'] = '204';
                $res['tip'] = "No client found with the provided ID: $id.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    //GET SERVICES
    public function getServices($param)
    {
        $id = $param;
        $query = "SELECT cliente_id, test_id, service, port, state, proto, banner  FROM services where cliente_id = $id";
        $result = $this->db->query($query);
        if ($result && $result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $res['code'] = '209';
            $res['data'] = $rows;
        } else {
            $res['code'] = '204';
        }
        return $res;
    }
   

    public function getDescri($id)
{
    $query = "SELECT descricao FROM test_result WHERE testes_id=?";
    $statement = $this->db->prepare($query);
    $statement->bind_param("i", $id);

    if ($statement->execute()) {
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            
            $row = $result->fetch_assoc();
            $descri = explode(", ", $row['descricao']);
            $res['code'] = '209';
            $res['data'] = $descri;
            return $res;
        } else {
            $res['code'] = '204';
            $res['tip'] = "No descri found with the provided ID: $id.";
            return $res;
        }
    } else {
        $res['code'] = '205';
        return $res;
    }
}


    //RETURNS CLIENT TESTS
    public function getInfoTest($param)
    {
        $id = $param['id'];
        if (empty($id)) {
            $res['code'] = '404';
            $res['tip'] = "Cliente ID is missing. Try action=getInfoTest&id=test_id";
            return $res;
        } else {
            $query = "SELECT g9db.clientes.cliente_id, g9db.clientes.tgt_ip, g9db.clientes.client_descri, g9db.perfis.prof_id, 
            g9db.perfis.gate, g9db.tests.test_id, g9db.tests.ports, g9db.test_result.testes_id, g9db.test_result.date, 
            g9db.test_result.execution_time 
            FROM g9db.clientes, g9db.perfis, g9db.tests, g9db.test_result 
            WHERE tests.prof_id = perfis.prof_id and test_result.testes_id = ? 
            and test_result.cliente_id=clientes.cliente_id and test_result.test_id = tests.test_id";
            $statement = $this->db->prepare($query);
            $statement->bind_param("i", $id);
        }

        if ($statement->execute()) {
            $result = $statement->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_all(MYSQLI_ASSOC);
                $res['code'] = '209';
                $res['data'] = $row;
                $descri = $this->getDescri($id);
                if ($descri['code'] === '209') {
                    $res['tip'] = $descri['data'];
                    return $res;
                } else {
                    $res['tip'] = $descri['tip'];
                    return $res;
                }
            } else {
                $res['code'] = '204';
                $res['tip'] = "No tests_result found with the provided ID: $id.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    //SHOW TEST BY ID  OK  !!!!!
    public function getTest($id)
    {
        $query = "SELECT * FROM tests WHERE test_id=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            $result = $statement->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $ports = $row["ports"];
                return $ports;
            } else {
                $res['code'] = '204';
                $res['tip'] = "No test found with the provided ID: $id.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    //GET GATEWAY FROM TEST_ID   OK 
    public function getGate($testId)
    {
        $query = "SELECT p.gate FROM perfis p INNER JOIN tests t ON p.prof_id = t.prof_id WHERE t.test_id = ?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("i", $testId);

        if ($statement->execute()) {
            $result = $statement->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $gateway = $row["gate"];
                return $gateway;
            } else {
                $res['code'] = '204';
                $res['tip'] = "No records found with the provided ID: $testId.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    //SHOW IP BY CLIENT_ID  OK
    public function getIp($id)
    {
        $query = "SELECT tgt_ip FROM clientes WHERE cliente_id=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            $result = $statement->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $ip = $row["tgt_ip"];
                return $ip;
            } else {
                $res['code'] = '204';
                $res['tip'] = "No target ip found with the provided ID: $id.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }
    }

    //GET NUMBER OF TESTS DONE ON CLIENT  ?????  PUBLIC
    public function getNTest($id)
    {
        $query = "SELECT n_test FROM clientes WHERE cliente_id=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("i", $id);

        if ($statement->execute()) {
            $result = $statement->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["n_test"];
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    //CREATE CLIENT    OK !!!
    public function createClient($param)
    {
        $host = $param['host'];
        $descri = $param['descri'];
        if (empty($host) || empty($descri)) {
            $res['code'] = '404';
            $res['tip'] = "Try host=hostname\ip&descri=client_descri";
            return $res;
        } else {
            $ip = $this->check_param($host);

            if ($ip === False) {
                $res['code'] = '405';
                $res['tip'] = "Try valid ip or hostname";
                return $res;
            }
            $n_test = 0;
            $query = "INSERT INTO clientes(n_test, tgt_ip, client_descri, date) VALUES (?, ?, ?, NOW())";
            $statement = $this->db->prepare($query);
            $statement->bind_param("iss", $n_test, $ip, $descri);
        }
        if ($statement->execute()) {
            $id = $statement->insert_id;
            $msg = "Client created successfully. Client id: $id ";
            $res['code'] = '202';
            $res['tip'] = $msg;
            return $res;
        } else {
            $res['code'] = '205';
            $res['tip'] = "Error creating client: " . $statement->error;
            return $res;
        }
    }

    //CREATE TEST   OK !!!
    public function createTest($param)
    {
        $perfil = $param['perfil'];
        $ports = $param['ports'];

        if (empty($perfil) || empty($ports)) {
            $res['code'] = '404';
            $res['tip'] = "Try perfil=prof_id&ports= 80/all";
            return $res;
        }
        $query = "INSERT INTO tests(prof_id, ports,date) VALUES (?, ?, NOW())";
        $statement = $this->db->prepare($query);
        $statement->bind_param("is", $perfil, $ports);

        if ($statement->execute()) {
            $id = $statement->insert_id;
            $msg = "Test created successfully. Test id: $id ";
            $res['code'] = '202';
            $res['tip'] = $msg;
            return $res;
        } else {
            $res['code'] = '205';
            $res['tip'] = "Error creating Test: " . $statement->error;
            return $res;
        }
    }

    public function makeTest($param)
    {
        $client = $param['client'];
        $test = $param['test'];
        $date = $param['date'];
        date_default_timezone_set('Europe/Lisbon');

        if (empty($client) || empty($test) || empty($date)) {
            $res['code'] = '404';
            $res['tip'] = "Try createSchedule&client=client_id&test=test_id&date=YYYY-MM-DDTHH:MM:SS or now";
            return $res;
        }
        $client_exist = $this->validClient($client);
        if ($client_exist['code'] === '209') {
            $test_exist = $this->getTest($test);
            if (is_string($test_exist)) {

                if ($date !== 'now') {
                    if ($this->validate_date($date)) {
                        $currentDateTime = new DateTime();
                        $time = $currentDateTime->format('Y-m-d H:i:s');
                        $inputDate = str_replace('T', ' ', $date);

                        if ($inputDate < $time) {
                            $res['code'] = '404';
                            $res['tip'] = "Warning !!!! Date must be greater than the current date";
                            return $res;
                        }
                    } else {
                        $res['code'] = '404';
                        $res['tip'] = "Please insert valid date format: date=YYYY-MM-DDTHH:MM:SS or date=now";
                        return $res;
                    }
                } else {
                    $currentDateTime = new DateTime();
                    $time = $currentDateTime->format('Y-m-d H:i:s');
                    $date = $time;
                }
            } else {
                $res['code'] = $test_exist['code'];
                $res['tip'] = $test_exist['tip'];
                return $res;
            }

        } else {
            $res['code'] = $client_exist['code'];
            $res['tip'] = $client_exist['tip'];
            return $res;
        }

        $tResulID = $this->createTestResul($client, $test);
        if ($tResulID['code'] === '205') {
            return $tResulID;
        }
        $query = "INSERT INTO schedule_tests(cliente_id, test_id, testes_id, date) VALUES (?, ?, ?, ?)";
        $statement = $this->db->prepare($query);
        $statement->bind_param("iiis", $client, $test, $tResulID['data'], $date);

        if ($statement->execute()) {
            $id = $statement->insert_id;
            $res['code'] = '202';
            $res['data'] = $tResulID['data'];
            $res['tip'] = "Schedule Create Success Result_ID: " . $tResulID['data'];
            return $res;
        } else {
            $res['code'] = '205';
            $res['tip'] = "Error Schedule Test: ";
            return $res;
        }
    }



    public function createTestResul($client, $test)
    {
        $descricao = " ";
        $query = "INSERT INTO test_result (date, cliente_id, test_id, descricao) VALUES (NOW(), ?, ?, ?)";
        $statement = $this->db->prepare($query);
        $statement->bind_param("iis", $client, $test, $descricao);

        if ($statement->execute()) {
            $id = $statement->insert_id;
            $res['code'] = '202';
            $res['data'] = $id;
            $res['tip'] = "TestResult created successfully. TestResult id: $id <br>";
            return $res;
        } else {
            $res['code'] = '203';
            $res['tip'] = "Error creating TestResult";
            return $res;
        }
    }


    //INSERT FUNCTIONS
//INSERT NEW SERVICE
    public function insertService($client, $test, $servico, $porta, $estado, $protocolo, $mensagem, $descri, $tResulID)
    {
        $query = "INSERT INTO services(date,cliente_id, test_id, service, port, state, proto, banner) VALUES (NOW(),?,?,?,?,?,?,?)";
        $statement = $this->db->prepare($query);
        $statement->bind_param("iisssss", $client, $test, $servico, $porta, $estado, $protocolo, $mensagem);

        if ($statement->execute()) {
            $id = $statement->insert_id;
            $this->updateDescri($client, $descri, $tResulID);
            return $id;
        } else {
            return false;
        }

    }

    public function updateState($service_id, $estado, $client, $obs)
    {
        $query = "INSERT INTO state(date, service_id, state, cliente_id, obs) VALUES (NOW(),?, ?, ?, ?)";
        $statement = $this->db->prepare($query);
        $statement->bind_param("isis", $service_id, $estado, $client, $obs);

        if ($statement->execute()) {
            $id = $statement->insert_id;
            return $id;
        } else {
            return false;
        }

    }

    //UPDATE FUNCTIONS
//UPDATE NUNBER OF CLIENT  TESTS
    public function updateNTest($client_id)
    {
        $increment = 1;
        $query = "UPDATE clientes set n_test = n_test + ? where cliente_id = ? ";
        $statement = $this->db->prepare($query);
        $statement->bind_param("ii", $increment, $client_id);

        if ($statement->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateExeTest($tResulID, $exeDate)
    {
        $query = "UPDATE test_result set date = ? where testes_id = ? ";
        $statement = $this->db->prepare($query);
        $statement->bind_param("si", $exeDate, $tResulID);

        if ($statement->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateDescri($client_id, $descricao, $tResulID)
    {
        $query = "UPDATE test_result SET descricao = CONCAT(descricao, ?) WHERE testes_id = ?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("si", $descricao, $tResulID);

        if ($statement->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateExection($testes_id, $formattedTime)
    {
        $query = "UPDATE test_result SET execution_time = ? WHERE testes_id = ?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("si", $formattedTime, $testes_id);

        if ($statement->execute()) {
            return true;
        } else {
            return false;
        }
    }

    //DELETE FUNCTIONS
//DELETE CLIENT BY ID OK!!!!
    public function deleteClient($param)
    {
        $id = $param['id'];
        if (empty($id)) {
            $res['code'] = '404';
            $res['tip'] = "Try id=cliente_id";
            return $res;
        }

        $res = $this->getClient($param);

        if ($res['code'] === '209') {
            $query = "DELETE FROM clientes WHERE cliente_id=?";
            $statement = $this->db->prepare($query);
            $statement->bind_param("i", $id);

            if ($statement->execute()) {
                $res['code'] = '207';
                $res['tip'] = "Record deleted successfully.";
                return $res;
            } else {
                $res['code'] = '208';
                $res['tip'] = "No client found with the provided ID.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }

    }

    //DELETE TEST BY ID  OK!!!!!!
    public function deleteTest($param)
    {
        $id = $param['id'];
        if (empty($id)) {
            $res['code'] = '404';
            $res['tip'] = "Try id=test_id";
            return $res;
        }
        $res = $this->getTest($id);

        if (is_string($res)) {
            $query = "DELETE FROM tests WHERE test_id=?";
            $statement = $this->db->prepare($query);
            $statement->bind_param("i", $id);
            if ($statement->execute()) {
                $res['code'] = '207';
                $res['tip'] = "Record deleted successfully.";
                return $res;
            } else {
                $res['code'] = '208';
                $res['tip'] = "No Test found with the provided ID.";
                return $res;
            }
        } else {
            $res['code'] = '205';
            return $res;
        }

    }

    public function deleteScheduleTest($schedule_id)
    {
        $query = "DELETE FROM schedule_tests WHERE schedule_id=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("i", $schedule_id);

        if ($statement->execute()) {
            $res['code'] = '207';
            $res['tip'] = "Schedule deleted successfully.";
            return $res;
        } else {
            $res['code'] = '208';
            $res['tip'] = "No schedule found with the provided ID.";
            return $res;
        }
    }

    //LOGICAL FUNCTIONS
    // SCHEDULE TEST
    public function scheduleTest($client, $test, $tResulID, $exeDate)
    {
        $newGateway = $this->getGate($test);
        if (is_string($newGateway)) {
            $data = " Gateway: " . $newGateway . " ";
        } else {
            $this->updateDescri($client, $newGateway['code'], $tResulID);
            return $newGateway;
        }

        $ip = $this->getIp($client);
        if (is_string($ip)) {
            $data .= " Target IP: " . $ip . " Client_ID: " . $client . " ";
        } else {
            $this->updateDescri($client, $ip['code'], $tResulID);
            return $ip;
        }

        $ports = $this->getTest($test);
        if (is_string($ports)) {
            $data .= " Test_ID: " . $test . " Scan ports: " . $ports . " ";
        } else {
            $this->updateDescri($client, $ports['code'], $tResulID);
            return $ports;
        }

        $gate = $this->updateGatewayForIP($ip, $newGateway);
        if (is_string($gate)) {
            $data .= " Gateway update Success ";
        } else {
            $this->updateDescri($client, $gate['code'], $tResulID);
            return $gate;
        }

        $upExeTest = $this->updateExeTest($tResulID, $exeDate);
        if ($upExeTest == true) {
            $data .= " Execution date update Success ";
        } else {
            $res['code'] = '205';
            $res['tip'] = 'Update Execution Time Failure';
            $this->updateDescri($client, $res['code'], $tResulID);
            return $res;
        }

        $scan_Res = $this->testScan($ip, $client, $ports, $test, $tResulID);
        if ($scan_Res['code'] === '102') {
            $data .= "Scanning Code: " . $scan_Res['code'] . " Scanning Services: " . $scan_Res['data'];
        } else {
            $this->updateDescri($client, $scan_Res['code'], $tResulID);
            return $scan_Res;
        }

        $resetGate = $this->resetGateway($ip);
        if ($resetGate === true) {
            $data .= "Gateway delete Success";
        } else {
            $this->updateDescri($client, $resetGate['code'], $tResulID);
            return $scan_Res;
        }
        $res['code'] = '102';
        $res['data'] = $data;
        $res['tip'] = "Scan Scheduled Execution Success";
        return $res;
    }


    public function testScan($ip, $client, $ports, $test, $tResulID)
    {
        $start = microtime(true);
        $n_test = $this->getNtest($client);
        $scan_services = []; // Array para armazenar os valores
        $service_count = 0;

        if ($n_test === false) {
            $res['code'] = '507';
            $res['tip'] = 'GetNtest Failure';
            return $res;
        }
        if ($ports === "all") {
            // Execute the nmap command with the IP address parameter to scan TCP/UDP services
            $command = 'sudo nmap -sS -sU -O -T4 ' . $ip;
            $resultado = shell_exec($command);

            if ($resultado === null) {
                $res['code'] = '507';
                $res['tip'] = 'Execution Failure shell_exec nmap';
                return $res;
            }
            // Regular expression to extract the information
            $regex = '/([0-9]+)\/(udp|tcp)\s+(open|closed|filtered)\s+([^\n]+)/';

            // Execute the regular expression on the nmap command output
            preg_match_all($regex, $resultado, $matches, PREG_SET_ORDER);
        } else {
            // Construct the nmap command with the specified port
            $command = 'sudo nmap -p ' . $ports . ' -sS -sU -O -T4 ' . escapeshellarg($ip);
            // Execute the command and capture the output
            $resultado = shell_exec($command);

            if ($resultado === null) {
                $res['code'] = '507';
                $res['tip'] = 'Execution Failure shell_exec nmap';
                return $res;
            }
            // Define a regular expression to extract the desired information
            $regex = '/([0-9]+)\/(udp|tcp)\s+(open|closed|filtered)\s+([^\n]+)/';
            // Execute the regular expression on the nmap command output
            preg_match_all($regex, $resultado, $matches, PREG_SET_ORDER);
        }
        // Percorre os resultados e guarda as informações em variáveis
        foreach ($matches as $match) {
            $porta = $match[1];
            $protocolo = $match[2];
            $estado = $match[3];
            $servico = trim($match[4]);
            $default_msg = "no message";

            $comando = 'expect -c "spawn telnet ' . $ip . ' ' . $porta . '; expect \{*\} {puts \\"%expect_out(buffer)\\"; exit 0}"';
            // Executa o comando expect e captura a saída
            $output = shell_exec($comando);

            if ($estado !== 'closed') {
                // Verifica se a conexão foi bem-sucedida
                if (strpos($output, 'Connected to') !== false) {
                    // Encontra a posição do caractere '^]'
                    $posicao = strpos($output, '^]\'.');
                    if ($posicao !== false) {
                        // Extrai a mensagem após o caractere '^]'
                        $mensagem = substr($output, $posicao + 4);
                        $posicaoColchete = strpos($mensagem, '[');

                        // Verifica se encontrou o caractere '['
                        if ($posicaoColchete !== false) {
                            // Extrai a parte da substring até o caractere '['
                            $substringFinal = substr($mensagem, 0, $posicaoColchete);
                            $default_msg = trim($substringFinal);
                        } else {
                            $default_msg = trim($mensagem);
                        }
                        $default_msg = iconv(mb_detect_encoding($default_msg, mb_detect_order(), true), 'UTF-8', $default_msg);
                        if (empty($default_msg)) {
                            $default_msg = 'no message';
                        }
                    } else {
                        $default_msg = 'no message';
                    }
                } else {
                    $default_msg = 'Connection refused';
                }
            } else {
                $default_msg = 'no message';
            }

            $mensagem = $default_msg;
            $descri = "Porta: $porta Protocolo: $protocolo Estado: $estado Serviço: $servico Banner: $mensagem \n";

            // Criar uma variável para armazenar os valores
            $dados = [
                'cliente_id' => $client,
                'test_id' => $test,
                'porta' => $porta,
                'protocolo' => $protocolo,
                'estado' => $estado,
                'servico' => $servico,
                'mensagem' => $mensagem
            ];
            // Adicionar a variável ao array de descrições
            $scan_services[] = $dados;

            if ($n_test === 0) {
                $service_id = $this->insertService($client, $test, $servico, $porta, $estado, $protocolo, $mensagem, $descri, $tResulID);
                if ($service_id === false) {
                    $res['code'] = '507';
                    $res['tip'] = "Error inserting service";
                    return $res;
                }
            } else {
                $service_status = $this->serviceCompare($client, $test, $servico, $porta, $estado, $protocolo, $mensagem);
                //If service exist
                if ($service_status['opt'] === '1') {
                    $descri = "Porta: $porta Protocolo: $protocolo Estado: $estado Serviço: $servico Banner: $mensagem ";
                    $descri .= " SERVICE OK \n";
                    $upDescri = $this->updateDescri($client, $descri, $tResulID);
                    if ($upDescri === false) {
                        $res['code'] = '507';
                        $res['tip'] = "Error updating service";
                        return $res;
                    }
                }

                //If service exist and estate change
                if ($service_status['opt'] === '2') {
                    $service_id = $service_status['id'];
                    $descri = "Porta: $porta Protocolo: $protocolo Estado: $estado Serviço: $servico Banner: $mensagem ";
                    $descri .= " SERVICE ALERT \n";
                    $upDescri = $this->updateDescri($client, $descri, $tResulID);
                    if ($upDescri === false) {
                        $res['code'] = '507';
                        $res['tip'] = "Error updating service";
                        return $res;
                    }
                    $obs = " Changed Service!!! Verify state and banner ";
                    $id = $this->updateState($service_id, $estado, $client, $obs);
                    if ($id === false) {
                        $res['code'] = '507';
                        $res['tip'] = "Error updating state";
                        return $res;
                    }
                }

                //If no service 
                if ($service_status['opt'] === '3') {
                    $descri = "Porta: $porta Protocolo: $protocolo Estado: $estado Serviço: $servico Banner: $mensagem ";
                    $descri .= " SERVICE ALERT \n";
                    $service_id = $this->insertService($client, $test, $servico, $porta, $estado, $protocolo, $mensagem, $descri, $tResulID);
                    if ($service_id === false) {
                        $res['code'] = '507';
                        $res['tip'] = "Error inserting service";
                        return $res;
                    }
                    $obs = " New Service Inserted!!!";
                    $id = $this->updateState($service_id, $estado, $client, $obs);
                    if ($id === false) {
                        $res['code'] = '507';
                        $res['tip'] = "Error updating state";
                        return $res;
                    }
                }
            }
        }

        if ($n_test > 0) {
            $this->serviceMissing($client, $test, $servico, $scan_services, $descri, $tResulID);
        }

        $upNTest = $this->updateNTest($client);
        if ($upNTest === false) {
            $res['code'] = '507';
            $res['tip'] = "Error updating NTest";
            return $res;
        }
        $end = microtime(true);

        // Calculate the execution time
        $executionTime = $end - $start;
        $formattedTime = gmdate("i:s", $executionTime);
        $eTime = $this->updateExection($tResulID, $formattedTime);
        if ($eTime === false) {
            $res['code'] = '507';
            $res['tip'] = "Error updating execution time";
            return $res;
        }
        $res['code'] = '102';
        $res['data'] = " Scanning Services: " . $service_count . " Execution Time: " . $formattedTime;
        return $res;
    }

    public function serviceMissing($client, $test, $servico, $scan_services, $descri, $tResulID)
    {
        $query = "SELECT service_id, service, state FROM services WHERE cliente_id=? AND test_id=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("ii", $client, $test);

        if ($statement->execute()) {
            $result = $statement->get_result();
        }

        $resultServices = [];
        while ($row = $result->fetch_assoc()) {
            $resultServices[] = $row;
        }

        // Check if the services in $result exist in $scan_services
        foreach ($resultServices as $serviceData) {
            $service_id = $serviceData['service_id'];
            $service = $serviceData['service'];
            $state = $serviceData['state'];

            if (in_array($service, array_column($scan_services, 'servico'))) {
                // Service exists
            } else {
                $descri .= "Service Missing";
                $obs = "Service Missing ";
                $this->updateDescri($client, $descri, $tResulID);
                $this->updateState($service_id, $state, $client, $obs);
            }
        }
    }

    public function serviceCompare($client, $test, $servico, $porta, $estado, $protocolo, $mensagem)
    {
        $query = "SELECT * FROM services WHERE cliente_id=? && service=? && port=? && proto=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("isss", $client, $servico, $porta, $protocolo);

        if ($statement->execute()) {
            $result = $statement->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['state'] === $estado && $row['banner'] === $mensagem) {
                    $resul['opt'] = '1';
                    $resul['id'] = $row['service_id'];
                    return $resul;
                } elseif ($row['state'] !== $estado || $row['banner'] !== $mensagem) {
                    $resul['opt'] = '2';
                    $resul['id'] = $row['service_id'];
                    return $resul;
                }
            } else {
                $resul['opt'] = '3';
                return $resul;
            }
        }
    }

    //FUNCTION TO CHANGE GATEWAY FOR TEST OK
    public function updateGatewayForIP($ip, $newGateway)
    {
        // Execute the update_gateway.php script with sudo
        $comand = 'sudo ip route add ' . $ip . ' via ' . $newGateway;
        $resul = exec($comand);
        if ($resul === null) {
            $res['code'] = '507';
            $res['tip'] = 'Execution Failure shell_exec update_gateway';
            return $res;
        } else {
            return $resul;
        }
    }

    public function resetGateway($ip)
    {
        // Execute the update_gateway.php script with sudo
        $comand = 'sudo ip route delete ' . $ip;
        $resul = exec($comand);
        if ($resul === null) {
            $res['code'] = '507';
            $res['tip'] = 'Execution Failure shell_exec reset_gateway';
            return $res;
        } else {
            return true;
        }

    }

    //SIGNAL TREAT FUNCTION OK
    public function signal_handler($sig, $frame)
    {
        $answer = strtolower(trim(fgets(STDIN)));
        if ($answer == 'y') {
            exit(3);
        }
    }

    //VERIFY IP CONNECTION  OK
    public function test_connection($ip_address)
    {
        $output = shell_exec(sprintf('ping -c 1 %s', escapeshellarg($ip_address)));
        if (strpos($output, '1 received') !== false) {
            return true;
        } else {
            return false;
        }
    }

    function validate_date($date_string)
    {
        $pattern = '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/';
        return preg_match($pattern, $date_string) === 1;
    }

    //CHECK IF HOSTNAME OR IP ARE VALID OK
    public function check_param($param)
    {
        // Check if the parameter is an IP address
        if (filter_var($param, FILTER_VALIDATE_IP)) {
            $ip_address = $param;
            return $ip_address;
        } else {
            // Parameter is a hostname
            $ip_address = gethostbyname($param);
            if ($ip_address === $param) {
                return False;
            } else {
                return $ip_address;
            }
        }
    }

    public function get_error($code, $code_array)
    {
        $array['code'] = $code;
        $array['description'] = $code_array[$code];
        $result = json_encode($array);
        return $result;
    }

    public function print_json($code, $description, $data = null, $tip = null)
    {
        $message['result']['code'] = $code;
        $message['result']['description'] = $description;
        if ($data) {
            $message['data'] = $data;
        }
        if ($tip) {
            $message['tip'] = $tip;
        }
        return json_encode($message, JSON_PRETTY_PRINT);
    }

    //END CLASS

}

?>
