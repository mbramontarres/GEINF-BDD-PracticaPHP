#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Factures entre dates");

	// Variables de plana
	$accio = "";
	$error = "";
	$data = array();
	$factures = array();
	init_form_data();
		
	// Funcions
	function init_form_data() {
		global $data;
		$data = [ 
			"data_inici" => "",
			"data_fi" => ""
			];
	}

	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
 			$data[$key] = $_POST[$key];
		}	
	}
	
	function str_to_date($str) {
		$a_str = explode('/',$str);
		return strtotime($a_str[1]."/".$a_str[0]."/".$a_str[2]);
	}

	function validate_data() {		
		global $data;
		
		if(empty($data["data_inici"])){
			return "Cal data inici";
		}
		if (!preg_match(REGEXP_DATE,$data["data_inici"])) {
			return "Data inici no vàlida. Format DD/MM/AAAA";
		}
		if(empty($data["data_fi"])){
			return "Cal data fi";
		}
		if (!preg_match(REGEXP_DATE,$data["data_fi"])) {
			return "Data fi no vàlida. Format DD/MM/AAAA";
		}
		if (str_to_date($data["data_inici"]) > str_to_date($data["data_fi"])) {
			return "La data fi ha de ser més gran o igual a la data d'inici";
		}
		
	}
	
	function load_factures() {
		global $data;
		global $error;
		global $factures;
		try {
			$factures = llista_factures_entre_dates($data["data_inici"],$data["data_fi"]);			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}
		
	}


	// Control del fluxe
	$accio = $_POST["accio"];
	

	if (strlen($error)==0 && $accio=="seleccionar" ){
		load_form_data();
		$error  = validate_data();
	}

 	if (strlen($error)==0 && strlen($data["data_inici"])>0 && strlen($data["data_fi"])>0) {
		load_factures();
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
						<form name="form-seleccio"  method="post">
							<div class="form-row">
								<div class="form-group col-md-4">
									<label for="data_inici">Data inici</label>
									<input type="text" name="data_inici" class="form-control" id="data_inici" placeholder="DD/MM/AAAA" value="<?=$data["data_inici"]?>">
								</div>
								<div class="form-group col-md-4">
									<label for="data_fi">Data fi</label>
									<input type="text" name="data_fi" class="form-control" id="data_fi" placeholder="DD/MM/AAAA" value="<?=$data["data_fi"]?>">
								</div>
							</div>
							<button type="submit" name="accio" value="seleccionar" class="btn btn-primary">Filtrar</button>
						</form>
						<?php if (strlen($error)==0 && strlen($data["data_inici"])>0 && strlen($data["data_fi"])>0) {?>
					<div class="row mt-2">
						<div class="col-12">
							<?php if (count($factures)==0) { ?>
								<div class="alert alert-danger"><?="No hi ha factures"?></div>
							<?php } else { ?>
								<table class="table table-striped">
								  <thead>
									<tr>
									  <th scope="col">Codi</th>
									  <th scope="col">Cursa</th>
									  <th scope="col">Vehicle</th>
									  <th scope="col">Propietari</th>
									  <th scope="col">Data</th>
									  <th scope="col">Temps</th>
									  <th scope="col">Cost combustible</th>
									  <th scope="col">Servei</th>
									  <th scope="col">IVA</th>
									  <th scope="col">Total</th>
									</tr>
								  </thead>
								  <tbody>
									<?php foreach ($factures as $row) { ?>
										<tr>
										  <th scope="row"><?=$row["CODI"]?></th>
										  <td><?=$row["CURSA"]?></td>
										  <td><?=$row["VEHICLE"]?></td>
										  <td><?=$row["PROPIETARI"]?></td>
										  <td><?=$row["DATA_FACTURA"]?></td>
										  <td><?=$row["TEMPS"]?></td>
										  <td><?=$row["COST_COMBUSTIBLE"]?></td>
										  <td><?=$row["PREU_SERVEI"]?></td>
										  <td><?=$row["IVA"]?>%</td>
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
		</div>
	</div>
</body>
</html>
