<?php
	// Control de la sessió
	ini_set('session.save_path', exec("pwd") . "/tmp");	
	session_start();
	
	// Si l'usuari no ha entrat, retornem a la plana entrada.php
	if (basename($_SERVER['SCRIPT_FILENAME'])=="entrada.php"){
		unset($_SESSION["usuari"]);
	}
	else if (empty($_SESSION["usuari"])) {
		header("Location: entrada.php");
		die();
	}
	
	function logout() {
	}
?>