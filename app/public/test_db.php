<?php
require_once '../../config/database.php';

try {
    $db = new Database();
    echo "Conexão com banco: OK<br>";
    
    $result = $db->query("SELECT 1 as test");
    echo "Query teste: OK<br>";
    
    if (is_array($result)) {
        echo "Resultado (array): " . json_encode($result) . "<br>";
    } else {
        $row = $result->fetch_assoc();
        echo "Resultado (mysqli): " . json_encode($row) . "<br>";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
?>