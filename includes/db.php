<?php
	// Includes
	require_once("includes/globals.php");

	// Accessos a la bdd

	function executa_query($sql) {
		$data = array();

		// Establim connexió
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], BDD_SERVIDOR);
		if (!$conn) {
			$e = oci_error();
			throw new Exception($e['message']);
		}

		// Preparem la consulta
		$stid = oci_parse($conn, $sql);
		if (!$stid) {
			$e = oci_error($conn);
			throw new Exception($e['message']);
		}

		// Executem la consulta
		$r = oci_execute($stid);
		if (!$r) {
			$e = oci_error($stid);
			throw new Exception($e['message']);
		}

		// Carreguem les dades
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
			array_push($data,$row);
		}

		oci_free_statement($stid);
		oci_close($conn);
		return $data;
	}	
	
	function executa_cmd($conn,$sql) {
		// Preparem la comanda
		$stid = oci_parse($conn, $sql);
		if (!$stid) {
			$e = oci_error($conn);
			throw new Exception($e['message']);
		}

		// Executem la comanda
		$r = oci_execute($stid,OCI_NO_AUTO_COMMIT);
		if (!$r) {
			$e = oci_error($stid);
			throw new Exception($e['message']);
		}
		
		oci_free_statement($stid);
	}
	
	// 
	function llista_vehicles(){
		$sql = "select v.codi,v.descripcio,v.color,v.consum,u.nom||' '||u.cognoms as propietari, NVL(v.foto,g.foto) as foto ".
			"from vehicles v ". 
			"join usuaris u on u.alias=v.propietari ".
			"join grupsvehicles g on g.codi=v.grupvehicle ".
			"where v.habilitat='S'";
		return executa_query($sql);
	}
	function llista_vehicles_per_propietari(){
		$sql = "select v.codi,v.descripcio,v.color,v.consum,v.propietari,u.nom||' '||u.cognoms as nompropietari,NVL(preu,0) as preu, NVL(v.foto,g.foto) as foto ".
			"from vehicles v ". 
			"join usuaris u on u.alias=v.propietari ".
			"join grupsvehicles g on g.codi=v.grupvehicle ".
			"where v.habilitat='S' ".
			"order by u.cognoms,u.nom,v.propietari,v.descripcio";
		return executa_query($sql);
	}
	function obtenir_vehicle($codi){
		$sql = "select v.codi,v.descripcio,v.color,v.consum,v.propietari,u.nom||' '||u.cognoms as nompropietari,NVL(preu,0) as preu,NVL(u.saldo,0) as saldopropietari, NVL(v.foto,g.foto) as foto ".
			"from vehicles v ". 
			"join usuaris u on u.alias=v.propietari ".
			"join grupsvehicles g on g.codi=v.grupvehicle ".
			"where v.codi='{$codi}'";
		return executa_query($sql);
	}
	function obtenir_cursa($codi) {
		$sql = "select * from curses where codi='{$codi}'";
		return executa_query($sql);
	}
	function llista_personatges_usuari($usuari) {
		$sql = "select * from personatges where usuari='{$usuari}'";
		return executa_query($sql);
	}
 	function llista_usuaris(){
		$sql = "select alias,nom||' '||cognoms as propietari,NVL(saldo,0) as saldo from usuaris order by cognoms,nom";	
		return executa_query($sql);
	}
	
	function llista_grupsvehicles(){
		$sql = "select codi,descripcio from grupsVehicles order by descripcio";		
		return executa_query($sql);
	} 
	
	function llista_combustibles(){		
		$sql = "select descripcio from combustibles order by descripcio";
		return executa_query($sql);		
	}
	
	function llista_curses(){
		$sql = "select codi, nom, premi, inscripcio, iniciprevist from curses order by nom";
		return executa_query($sql);
	}
	function llista_curses_inscripcio_oberta(){
		$sql = "select codi, nom, premi, inscripcio, iniciprevist from curses where inicireal is null order by nom";
		return executa_query($sql);
	}
	
	function llista_curses_codi(){
		$sql = "select codi from curses order by codi";
		return executa_query($sql);
	}
	
	function llista_participants($cursa){
		$sql = "select p.vehicle, p.personatge, ".
		"(case when c.tempsenregistrats='N' then 'Pendent' when p.temps is null then 'Abandonat' else  ".
		"to_char(floor(temps),'FM00') || ':' || ".
		"to_char(round((temps-floor(temps))*60),'FM00') end) as temps  ".
		"from curses c join participantscurses p on p.cursa=c.codi  where c.codi ='{$cursa}'"; 
		// $sql = "select p.vehicle, p.personatge, ". 
		// 	"(case when c.tempsenregistrats='N' then 'Pendent' when p.temps is null then 'Abandonat' else to_char(p.temps) end) as temps ".
		// 	"from curses c join participantscurses p on p.cursa=c.codi  where c.codi ='{$cursa}'";
		return executa_query($sql);
	}
		
	function llista_vehicles_facturacio($usuari){
		
		$sql= "select f.vehicle, min(v.descripcio) as nom_vehicle,count(*) as num_curses, sum(f.total) as total from factures f ".
				"join vehicles v on v.codi=f.vehicle ".
				"where f.propietari='{$usuari}' ".
				"group by f.vehicle ".
				"order by sum(f.total) desc";
		
		return executa_query($sql);
	}

	function llista_factures_entre_dates($data_inici,$data_fi){		
		$sql= "select codi,cursa,vehicle,propietari,to_char(data_factura,'DD/MM/YYYY') as data_factura, ".
			"to_char(floor(temps),'FM00') || ':' || to_char(round((temps-floor(temps))*60),'FM00') as temps,cost_combustible,preu_servei,iva,total from factures where data_factura>=to_date('{$data_inici}','dd/mm/yyyy') and data_factura<=to_date('{$data_fi} 23:59:59','dd/mm/yyyy HH24:MI:SS') order by data_factura, cursa,vehicle";
		return executa_query($sql);
	}	
	
	function inserta_vehicle($data){
		// calcula codi
		$codi = "";
		$descripciogrup = executa_query("select * from grupsvehicles where codi='{$data["grupvehicle"]}'")[0]["DESCRIPCIO"];
		$codi .= substr($descripciogrup,0,1);
		$codi .= substr(str_replace(" ","",$data["descripcio"]),0,4);
		$numvehiclesdup =  executa_query("select count(*) as num from vehicles where codi like '{$codi}%'")[0]["NUM"];
		if ($numvehiclesdup>0) {
			$codi .= $numvehiclesdup+1;
		}
		// Inserta
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], BDD_SERVIDOR);
		if (!$conn) {
			throw new Exception(oci_error()['message']);
		}
 		$sql = "insert into vehicles(codi,descripcio,color,consum,preu,datacompra,grupvehicle,combustible,propietari) ".
			"values ('{$codi}','{$data["descripcio"]}','{$data["color"]}',{$data["consum"]},".
			"{$data["preu"]},to_date('{$data["datacompra"]}','dd/mm/yyyy'),'{$data["grupvehicle"]}','{$data["combustible"]}','{$data["propietari"]}')";
		executa_cmd($conn,$sql);
		$r = oci_commit($conn);
		if (!r) {
			throw new Exception(oci_error()['message']);
		}
 	}
		
	function inserta_cursa($data){		
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], BDD_SERVIDOR);
		if (!$conn) {
			throw new Exception(oci_error()['message']);
		}
 		$sql = "insert into curses(codi,nom,premi,inscripcio,iniciprevist) ".
			"values ('{$data["codi"]}','{$data["nom"]}',{$data["premi"]},{$data["inscripcio"]},to_date('{$data["iniciprevist"]}','dd/mm/yyyy'))";
		executa_cmd($conn,$sql);
		$r = oci_commit($conn);
		if (!r) {
			throw new Exception(oci_error()['message']);
		}
	} 
	function compra_vehicle($codivehicle,$comprador,$datavenda,$preu,$codi){
		// Calculem alias del venedor
		$vendedor = obtenir_vehicle($codivehicle)[0]["PROPIETARI"];
		// Conectem a la bdd
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], BDD_SERVIDOR);
		if (!$conn) {
			throw new Exception(oci_error()['message']);
		}
		// Insertem nou vehicle
		$sql="insert into vehicles (codi,descripcio,color,consum,datacompra,preu,grupvehicle,combustible,propietari) ".
			"select '{$codi}',descripcio,color,consum,to_date('{$datavenda}','dd/mm/yyyy'),{$preu},grupvehicle,combustible,'{$comprador}' ".
				"from vehicles where codi='{$codivehicle}'";
		executa_cmd($conn,$sql);
		// Marquem el vehicle venut amb habilitat='N'
		$sql="update vehicles set habilitat='N' where codi='{$codivehicle}'";
		try {
			executa_cmd($conn,$sql);
		}
		catch (Exception $e) {
			oci_rollback($conn);
			throw $e;
		}
		// Actualitzem el saldo del comprador
		$sql = "update usuaris set saldo=saldo-{$preu} where alias='{$comprador}'";
		try {
			executa_cmd($conn,$sql);
		}
		catch (Exception $e) {
			oci_rollback($conn);
			throw $e;
		}
		// Actualitzem el saldo del venedor
		$sql = "update usuaris set saldo=saldo+{$preu} where alias='{$vendedor}'";
		try {
			executa_cmd($conn,$sql);
		}
		catch (Exception $e) {
			oci_rollback($conn);
			throw $e;
		}
		// Fem commit
		$r = oci_commit($conn);
		if (!r) {
			throw new Exception(oci_error()['message']);
		}
	}
	function tancar_inscripcio($codi,$inicireal,$vehicles,$personatges) {
		$cursa = obtenir_cursa($codi)[0];
		// Conectem a la bdd
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], BDD_SERVIDOR);
		if (!$conn) {
			throw new Exception(oci_error()['message']);
		}
		// Afegim inscripcions i actualitzem saldo dels usuaris
		for ($i=0; $i<count($vehicles); $i++) {
			$vehicle = obtenir_vehicle($vehicles[$i])[0];
			// Afegim inscripció
			$sql = "insert into participantscurses (cursa,vehicle,personatge) values ('{$codi}','{$vehicles[$i]}','{$personatges[$i]}')";
			try {
				executa_cmd($conn,$sql);
			}
			catch (Exception $e) {
				oci_rollback($conn);
				throw $e;
			}
			// Actualitzem saldo de l'usuari
			$sql = "update usuaris set saldo=saldo-{$cursa["INSCRIPCIO"]} where alias='{$vehicle["PROPIETARI"]}'";
			try {
				executa_cmd($conn,$sql);
			}
			catch (Exception $e) {
				oci_rollback($conn);
				throw $e;
			}			
		}
		// Actualitzem inicireal a la cursa
		$sql = "update curses set inicireal=to_date('{$inicireal}','dd/mm/yyyy') where codi='{$codi}'";
		try {
			executa_cmd($conn,$sql);
		}
		catch (Exception $e) {
			oci_rollback($conn);
			throw $e;
		}
		
		// Fem commit
		$r = oci_commit($conn);
		if (!r) {
			throw new Exception(oci_error()['message']);
		}		
	}
	function actualitzar_temps($cursa,$temps) {
		// Conectem a la bdd
		$conn = oci_connect($_SESSION['usuari'], $_SESSION['password'], BDD_SERVIDOR);
		if (!$conn) {
			throw new Exception(oci_error()['message']);
		}
		// Actualitzems els temps dels participants
		foreach($temps as $vehicle => $arr) {
			foreach($arr as $personatge => $valor) {
				if (!empty($valor)){
					$t=explode(':',$valor)[0]/1.0+round(explode(':',$valor)[1]/60.0,2);					 
					$sql = "update participantscurses set temps={$t} where cursa='{$cursa}' and vehicle='{$vehicle}' and personatge='{$personatge}'";
					try {
						executa_cmd($conn,$sql);
					}
					catch (Exception $e) {
						oci_rollback($conn);
						throw $e;
					}
				}
			}
		}
		//Creem factura
		foreach($temps as $vehicle => $arr) {
			foreach($arr as $personatge => $valor) {
				$iva= IVA;
				$preu_servei = PREU_SERVEI;
				$sql = "insert into factures(codi,cursa,vehicle,propietari, data_factura,temps,cost_combustible,preu_servei,iva,total) ".
						"select ".
						"NVL((select max(codi) from factures)+1,1) as codi, p.cursa, p.vehicle, v.propietari, current_date, ". 
						"NVL(p.temps,NVL((select max(temps) from participantscurses p2 where p2.cursa=p.cursa),0)) as temps, ".
						"c.preuunitat as cost_combustible, {$preu_servei} as preu_servei, {$iva} as iva, ".
						"({$preu_servei}+c.preuunitat*NVL(p.temps,NVL((select max(temps) from participantscurses p2 where p2.cursa=p.cursa),0)))*(100.0+{$iva})/100.0 as total ".
						"from participantscurses p ".
						"join vehicles v on v.codi=p.vehicle ".
						"join combustibles c on c.descripcio=v.combustible ".
						"where p.cursa='{$cursa}' and p.vehicle = '{$vehicle}' and p.personatge='{$personatge}'";
				try {
					executa_cmd($conn,$sql);
				}
				catch (Exception $e) {
					oci_rollback($conn);
					throw $e;
				}
			}
		}
		
		// Actualitzem el millortemps i tempsEnregistrats
		$sql = "update curses set millortemps=(select min(temps) from participantscurses pc where pc.cursa=curses.codi),tempsEnregistrats='S' where codi='{$cursa}'";
		try {
			executa_cmd($conn,$sql);
		}
		catch (Exception $e) {
			oci_rollback($conn);
			throw $e;
		}
		//Donem premi al guanyador
		$sql = "update usuaris set saldo=saldo+nvl((select premi from curses c where c.codi='{$cursa}'),0) ".
				"where alias in (select v.propietari from curses c join participantscurses pc on pc.cursa=c.codi join vehicles v on v.codi=pc.vehicle where c.codi='{$cursa}' and pc.temps=c.millortemps)";
		try {
			executa_cmd($conn,$sql);
		}
		catch (Exception $e) {
			oci_rollback($conn);
			throw $e;
		}
		
		// Fem commit de la transacció
		$r = oci_commit($conn);
		if (!r) {
			throw new Exception(oci_error()['message']);
		}
	
	}
?>