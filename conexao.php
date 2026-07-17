<?php
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "AcniLuZnLuFmmKDvOGfZJjxzvlNoAuar";
$database = "railway";
$port = 42227;

// Criando a conexão
$conexao = new mysqli($host, $user, $password, $database, $port);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
