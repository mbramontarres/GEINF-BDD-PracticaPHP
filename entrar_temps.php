#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Entrar temps participants");

	// Variables de plana
	$accio = "";
	$error = "";
	$data = array();
	init_form_data();
	$curses = array();
	$participants = array();
	
	// Funcions
	function load_curses(){
		global $curses;
		global $error;
		try {
			$curses = llista_curses_codi();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}		
	}
	function load_participants($cursa){
		global $participants;	
		global $error;
 		try {
			$participants = llista_participants($cursa);
		}
		catch (Exception $e) {
			$error = htmlentities("ERROR BDD: ".$e->getMessage());
		}
	} 
	function init_form_data() {
		global $data;
		$data = [ 
			"cursa" => "",
			"temps" => array(),
			];
	}
 	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
			if (array_key_exists($key,$_POST)) {
				$data[$key] = $_POST[$key];
			}
		}
	}
	function validate_data() {
		global $data;
		// Mirem que ens hagin entrat els temps amb el format correcte
		foreach($data["temps"] as $vehicle => $arr) {
			foreach($arr as $personatge => $valor) {
				if (!empty($valor) && !preg_match(REGEXP_HH_MM,$valor)) {
					return "El temps del vehicle \"" . $vehicle . "\" no &eacute;s v&agrave;lid. El format ha de ser HH:MM.";
				}
			}
		}
		//
		return "";
	}
	
	// Control del fluxe
	$accio = $_POST["accio"];
	
	if(strlen($error)==0){
		load_curses();
	}
	if (strlen($error)==0){
		load_form_data();
	}
	if (strlen($error)==0 && strlen($data["cursa"])>0) {
		load_participants($data["cursa"]);
	}
	if (strlen($error)==0 && $accio=="tanca"){
		$error=validate_data();
		if (strlen($error)==0) {
			try {
				actualitzar_temps($data["cursa"],$data["temps"]);
				$info = "Taula de temps tancada";
				load_participants($data["cursa"]);
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
				<div class="row">
					<div class="col-12">
						<form name="form-seleccio_cursa" method="post">
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="cursa">Cursa</label>
									<select class="form-control" name="cursa" onchange="javascript: this.form.submit();">
										<option value="">--- Selecciona cursa ---</option>
										<?php foreach($curses as $row){ ?>
											<option value="<?=$row["CODI"] ?>" <?=$row["CODI"]==$data["cursa"]? "selected" : ""?>><?=$row["CODI"] ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<input type="hidden" name="accio" value="seleccionar" />
						</form>
					<div>
				</div>
				<?php if (strlen($data["cursa"])!=0) {?>
					<?php $tempsenregistrats = obtenir_cursa($data["cursa"])[0]["TEMPSENREGISTRATS"]; ?>						
					<div class="row mt-2">
						<div class="col-12">
							<?php if (count($participants)==0) { ?>
								<div class="alert alert-danger"><?="No hi ha participants"?></div>
							<?php } else { ?>
								<form method="post">
									<table class="table table-striped">
									  <thead>
										<tr>
										  <th scope="col">Vehicle</th>
										  <th scope="col">Personatge</th>
										  <th scope="col">Temps</th>
										</tr>
									  </thead>
									  <tbody>
										<?php foreach($participants as $row) { ?>
											<tr>
											  <th scope="row"><?=$row["VEHICLE"]?></th>
											  <td><?=$row["PERSONATGE"]?></td>
											  <td>
												<?php if ($tempsenregistrats=='N') { ?>
													<input type="text" 
														name="temps[<?=$row["VEHICLE"]?>][<?=$row["PERSONATGE"]?>]" class="form-control" id="temps"  
														value="<?=$data["temps"][$row["VEHICLE"]][$row["PERSONATGE"]]?>" >
												<?php } else { ?>
													<input type="text" class="form-control" value="<?=$row["TEMPS"]?>" readonly>
												<?php } ?>
												</td>
											</tr>										
										<?php } ?>
									  </tbody>
									</table>
									<input type="hidden" name="cursa" value="<?=$data["cursa"]?>">
									<?php if ($tempsenregistrats=='N') { ?>
										<button type="submit" name="accio" value="tanca" class="btn btn-primary">Tanca taula de temps</button>
									<?php } ?>
								</form>
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
