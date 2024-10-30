<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_usuario'] !== 2) {
	$mensaje = '<div class="alerta">No tienes permiso para acceder a esta página. Serás redirigido al login.</div>';
	header("Location: /Turnos_Estetica_P/login.php");
	exit();
}
?>
.
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Controlador de Turnos</title>
	<link rel="stylesheet" href="styles/styles.css">
</head>

<body class="body-log">
	<div class="logo-circulo">
		<img src="assets/imgs/eplogoblanco.png" alt="Logo">
	</div>

	<div class="contenedor-principal">
		<h1>Menú de opciones</h1>

		<p>Bienvenido: recep1</p> <!-- Mensaje de bienvenida -->

		<div>
			<a href="registroCli.php">Registro de Cliente y Servicios</a>
		</div>
		<br>
		<div>
			<a href="siguientesTurnos.php">Visualizador de Turnos</a>
		</div>
		<br>

		<div>
			<a href="atender.php">Atender</a>
		</div>
		<br>
		<div class="svglog">
			<a href="logout.php">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svglogout"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
					<path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0z" />
				</svg>
			</a>
		</div>
		<br>

	</div>


	<script src="js/funcionesGenerales.js"></script>

	<script>
		agregarEvento(window, 'load', iniciarReset, false);

		function iniciarReset() {

			var resetear = document.getElementById('reset');
			agregarEvento(resetear, 'click', function(e) {

				if (e) {

					e.preventDefault();

					id = e.target.id;

				}

				var datos = "registrar=reset-turnos";

				funcion = procesarReseteo;
				fichero = "consultas/registrar.php";

				conectarViaPost(funcion, fichero, datos);

			}, false);

			function procesarReseteo() {

				if (conexion.readyState == 4) {

					var data = JSON.parse(conexion.responseText);

					if (data.status == "correcto") {

						alert(data.mensaje);

					} else {

						console.log(data.mensaje);

					}

				} else {

					console.log('cargando');
				}

			}

		}
	</script>


</body>

</html>