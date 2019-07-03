#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Alta vehicle");
	
	// Variables de plana
	$accio = "";
	$error = "";
	$info = "";
	$data = array();
	init_form_data();
	$usuaris = array();
	$grupsvehicles = array();
	$combustibles = array();
	
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
	function load_grupsvehicles(){
		global $grupsvehicles;
		global $error;
		try {
			$grupsvehicles = llista_grupsvehicles();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}
	}
	
	function load_combustible(){
		global $combustibles;
		global $error;
		try {
			$combustibles = llista_combustibles();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}
		
	}
 	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
 			$data[$key] = $_POST[$key];
		}	
	}
	function init_form_data() {
		global $data;
		$data = [ 
			"descripcio" => "",
			"color" => "",
			"consum" => "",
			"preu" => "",
			"combustible" => "",
			"datacompra" => "",
			"propietari" => "",
			"grupvehicle" => ""
			];
	}
	
	function validate_data() {		
		global $data;
		
		if(empty($data["descripcio"])){
			return "Cal descripció";
		}
		if(strlen($data["descripcio"])>45){
			return "Descripcio no vàlida. Màxim 45 caràcters.";
		}
		if(empty($data["color"])){
			return "Cal color";
		}
		if(strlen($data["color"])>45){
			return "Color no vàlid. Màxim 45 caràcters.";
		}
		if(empty($data["consum"])){
			return "Cal consum";
		}
		if (!preg_match(REGEXP_DECIMAL_4_2,$data["consum"])) {
			return "Consum no vàlid.";
		}
		if(empty($data["preu"])){
			return "Cal preu";
		}
		if (!preg_match(REGEXP_DECIMAL_8_2,$data["preu"])) {
			return "Preu no vàlid.";
		}
		if(empty($data["combustible"])){
			return "Cal combustible";
		}
		if(empty($data["datacompra"])){
			return "Cal data de compra";
		}
		if (!preg_match(REGEXP_DATE,$data["datacompra"])) {
			return "Data de compra no vàlida. Format DD/MM/AAAA";
		}
		if(empty($data["propietari"])){
			return "Cal propietari";
		}
		if(empty($data["grupvehicle"])){
			return "Cal grup de vehicles";
		}
		return "";
	}
	
	// Control del fluxe
	$accio = $_POST["accio"];
	if(strlen($error)==0){
		load_combustible();
	}
	if (strlen($error)==0){
		load_usuaris();
	}
	if (strlen($error)==0){
		load_grupsvehicles();
	}
	if ($accio=="alta"){
 		load_form_data();
		$error  = validate_data();
		if (strlen($error)==0){
			try {
				inserta_vehicle($data);
				init_form_data();
				$info = "Nou vehicle enregistrat.";
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
						<form name="form" method="post">
						  <div class="form-row">
							<div class="form-group col-md-12">
							  <label for="descripcio">Descripció</label>
							  <input type="text" name="descripcio" class="form-control" id="descripcio" placeholder="Descripció" value="<?=$data["descripcio"]?>">
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-3">
								<label for="color">Color</label>
								<input type="text" name="color" class="form-control" id="color" placeholder="Color" value="<?=$data["color"]?>">
							</div>
							<div class="form-group col-md-3">
								<label for="consum">Consum</label>
								<input type="text" name="consum" class="form-control" id="consum" placeholder="Consum" value="<?=$data["consum"]?>">
							</div>
							<div class="form-group col-md-3">
								<label for="preu">Preu</label>
								<input type="text" name="preu" class="form-control" id="preu" placeholder="Preu" value="<?=$data["preu"]?>">
							</div>
							<div class="form-group col-md-3">
								<label for="combustible">Combustible</label>
								<select class="form-control" name="combustible">
									<option value="">--- Selecciona combustible ---</option>
									<?php foreach($combustibles as $row){ ?>
										<option value="<?=$row["DESCRIPCIO"] ?>" <?=$row["DESCRIPCIO"]==$data["combustible"]? "selected" : ""?>><?=$row["DESCRIPCIO"] ?></option>
									<?php } ?>
								</select>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-3">
								<label for="datacompra">Data de compra</label>
								<input type="text" name="datacompra" class="form-control" id="datacompra" placeholder="DD/MM/AAAA" value="<?=$data["datacompra"]?>">
							</div>
							<div class="form-group col-md-9">
								<label for="propietari">Propietari</label>
								<select class="form-control" name="propietari">
									<option value="">--- Selecciona propietari ---</option>
									<?php foreach($usuaris as $row){ ?>
										<option value="<?=$row["ALIAS"] ?>" <?=$row["ALIAS"]==$data["propietari"]? "selected" : ""?>><?=$row["PROPIETARI"] ?></option>
									<?php } ?>
								</select>
							</div>
							
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-12">
								<label for="grupvehicle">Grup de vehicles</label>
								<select class="form-control" name="grupvehicle">
									<option value="">--- Selecciona grup de vehicles ---</option>
									<?php foreach($grupsvehicles as $row){ ?>
										<option value="<?=$row["CODI"] ?>" <?=$row["CODI"]==$data["grupvehicle"]? "selected" : ""?>><?=$row["DESCRIPCIO"] ?></option>
									<?php } ?>
								</select>
							</div>
							
						  </div>
						  <button type="submit" name="accio" value="alta" class="btn btn-primary">Donar d'alta</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col-1"></div>
		</div>
    </div>
</body>
</html>
