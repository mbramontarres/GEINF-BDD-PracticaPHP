#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Inscriure participants");
	
	// Variables de plana
	$accio = "";
	$error = "";
	$info = "";
	$data = array();
	$curses = array();
	$vehicles = array();
	init_form_data();
	
	// Funcions
	function load_curses(){
		global $curses;
		global $error;
		try {
			$curses = llista_curses_inscripcio_oberta();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}		
	}
	function load_vehicles(){
		global $vehicles;
		global $error;
		try {
			$vehicles = llista_vehicles_per_propietari();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}
	}
 	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
			if (array_key_exists($key,$_POST)) {
				$data[$key] = $_POST[$key];
			}
		}
	}
	function init_form_data() {
		global $data;
		$data = [ 
			"cursa" => "",
			"vehicles" => array(),
			"personatges" => array(),
			"inicireal" => ""
			];
	}	
	function validate_data() {
		global $data;
		if(empty($data["inicireal"])){
			return "Cal entrar la data d'inici real";
		}
		if (!preg_match(REGEXP_DATE,$data["inicireal"])) {
			return "Data d'inici real no v&agrave;lida. Format dd/mm/aaaa.";
		}
		if (count($data["vehicles"])==0) {
			return "Cal que inscriguis almenys un vehicle.";
		}
		foreach($data["personatges"] as $row) {
			if (empty($row)) {
				return "Tens almenys un vehicle sense personatge assignat.";
			}
		}
		return "";
	}
	function validate_afegir($codi) {
		global $data;
		$vehicle = obtenir_vehicle($codi)[0];
		// Validem que el vehicle no estigui inscrit
		if (in_array($codi,$data["vehicles"])) {
			return "El vehicle \"".$vehicle["DESCRIPCIO"]."\" ja esta incrit";
		}
		// Validem que l'usuari tingui saldo
		$cursa = obtenir_cursa($data["cursa"])[0];
		if ($vehicle["SALDOPROPIETARI"]<$cursa["INSCRIPCIO"]) {
			return "El propietari del vehicle \"".$vehicle["DESCRIPCIO"]."\" no t&eacute; prou saldo (" .$vehicle["SALDOPROPIETARI"].
				") per pagar el preu d'inscripci&oacute; (".$cursa["INSCRIPCIO"].")";
		}
		// Validem que el mateix usuari no hagi inscrit un altre vehicle
		foreach($data["vehicles"] as $row) {
			$vehicle2 = obtenir_vehicle($row)[0];
			if ($vehicle2["PROPIETARI"]==$vehicle["PROPIETARI"]) {
				return "El propietari del vehicle \"".$vehicle["DESCRIPCIO"]."\" ja ha inscrit un altre vehicle.";
			}
		}
		return "";
	}
	// Control del fluxe
	$accio = $_POST["accio"];

	if(strlen($error)==0){
		load_curses();
	}
	if(strlen($error)==0){
		load_vehicles();
	}
	if (strlen($error)==0){
		load_form_data();
	}
	if (strlen($error)==0 && $accio=="afegir"){
		if (strlen($_POST["vehicle"])>0) {
			$error = validate_afegir($_POST["vehicle"]);
			if (strlen($error)==0) {
				array_push($data["vehicles"],$_POST["vehicle"]);
			}
		}
	}
	if (strlen($error)==0 && substr($accio,0,9)=="esborrar_"){
		array_splice($data["vehicles"],explode("_",$accio)[1],1);
	}
	if (strlen($error)==0 && $accio=="tancar") {
		$error=validate_data();
		if (strlen($error)==0) {
			try {
				tancar_inscripcio($data["cursa"],$data["inicireal"],$data["vehicles"],$data["personatges"]);
				init_form_data();
				load_curses();
				$info = "Inscripci&oacute; tancada.";
			}
			catch (Exception $e) {
				$error = $e->getMessage();
			} 
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
						<?php } else if (strlen($info)>0) { ?>
							<div class="alert alert-success"><?=$info?></div>	
						<?php } ?>
					</div>
					<div class ="col-2" style="text-align: right;">
						<a type="button" class="btn btn-outline-secondary btn-sm" href="menu.php">Enrere</a>
					</div>
				</div>
				<div class="row mt-2">
					<div class="col-12">
						<form name="form-seleccio_cursa" method="post">
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="cursa">Cursa</label>
									<select class="form-control" name="cursa" onchange="javascript: this.form.submit();">
										<option value="">--- Selecciona cursa ---</option>
										<?php foreach($curses as $row){ ?>
											<option value="<?=$row["CODI"] ?>" <?=$row["CODI"]==$data["cursa"]? "selected" : ""?>><?=$row["NOM"] ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<input type="hidden" name="accio" value="seleccionar" />
						</form>
					</div>
					<?php if (strlen($data["cursa"])>0) { ?>
					<div class="col-12">
						<form method="post">
							<div class="form-row" style="background-color: #F2F2F2; padding-top: 10px;">
								<div class="form-group col-md-8">
									<select class="form-control" name="vehicle">
										<option value="">--- Selecciona vehicle ---</option>
										<?php for($i=0;$i<count($vehicles);$i++){ ?>
											<?php if ($i==0 || $vehicles[$i]["PROPIETARI"]!=$vehicles[$i-1]["PROPIETARI"]) {?>
												<optgroup label="<?=$vehicles[$i]["PROPIETARI"]." - ".$vehicles[$i]["NOMPROPIETARI"] ?>">
											<?php } ?>
											<option value="<?=$vehicles[$i]["CODI"] ?>" <?=$vehicles[$i]["CODI"]==$data["vehicle"]? "selected" : ""?>><?=$vehicles[$i]["CODI"]." - ".$vehicles[$i]["DESCRIPCIO"] ?></option>
											<?php if ($i==count($vehicles)-1 || $vehicles[$i]["PROPIETARI"]!=$vehicles[$i+1]["PROPIETARI"]) {?>
												</optgroup>
											<?php } ?>
										<?php } ?>
									</select>
								</div>
								<div class="form-group col-md-4">
									<input type="hidden" name="cursa" value="<?=$data["cursa"]?>">
									<button type="submit" name="accio" value="afegir" class="btn btn-outline-secondary btn-sm">Afegir vehicle</button>
								</div>
							</div>
							<?php if(count($data["vehicles"])>0) { ?>
							<table class="table table-striped">
							  <thead>
								<tr>
								  <th scope="col">Usuari</th>
								  <th scope="col">Vehicle</th>
								  <th scope="col">Personatge</th>
								  <th scope="col">&nbsp;</th>
								</tr>
							  </thead>
							  <tbody>
							  <?php for($i=0;$i<count($data["vehicles"]);$i++) {?>
									<?php 
										$vehicle=obtenir_vehicle($data["vehicles"][$i])[0];
										$personatges = llista_personatges_usuari($vehicle["PROPIETARI"]);
									?>
									<tr>
									  <td>
										<?=$vehicle["NOMPROPIETARI"]?>
									  </td>
									  <td>
											<?=$vehicle["DESCRIPCIO"]?>
											<input type="hidden" name="vehicles[<?=$i?>]" value="<?=$data["vehicles"][$i]?>" >
									  </td>
									  <td>
										<select class="form-control" name="personatges[<?=$i?>]" >
											<option value="">--- Selecciona ---</option>
											<?php foreach($personatges as $row){ ?>
												<option value="<?=$row["ALIAS"] ?>" <?=$row["ALIAS"]==$data["personatges"][$i]? "selected" : ""?>><?=$row["ALIAS"] ?></option>
											<?php } ?>
										</select>	
									  </td>
									  <td style="text-align: right;">
										<button type="submit" name="accio" value="esborrar_<?=$i?>" class="btn btn-outline-secondary btn-sm">Esborrar</button>
									  </td>
									</tr>
								<?php } ?>
							  </tbody>
							</table>
							<?php } ?>
							<div class="form-row" style="background-color: #F2F2F2; padding-top: 10px;">
								<div class="form-group col-md-6">
									<label>Data d'inici real</label>
									<input type="text" name="inicireal" value="<?=$data["inicireal"]?>" placeholder="DD/MM/AAAA">
								</div>
								<div class="form-group col-md-6">
									<button type="submit" class="form-control btn btn-primary" name="accio" value="tancar">Guardar participants i tancar inscripci&oacute;</button>
								</div>
							</div>

						</form>							
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="col-1"></div>
		</div>
    </div>
</body>
</html>
