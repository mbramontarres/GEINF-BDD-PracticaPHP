#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	//require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Men&uacute;");

	// Variables de plana
	$accio = "";
	$error = "";
	
	// Funcions
	
	// Control del fluxe 
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=TITOL_APLICACIO.". ".TITOL?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary" style="background-color: rgba(2,139,255,1);">
		<a class="navbar-brand" href="#">
			<?=TITOL_APLICACIO?>
		</a>
		<span class="navbar-text">
			<?=$_SESSION["usuari"]?>
			<a class="btn btn-primary border" href="entrada.php">Sortir</a>
		</span>
    </nav>
    <div class="container">
        <div class="row text-center mt-3">
            <div class="col-3"></div>
            <div class="col-6">
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="alta_vehicle.php">Alta de vehicle</a>
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="mostrar_vehicles.php">Mostrar vehicles</a>
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="compra_vehicle.php">Compra de vehicle</a>
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="alta_cursa.php">Alta de cursa</a>
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="inscriure_participants.php">Inscriure participants</a>
 				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="mostrar_participants.php">Mostrar participants</a>
 				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="entrar_temps.php">Entrada de temps dels participants</a>
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="mostrar_factures_dates.php">Mostra factures entre dates</a>
				<a type="button" class="btn btn-outline-secondary btn-lg btn-block" href="mostrar_factures_usuari.php">Mostra factures dels usuaris</a>
           </div>
            <div class="col-3"></div>
        </div>
    </div>
</body>
</html>
