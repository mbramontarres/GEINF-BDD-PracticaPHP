#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Facturació per usuari");

	// Variables de plana
	$accio = "";
	$error = "";
	$data = array();
	init_form_data();
	$usuaris = array();
	$vehicles = array();
	
	// Funcions
	function load_usuaris(){
		global $usuaris;
		global $error;
		try {
			$usuaris = llista_usuaris();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}		
	}	
	function load_vehicles(){
		global $vehicles;
		global $data;
		global $error;
 		try {
			$vehicles = llista_vehicles_facturacio($data["usuari"]);
		}
		catch (Exception $e) {
			$error = htmlentities("ERROR BDD: ".$e->getMessage());
		}
	}
	
	function init_form_data() {
		global $data;
		$data = [ 
			"usuari" => ""
			];
	}
	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
 			$data[$key] = $_POST[$key];
		}	
	}

	// Control del fluxe
	$accio = $_POST["accio"];
	
	if(strlen($error)==0){
		load_usuaris();
	}
	if (strlen($error)==0 && $accio=="seleccionar"){
		load_form_data();
	}
	if (strlen($error)==0 && strlen($data["usuari"])>0) {
		load_vehicles();
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
				<div class="row">
					<div class="col-12">
						<form name="form-seleccio_cursa" method="post">
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="usuari">Usuaris</label>
									<select class="form-control" name="usuari" onchange="javascript: this.form.submit();">
										<option value="">--- Selecciona usuari ---</option>
										<?php foreach($usuaris as $row){ ?>
										<option value="<?=$row["ALIAS"] ?>" <?=$row["ALIAS"]==$data["usuari"]? "selected" : ""?>><?=$row["ALIAS"]." - ". $row["PROPIETARI"] ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<input type="hidden" name="accio" value="seleccionar" />
						</form>
					<div>
				</div>
				<?php if (strlen($data["usuari"])!=0) {?>
					<div class="row mt-2">
						<div class="col-12">
							<?php if (count($vehicles)==0) { ?>
								<div class="alert alert-danger">No hi ha factures</div>
							<?php } else { ?>
								<table class="table table-striped">
								  <thead>
									<tr>
									  <th scope="col" colspan="2">Vehicle</th>
									  <th scope="col"># Curses</th>
									  <th scope="col">Total</th>
									</tr>
								  </thead>
								  <tbody>
									<?php foreach ($vehicles as $row) { ?>
										<tr>
										  <th scope="row"><?=$row["VEHICLE"]?></th>
										  <td><?=$row["NOM_VEHICLE"]?></td>
										  <td><?=$row["NUM_CURSES"]?></td>
										  <td><?=$row["TOTAL"]?></td>
										</tr>
									<?php } ?>
								  </tbody>
								</table>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="col-1"></div>
		</div>
		<div class="h-50 d-inline-block"></div>
    </div>
</body>
</html>
