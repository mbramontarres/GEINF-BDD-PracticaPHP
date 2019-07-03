#!/usr/bin/php-cgi
<?php
	// Includes
	require_once("includes/globals.php");
	require_once("includes/sessio.php");
	require_once("includes/db.php");
	
	// Constants de plana
	define("TITOL","Participants cursa");

	// Variables de plana
	$accio = "";
	$error = "";
	$data = array();
	init_form_data();
	$curses = array();
	
	// Funcions
	function load_curses(){
		global $curses;
		global $error;
		try {
			$curses = llista_curses();			
		}
		catch (Exception $e) {
			$error = $e->getMessage();
		}		
	}	
	function init_form_data() {
		global $data;
		$data = [ 
			"cursa" => "",
			"participants" => array(),
			];
	}
	function load_form_data() {
		global $data;
 		foreach ($data as $key => $value){
 			$data[$key] = $_POST[$key];
		}	
	}
	function load_participants(){
		global $data;	
		global $error;
 		try {
			$data["participants"] = llista_participants($data["cursa"]);
		}
		catch (Exception $e) {
			$error = htmlentities("ERROR BDD: ".$e->getMessage());
		}
	}
	// Control del fluxe
	$accio = $_POST["accio"];
	
	if(strlen($error)==0){
		load_curses();
	}
	if (strlen($error)==0 && $accio=="seleccionar"){
		load_form_data();
	}
	if (strlen($error)==0 && strlen($data["cursa"])>0) {
		load_participants();
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
				</div>
				<?php if (strlen($data["cursa"])!=0) {?>
					<div class="row mt-2">
						<div class="col-12">
							<?php if (count($data["participants"])==0) { ?>
								<div class="alert alert-danger"><?="No hi ha participants"?></div>
							<?php } else { ?>
								<table class="table table-striped">
								  <thead>
									<tr>
									  <th scope="col">Vehicle</th>
									  <th scope="col">Personatge</th>
									  <th scope="col">Temps</th>
									</tr>
								  </thead>
								  <tbody>
									<?php foreach ($data["participants"] as $row) { ?>
										<tr>
										  <th scope="row"><?=$row["VEHICLE"]?></th>
										  <td><?=$row["PERSONATGE"]?></td>
										  <td><?=$row["TEMPS"]==NULL? "Abandonat": $row["TEMPS"]?></td>
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
