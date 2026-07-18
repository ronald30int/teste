<?php
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "kuijjqJlSUMnqseiUNjuITHvYsrOeUlH@tokaido";
$database = "railway";
$port = 59152;

// Criando a conexão
$conexao = new mysqli($host, $user, $password, $database, $port);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
