<?php
$host = "localhost";
$usuario_db = "root";
$senha_db = "";
$nome_db = "sistema_entregas";

$conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}
?>