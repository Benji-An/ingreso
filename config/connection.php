<?php

    class Connection {
        private $host = 'localhost';
        private $user = 'root';
        private $password = '';
        private $dbname = 'control_acceso';

        public function connect() {
            try {
                $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                return new PDO($dsn, $this->user, $this->password, $options);
                
            } catch (\Throwable $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }

?>