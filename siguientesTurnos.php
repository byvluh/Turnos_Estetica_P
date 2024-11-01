<?php
// Iniciar sesión
session_start();

// Incluir la conexión a la base de datos
include 'services/dbcon.php';
$conexion = conectar();

// Verificar si la sesión id_usuario está establecida y si su valor es 2
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_usuario'] != 2) {
    // Si la sesión no es válida, redirigir al login
    header("Location: /Turnos_Estetica_P/login.php");
    exit();
}

// Obtener el turno atendido y servicio atendido directamente desde la base de datos
$sql_atendido = "SELECT t.num_turno, GROUP_CONCAT(s.nombre_serv SEPARATOR ', ') AS servicios
                 FROM turno t 
                 JOIN ventas v ON t.id_turno = v.id_turno 
                 JOIN servicios s ON v.id_servicio = s.id_servicio 
                 WHERE t.estado = 3 AND t.fecha_turno = CURDATE() 
                 GROUP BY t.num_turno 
                 ORDER BY t.num_turno DESC 
                 LIMIT 1";

$stmt_atendido = $conexion->query($sql_atendido);
$turno_atendido = $stmt_atendido->fetch(PDO::FETCH_ASSOC);

// Obtener todos los turnos del día actual que están en espera (estado 1)
$sql_turnos = "SELECT t.num_turno, GROUP_CONCAT(s.nombre_serv SEPARATOR ', ') AS servicios 
               FROM turno t 
               JOIN ventas v ON t.id_turno = v.id_turno 
               JOIN servicios s ON v.id_servicio = s.id_servicio 
               WHERE t.estado = 1 AND t.fecha_turno = CURDATE() 
               GROUP BY t.num_turno 
               ORDER BY t.num_turno ASC";

$result_turnos = $conexion->query($sql_turnos);
$turnos = [];
while ($row = $result_turnos->fetch(PDO::FETCH_ASSOC)) {
    $turnos[] = $row;
}

// Cerrar conexión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siguientes Turnos</title>
    <link rel="stylesheet" type="text/css" href="styles/styles2turnos.css">
    <link rel="stylesheet" type="text/css" href="styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .turno-actual {
            display: none; /* Ocultar por defecto */
        }
    </style>
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
        <div class="turno-atendido">TURNO A ATENDER:</div>

        <?php if ($turno_atendido): ?>
            <div class="num-turno">
                <?php echo $turno_atendido['num_turno']; ?>
            </div>
            <div class="serv-turno">
                <?php echo $turno_atendido['servicios']; ?>
            </div>
            <script>
                // Mostrar el turno por 30 segundos
                setTimeout(function() {
                    document.querySelector('.turno-atendido').style.display = 'none';
                    document.querySelector('.turno-actual').style.display = 'block';
                    document.querySelector('.turno-actual').textContent = 'No hay turnos ni servicios disponibles.';
                }, 15000); // 30 segundos
            </script>
        <?php endif; ?>

        <div class="siguientes-container">
        <?php if (count($turnos) > 0): ?>
            <div class="despues">Siguientes</div>
            <div class="siguientes">
                <?php foreach ($turnos as $index => $turno): ?>
                    <div class="siguiente-turno <?php echo $index === 2 ? 'highlight' : ''; ?>">
                        <div class="numero"><?php echo $turno['num_turno']; ?></div>
                        <?php if (!empty($turno['servicios'])): ?>
                            <div class="servicio"><?php echo $turno['servicios']; ?></div>
                        <?php else: ?>
                            <div class="servicio">No hay servicios asociados</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <br>
            <div class="numero-turno">No hay turnos en espera</div>
        <?php endif; ?>
    </div>
    </div>

    <script>
        // Actualizar la vista cada 5 segundos
        setInterval(function() {
            location.reload();
        }, 5000);
    </script>

</body>
</html>
