#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	//require_once("includes/db.php");

	// Constants de plana
	define("TITOL","Entrada");

	// Variables de plana
	$accio = "";
	$error = "";
	
	// Funcions
	function validar_usuari($usuari,$password)
	{
		$_SESSION['usuari'] = $usuari;
		$_SESSION['password'] = $password;
		// ara comprovem usuari i password intentant establir connexió amb Oracle	
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], 'oracleps');
		if ($conn) {
			return true;
		}
		return false;
	}
	function entrar() {
		$usuari = $_POST["usuari"];
		$password = $_POST["password"];
		if (validar_usuari($usuari,$password)) {
			$_SESSION["usuari"]=$usuari;
			header("Location: menu.php");
			die();
		}
		else {
			throw new Exception("Usuari o paraula de pas no v&agrave;lid");
		}
	}
	
	// Control del fluxe de la plana
	$accio = $_POST["accio"];
	if ($accio=="entrar") {
		try {
			entrar();
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}
	}
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
    </nav>
    <div class="container">
        <div class="row text-center mt-3">
            <div class="col-3"></div>
            <div class="col-6">
                <form name="form" method="post" class="form-horizontal">
                    <fieldset class="border p-2">
                        <legend class="w-auto"><?=TITOL?></legend>
                        <div class="form-group">
                            <label for="usuari" class="sr-only">Usuari</label>
                            <input name="usuari" type="text" id="usuari" placeholder="Usuari" />
                        </div>
                        <div class="form-group">
                            <label for="password" class="sr-only">Paraula de pas</label>
                            <input name="password" type="password" id="password" placeholder="Paraula de pas" />
                        </div>
                        <div class="form-group">
                            <input type="submit" name="accio" value="entrar" class="btn btn-primary" />
                        </div>
					</fieldset>
					<?php if (strlen($error)>0) {?>
						<div class="alert alert-danger mt-2" role="alert"><?=$error?><div>
					<?php } ?>
				</form>
            </div>
            <div class="col-3"></div>
        </div>
    </div>
</body>
</html>
