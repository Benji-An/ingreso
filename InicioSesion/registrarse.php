<?php
    require_once '../config/connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role_id = $_POST['role_id'];

        try {
            $connection = new Connection();
            $conn = $connection->connect();

            $sql = "INSERT INTO usuarios (username, password, role_id) VALUES (:username, :password, :role_id)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':role_id' => $role_id
            ]);

            echo "<script>
            alert('Registro exitoso');
            window.location.href = '../index.php';
            </script>";
        } catch (PDOException $e) {
            echo "<script>
            alert('Error al registrar usuario: " . addslashes($e->getMessage()) . "');
            window.location.href = '../registrarse.php';
            </script>";
        }
    }

