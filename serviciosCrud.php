<?php
session_start();

// Incluir la conexión a la base de datos
include("services/dbcon.php");
$conexion = conectar();

// Verificar si el usuario está autenticado y es el id_usuario = 1
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_usuario'] != 1) {
    header("Location: http://localhost/Turnos_Estetica/login.php");
    exit();
}

// Mensaje de error
$mensaje_error = '';

// Verificar si se está agregando un nuevo servicio
if (isset($_POST['add_service'])) {
    $nombre_serv = $_POST['nombre_serv'];
    $costo = $_POST['costo'];

    if (!empty($nombre_serv) && !empty($costo)) {
        $stmt = $conexion->prepare("INSERT INTO servicios (nombre_serv, costo, activo) VALUES (:nombre_serv, :costo, 1)");
        $stmt->bindParam(':nombre_serv', $nombre_serv);
        $stmt->bindParam(':costo', $costo);
        $stmt->execute();
    }
}

// Verificar si se está editando un servicio
if (isset($_POST['edit_service'])) {
    $id_servicio = $_POST['id_servicio'];
    $nombre_serv = $_POST['nombre_serv'];
    $costo = $_POST['costo'];
    $activo = isset($_POST['activo']) ? 1 : 0; // Obtener estado activo

    if (!empty($nombre_serv) && !empty($costo)) {
        $stmt = $conexion->prepare("UPDATE servicios SET nombre_serv = :nombre_serv, costo = :costo, activo = :activo WHERE id_servicio = :id_servicio");
        $stmt->bindParam(':nombre_serv', $nombre_serv);
        $stmt->bindParam(':costo', $costo);
        $stmt->bindParam(':activo', $activo);
        $stmt->bindParam(':id_servicio', $id_servicio);
        $stmt->execute();
    }
}

// Verificar si se está eliminando un servicio
if (isset($_GET['delete'])) {
    $id_servicio = $_GET['delete'];

    // Comprobar si hay registros asociados al servicio
    $stmtVerificar = $conexion->prepare("SELECT COUNT(*) FROM ventas WHERE id_servicio = :id_servicio");
    $stmtVerificar->bindParam(':id_servicio', $id_servicio);
    $stmtVerificar->execute();
    $registros = $stmtVerificar->fetchColumn();

    if ($registros > 0) {
        $mensaje_error = '<div style="color: red;">No se puede borrar el servicio porque tiene registros asociados.</div>';
    } else {
        // Eliminar el servicio de forma permanente
        $stmt = $conexion->prepare("DELETE FROM servicios WHERE id_servicio = :id_servicio");
        $stmt->bindParam(':id_servicio', $id_servicio);
        $stmt->execute();
    }
}

// Obtener todos los servicios, incluyendo los inactivos
$stmt = $conexion->prepare("SELECT * FROM servicios");
$stmt->execute();
$servicios = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Servicios</title>
    <link rel="stylesheet" type="text/css" href="styles/styles.css">
</head>

<body class="body-log">

    <h1>Servicios Registrados</h1>

    <div class="contprinc">

        <!-- Mostrar mensaje de error -->
        <?php if (!empty($mensaje_error)) echo $mensaje_error; ?>

        <!-- Formulario para Crear o Editar Servicio -->
        <div class="contform">
            <form action="" method="POST">
                <input type="hidden" name="id_servicio" id="id_servicio"> <!-- Campo oculto para el ID del servicio -->
                <label for="nombre_serv">Nombre del Servicio:</label>
                <input type="text" name="nombre_serv" id="nombre_serv" required>
                <br><br>
                <label for="costo">Costo:</label>
                <input type="number" name="costo" id="costo" step="0.01" required>
                <br><br>
                <label>
                    <input type="checkbox" name="activo" id="activo" value="1">
                    Activo
                </label>
                <br><br>

                <div class="btncont">
                    <!-- Botón de agregar o editar -->
                    <button type="submit" name="add_service" class="btn">Agregar Servicio</button>
                    <button type="submit" name="edit_service" class="btn">Actualizar Servicio</button>
                </div>
            </form>
        </div>

        <!-- Tabla de Servicios -->
        <div class="contab">
            <table border="1" class="tbl">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Costo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $servicio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($servicio['nombre_serv']); ?></td>
                            <td class="cost">$<?php echo number_format($servicio['costo'], 2); ?></td>
                            <td class="cost"><?php echo $servicio['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                            <td class="act">
                                <button onclick="editService(<?php echo $servicio['id_servicio']; ?>, '<?php echo addslashes($servicio['nombre_serv']); ?>', <?php echo $servicio['costo']; ?>, <?php echo $servicio['activo']; ?>)">Editar</button>
                                <a href="?delete=<?php echo $servicio['id_servicio']; ?>" onclick="return confirm('¿Estás seguro de eliminar este servicio?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="svgcont">
        <a href="logout.php">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svgmenu"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0z" />
            </svg>
        </a>
    </div>
    <script>
        function editService(id_servicio, nombre_serv, costo, activo) {
            // Rellenar el formulario con los datos del servicio
            document.getElementById('id_servicio').value = id_servicio;
            document.getElementById('nombre_serv').value = nombre_serv;
            document.getElementById('costo').value = costo;

            // Marcar el checkbox si está activo
            document.getElementById('activo').checked = activo;

            // Cambiar el botón de agregar a editar
            document.querySelector('button[name="add_service"]').style.display = 'none';
            document.querySelector('button[name="edit_service"]').style.display = 'inline';
        }
    </script>

</body>

</html>