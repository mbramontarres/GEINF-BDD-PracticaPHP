#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Mostrar vehicles");

	// Variables de plana
	$accio = "";
	$error = "";
	$data = array();
	
	// Funcions
	function load_data(){
		global $data;	
		global $error;
 		try {
			$data = llista_vehicles();
		}
		catch (Exception $e) {
			$error = htmlentities("ERROR BDD: ".$e->getMessage());
		}
	}
	// Control del fluxe
	load_data();
	if (strlen($error)==0 && count($data)==0) {
		$error =  htmlentities("No hi ha cap vehicle.");
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
		<span class="navbar-text" s>
			<?=$_SESSION["usuari"]?>
			<a class="btn btn-primary border" href="entrada.php">Sortir</a>
		</span>
    </nav>
    <div class="container mt-4" >
		<div class="row">
			<div class="col-1"></div>
			<div class="col-9">
				<div class="row">
					<div class="col-12">
						<h2 style="color:rgba(2,139,255,1); border-bottom: 2px solid rgba(2,139,255,1);"><?=TITOL?></h2>
					</div>
				</div>
				<div class="row"> 
					<div class="col-10">
						<?php if (strlen($error)>0) { ?>
							<div class="alert alert-danger"><?=$error?></div>
						<?php } ?>
					</div>
					<div class ="col-2" style="text-align: right;">
						<a type="button" class="btn btn-outline-secondary btn-sm" href="menu.php">Enrere</a>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-12">
						<table class="table table-striped">
						  <thead>
							<tr>
							  <th scope="col">Codi</th>
							  <th scope="col">Descripció</th>
							  <th scope="col">Color</th>
							  <th scope="col">Consum</th>
							  <th scope="col">Propietari</th>
							  <th scope="col">Fotografia</th>
							</tr>
						  </thead>
						  <tbody>
							<?php foreach ($data as $row) { ?>
								<tr>
								  <th scope="row"><?=$row["CODI"]?></th>
								  <td><?=$row["DESCRIPCIO"]?></td>
								  <td><?=$row["COLOR"]?></td>
								  <td><?=$row["CONSUM"]?></td>
								  <td><?=$row["PROPIETARI"]?></td>
								  <td><img <img src="data:image/png;base64,<?=$row['FOTO']?>" class="rounded" style="width: 70px;" alt="..."></td>
								</tr>
							<?php } ?>
						  </tbody>
						</table>
						</div>
				</div>
			</div>
			<div class="col-1"></div>
		</div>
    </div>
</body>
</html>
