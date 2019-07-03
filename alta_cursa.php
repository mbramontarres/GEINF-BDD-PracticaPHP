#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Alta cursa");
	
	// Variables de plana
	$accio = "";
	$error = "";
	$info = "";
	$data = array();
	init_form_data();
	$cursa = array();
	
	// Funcions
	function load_cursa(){
		global $cursa;
		global $error;
		try {
			$cursa = llista_curses();			
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
			"codi" => "",
			"nom" => "",
			"premi" => "",
			"inscripcio" => "",
			"iniciprevist" => "",
			];
	}
	
	function validate_data() {		
		global $data;
		
		if(empty($data["codi"])){
			return "Cal codi";
		}
		if(strlen($data["codi"])>15){
			return "Codi no vàlid. Màxim 15 caràcters.";
		}
		if(empty($data["nom"])){
			return "Cal nom";
		}
		if(strlen($data["nom"])>45){
			return "Nom no vàlid. Màxim 45 caràcters.";
		}
		if(empty($data["premi"])){
			return "Cal premi";
		}
		if (!preg_match(REGEXP_NATURAL,$data["premi"])) {
			return "Premi no vàlid.";
		}
		if(empty($data["inscripcio"])){
			return "Cal inscripcio";
		} 
		if (!preg_match(REGEXP_DECIMAL_5_2,$data["inscripcio"])) {
			return "Inscripció no vàlida.";
		}
		if(empty($data["iniciprevist"])){
			return "Cal data prevista";
		}
		if (!preg_match(REGEXP_DATE,$data["iniciprevist"])) {
			return "Data prevista no vàlida. Format DD/MM/AAAA";
		}
	}
	
	// Control del fluxe
 	$accio = $_POST["accio"];
 	if(strlen($error)==0){
		load_cursa();
	} 
	if ($accio=="alta"){
 		load_form_data();
		$error  = validate_data();
		if (strlen($error)==0){
			try {
				inserta_cursa($data);
				init_form_data();
				$info = "Nova cursa enregistrada."; 
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
							<div class="form-group col-md-4">
							  <label for="codi">Codi</label>
							  <input type="text" name="codi" class="form-control" id="codi" placeholder="Codi" value="<?=$data["codi"]?>">
							</div>
							<div class="form-group col-md-8">
								<label for="nom">Nom</label>
								<input type="text" name="nom" class="form-control" id="nom" placeholder="Nom" value="<?=$data["nom"]?>">
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-3">
								<label for="premi">Premi</label>
								<input type="text" name="premi" class="form-control" id="premi" placeholder="Premi" value="<?=$data["premi"]?>">
							</div>
							<div class="form-group col-md-3">
								<label for="inscripcio">Inscripció</label>
								<input type="text" name="inscripcio" class="form-control" id="inscripcio" placeholder="Inscripció" value="<?=$data["inscripcio"]?>">
							</div>
							<div class="form-group col-md-6">
								<label for="iniciprevist">Data Prevista d'inici</label>
								<input type="text" name="iniciprevist" class="form-control" id="iniciprevist" placeholder="DD/MM/AAAA" value="<?=$data["iniciprevist"]?>">
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
