#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Compra de vehicle");
	
	// Variables de plana
	$accio = "";
	$error = "";
	$info = "";
	$data = array();
	init_form_data();
	$vehicles = array();
	$usuaris = array();
	
	// Funcions
	function init_form_data() {
		global $data;
		$data = [ 
			"vehicle" => "",
			"comprador" => "",
			"datavenda" => "",
			"preu" => "",
			"codi" => ""
			];
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
	function get_usuari($alias) {
		global $usuaris;
		foreach($usuaris as $row) {
			if ($row["ALIAS"]==$alias) {
				return $row;
			}
		}
		return null;
	}
	function get_vehicle($codi) {
		global $vehicles;
		foreach($vehicles as $row) {
			if ($row["CODI"]==$codi) {
				return $row;
			}
		}
		return null;
	}
 	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
 			$data[$key] = $_POST[$key];
		}	
	}
	function validate_data() {	
		global $data;
		if(empty($data["comprador"])){
			return "Cal entrar comprador";
		}		
		if(empty($data["datavenda"])){
			return "Cal entrar data venda";
		}
		if (!preg_match(REGEXP_DATE,$data["datavenda"])) {
			return "Data venda no vàlida. Format dd/mm/aaaa.";
		}
		if(empty($data["preu"])){
			return "Cal entrar preu";
		}
		if (!preg_match(REGEXP_DECIMAL_8_2,$data["preu"])) {
			return "Preu de compra no vàlid.";
		}
		// Validem que l'usuari tingui prou saldo
		$usuari = get_usuari($data["comprador"]);
		if ($usuari["SALDO"]<$data["preu"]) {
			return "L'usuari no té prou saldo (" . $usuari["SALDO"] . ") per comprar el vehicle.";
		}
		if(empty($data["codi"])){
			return "Cal entrar el nou codi";
		}
		if (strlen($data["codi"])>10) {
			return "El codi no pot ser més llarg de 10 caràcters.";
		}
		// Validem que el codi proposat no existeixi
		if (count(obtenir_vehicle($data["codi"]))>0) {
			return "Ja existeix un altre vehicle amb el mateix codi.";
		}
		return "";
	}
	
	// // Control del fluxe
	$accio = $_POST["accio"];
	if(strlen($error)==0){
		load_vehicles();
	}
	if (strlen($error)==0){
		load_usuaris();
	}
	if (strlen($error)==0){
		load_form_data();
	}
	if ($accio=="comprar") {
 		$error  = validate_data();
		if (strlen($error)==0){
			try {
				compra_vehicle($data["vehicle"],$data["comprador"],$data["datavenda"],$data["preu"],$data["codi"]);
				init_form_data();
				load_vehicles();
				$info = "Compra efectuada.";
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
						<form name="form-seleccio_vehicle" method="post">
						  <div class="form-row">
							<div class="form-group col-md-12">
								<label for="vehicle">Vehicle </label>
								<select class="form-control" name="vehicle" onchange="javascript: this.form.submit();">
									<option value="">--- Selecciona ---</option>
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
								<input type="hidden" name="accio" value="seleccionar" />
							</div>							
						  </div>
						</form>
					</div>
				</div>
				<?php $dadesvehicle=get_vehicle($data["vehicle"]);?>
				<?php if ($dadesvehicle!=null) { ?>
				<div class="row mt-2">
					<div class="col-12">
						<form method="post">
							<fieldset class="border p-2" disabled>
								<legend class="w-auto">Dades del vehicle</legend>
								<div class="form-row">
									<div class="form-group col-md-3">
									  <label>Codi</label>
									  <input type="text" class="form-control" value="<?=$dadesvehicle["CODI"]?>">
									</div>
									<div class="form-group col-md-6">
									  <label>Descripció</label>
									  <input type="text" class="form-control" value="<?=$dadesvehicle["DESCRIPCIO"]?>">
									</div>
									<div class="form-group col-md-3">
									  <label>Color</label>
									  <input type="text" class="form-control" value="<?=$dadesvehicle["COLOR"]?>">
									</div>
								</div>
								<div class="form-row">
									<div class="form-group col-md-3">
									  <label>Consum</label>
									  <input type="text" class="form-control" value="<?=$dadesvehicle["CONSUM"]?>">
									</div>
									<div class="form-group col-md-6">
									  <label>Propietari</label>
									  <input type="text" class="form-control" value="<?=$dadesvehicle["NOMPROPIETARI"]?>">
									</div>
									<div class="form-group col-md-3">
									  <label>Preu</label>
									  <input type="text" class="form-control" value="<?=$dadesvehicle["PREU"]?>">
									</div>
								</div>
							</fieldset>
							<fieldset class="border p-2" >
								<legend class="w-auto">Dades de la compra</legend>
								<div class="form-row">
									<div class="form-group col-md-6">
										<label>Comprador</label>
										<select class="form-control" name="comprador">
											<option value="">--- Selecciona ---</option>
											<?php foreach($usuaris as $row){ ?>
												<?php if ($row["ALIAS"]!=$dadesvehicle["PROPIETARI"]) { ?>
													<option value="<?=$row["ALIAS"] ?>" <?=$row["ALIAS"]==$data["comprador"]? "selected" : ""?>><?=$row["PROPIETARI"]." (" .$row["SALDO"] .")" ?></option>
												<?php } ?>
											<?php } ?>
										</select>
									</div>
									<div class="form-group col-md-2">
									  <label>Data venda</label>
									  <input type="text" name="datavenda" class="form-control" value="<?=$data["datavenda"]?>">
									</div>
									<div class="form-group col-md-2">
									  <label>Preu</label>
									  <input type="text" name="preu" class="form-control" value="<?=$data["preu"]?>">
									</div>
									<div class="form-group col-md-2">
									  <label>Nou codi</label>
									  <input type="text" name="codi" class="form-control" value="<?=$data["codi"]?>">
									</div>

								</div>
							</fieldset>
							<input type="hidden" name="vehicle" value="<?=$data["vehicle"]?>">
							<button type="submit" name="accio" value="comprar" class="btn btn-primary mt-2">Comprar</button>
						</form>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="col-1"></div>
		</div>
    </div>
</body>
</html>
