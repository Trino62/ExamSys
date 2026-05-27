<?php
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

class ConexionPDO
{
    private string $host;
    private string $user;
    private string $pass;
    private string $db;
    private string $charset = 'utf8mb4';

    public function __construct()
    {
        $host        = getenv('DB_HOST') ?: 'localhost';
        $port        = getenv('DB_PORT') ?: '3306';
        $this->host  = "$host:$port";
        $this->user  = getenv('DB_USER') ?: 'root';
        $this->pass  = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $this->db    = getenv('DB_NAME') ?: 'examen';
    }

    public function Conexion(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $this->user, $this->pass, $options);
    }
}
