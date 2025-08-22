<?php
    
    require_once '../config/connection.php';
    session_start();
   

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        try {
            $connection = new Connection();
            $conn = $connection->connect();

            $sql = "SELECT * FROM usuarios WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];

                if ($user['role_id'] == 1) {
                    header("Location: ../Home/dashboard.php");
                    exit();
                } elseif ($user['role_id'] == 2) {
                    header("Location: ../Operador/index.php");
                    exit();
                } elseif ($user['role_id'] == 3) {
                    header("Location: ../Propietario/index.php");
                    exit();
                }else {
                    echo "Acceso denegado.";
                }
                exit();
            } else {
                echo "<script>
                alert('Credenciales incorrectas');
                window.location.href = '../index.php';
                </script>";
            }
        } catch (\Throwable $th) {
            echo "<script>
            alert('Error al iniciar sesión: " . addslashes($th->getMessage()) . "');
            window.location.href = '../index.php';
            </script>";
            exit();
        }
    }
?>
