<?php
// Iniciar sesión
session_start();

// Incluir la conexión a la base de datos
include '../services/dbcon.php';
$conexion = conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener valores del formulario
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    if (empty($usuario) || empty($password)) {
        echo '<div class="alerta">Por favor, ingresa todos los campos.</div>';
    } else {
        // Consulta para verificar el usuario y la contraseña
        $stmt = $conexion->prepare("SELECT id_usuario, password, id_rol FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioEncontrado) {
            // Verificar la contraseña
            if (hash('sha256', $password) === $usuarioEncontrado['password']) {
                // Login exitoso, establecer la sesión
                $_SESSION['id_usuario'] = $usuarioEncontrado['id_usuario'];
                $_SESSION['id_rol'] = $usuarioEncontrado['id_rol'];


                // Redirigir según el rol
                if ($usuarioEncontrado['id_rol'] == 1) {
                    header("Location: /Turnos_Estetica_P/serviciosCrud.php");
                } elseif ($usuarioEncontrado['id_rol'] == 2) {
                    header("Location: /Turnos_Estetica_P/menu.php");
                } else {
                    header('Location: /Turnos_Estetica_P/login.php');
                }
                exit; // Asegurarse de que el código se detenga aquí
            } else {
                echo '<div class="alerta">Contraseña incorrecta.</div>';
            }
        } else {
            echo '<div class="alerta">El usuario no existe.</div>';
        }
    }
}
?>
