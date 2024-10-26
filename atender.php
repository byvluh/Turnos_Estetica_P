<?php
// Incluir la conexión a la base de datos
include 'services/dbcon.php';
$conexion = conectar();

// Obtener el token de la solicitud (por ejemplo, desde un parámetro GET o un header)
$token = isset($_GET['token']) ? $_GET['token'] : null;

// Verificar si el token es válido y corresponde al usuario con ID 2
if (!$token || !esTokenValido($token, $conexion, 2)) {
    header("Location: http://localhost/Turnos_Estetica_P/login.php");
    exit();
}

// Lógica para manejar el botón presionado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turno_numero = $_POST['turno_numero'];
    $accion = $_POST['accion'];

    if ($accion === 'Atender') {
        // Actualizar el estado del turno a atendido
        $stmt = $conexion->prepare("UPDATE turno SET estado = 3 WHERE num_turno = :turno_numero AND fecha_turno = CURDATE()");
        $stmt->bindParam(':turno_numero', $turno_numero);
        $stmt->execute();

        // Obtener los servicios para el turno atendido
        $stmt_servicios = $conexion->prepare("SELECT GROUP_CONCAT(DISTINCT s.nombre_serv SEPARATOR ', ') AS servicios 
                                              FROM ventas v 
                                              JOIN servicios s ON v.id_servicio = s.id_servicio 
                                              JOIN turno t ON v.id_turno = t.id_turno 
                                              WHERE t.num_turno = :turno_numero");
        $stmt_servicios->bindParam(':turno_numero', $turno_numero);
        $stmt_servicios->execute();
        $resultado_servicios = $stmt_servicios->fetch(PDO::FETCH_ASSOC);
        $servicio_atendido = $resultado_servicios['servicios'];
    } elseif ($accion === 'Pasar') {
        // Pasar el turno (cambiar el estado)
        $stmt = $conexion->prepare("UPDATE turno SET estado = 2 WHERE num_turno = :turno_numero AND fecha_turno = CURDATE()");
        $stmt->bindParam(':turno_numero', $turno_numero);
        $stmt->execute();
    }

    // Redireccionar a la misma página para mostrar el siguiente turno
    header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . $token);
    exit;
}

// Obtener el siguiente turno en espera
$stmt = $conexion->prepare("SELECT t.num_turno 
                            FROM turno t 
                            WHERE t.estado = 1 AND t.fecha_turno = CURDATE() 
                            ORDER BY t.id_turno ASC 
                            LIMIT 1");
$stmt->execute();
$turno_actual = $stmt->fetch(PDO::FETCH_ASSOC);

// Inicializar las variables del turno actual
$turno_actual['numero'] = isset($turno_actual['num_turno']) ? $turno_actual['num_turno'] : 'No hay turnos';

// Obtener todos los servicios asociados al turno actual
$servicios = '';
if ($turno_actual['numero'] !== 'No hay turnos') {
    $stmt_servicios = $conexion->prepare("SELECT GROUP_CONCAT(DISTINCT s.nombre_serv SEPARATOR ', ') AS servicios 
                                          FROM ventas v 
                                          JOIN servicios s ON v.id_servicio = s.id_servicio 
                                          JOIN turno t ON v.id_turno = t.id_turno 
                                          WHERE t.num_turno = :turno_numero");
    $stmt_servicios->bindParam(':turno_numero', $turno_actual['numero']);
    $stmt_servicios->execute();
    $resultado_servicios = $stmt_servicios->fetch(PDO::FETCH_ASSOC);
    
    $servicios = $resultado_servicios['servicios']; // Obtener todos los nombres de los servicios en una cadena
}

// Si no hay turnos, establecer valores predeterminados
if (!$turno_actual) {
    $turno_actual = ['numero' => 'No hay turnos'];
}

// Función para verificar si el token es válido
function esTokenValido($token, $conexion, $id_usuario) {
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario AND token = :token");
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turno Actual - Recepcionista</title>
    <link rel="stylesheet" type="text/css" href="styles/styles2turnos.css">
    <link rel="stylesheet" type="text/css" href="styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="svgcont">
    <a href="menu.php" class="">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svgmenu">
            <path d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z" />
        </svg>
    </a>
</div>

<div class="contenedor-turno">
    <div class="turno-actual">TURNO</div>
    <div class="numero-turno"><?php echo $turno_actual['numero']; ?></div>
    
    <?php if (!empty($servicios)): ?>
        <div class="servicio">Servicios: <?php echo $servicios; ?></div>
    <?php else: ?>
        <div class="servicio">No hay servicios asociados</div>
    <?php endif; ?>

    <form class="botones" action="" method="post">
        <input type="hidden" name="turno_numero" value="<?php echo $turno_actual['numero']; ?>">
        <div class="botones">
            <button type="submit" name="accion" value="Pasar" class="boton">Saltar</button>
            <button type="submit" name="accion" value="Atender" class="boton">Atender</button>
        </div>
    </form>
</div>

<script>
    // Actualizar la vista cada 5 segundos
    setInterval(function() {
        location.reload();
    }, 5000);
</script>

</body>
</html>
