<?php 

namespace App\Controller;

class PartnerController extends SQLController{

	public function familychildren($field = null){
		if($field == null){ $error = array('error' => 400, 'msg' => 'il manque l\'id ou le mail de la famille.'); echo json_encode($error); die(); }
		// On prépare l'erreur
		$error = array('error' => 400, 'msg' => 'il manque l\'id ou le mail de la famille.');
		// Si erreur il y a :
		if(preg_match('[\d]', $field)){
			// Le client a passé un id
			$condition = $field;
		}
		elseif(filter_var($field, FILTER_VALIDATE_EMAIL)){
			// Le client a passé un mail
			$condition = "(";
			$condition .= "SELECT idFamily FROM api_Family WHERE mail = '$field' LIMIT 1";
			$condition .= ")";
		}else{ echo json_encode($error); die();  }
		// Cb d'enfant en tout ?
		$sqlnb = "SELECT COUNT(idChildren) as nbChildren FROM api_Children WHERE Family_idFamily = ".$condition;
		$famille['nb'] = $this->select($sqlnb)[0]['nbChildren'];
		// Qui sont-ils ?
		if($famille['nb'] > 0){
			$sql = "SELECT idChildren, name, photo, level, xp, energy FROM api_Children WHERE Family_idFamily = ".$condition;
			$famille['children'] = $this->select($sql);
		}
		// On retourne l'objet json.
		echo json_encode($famille);

	}

	public function isAbleToPlay($idChild){
		//
		$sql = "SELECT energy FROM api_Children WHERE idChildren = ".$idChild;
		$rep = $this->select($sql);
		// On analyse la reponse
		if($rep[0]['energy'] >= 70){
			// IsAbleToPlay
			echo 'true';
			return true;
		}else{
			// NotAllowed
			echo 'false';
			return false;

		}

	}

	public function gainXp($idChild, $gain){
		// Si le gain est trop grand, on tronque :)
		if($gain > 50){ $gain = 50; }

		// choper le lvl de l'enfant
		$sqlLvl = "SELECT level, xp FROM api_Children WHERE idChildren = ".$idChild;
		$rep = $this->select($sqlLvl);
		$level = $rep[0]['level'];
		$xp = $rep[0]['xp'];

		// On calcul l'xp max du niveau
		$xpNeeded = pow(2, $level -1);
		$xp += $gain;

		// doit-on passer au niveau superieur ?
		if( $xp >= $xpNeeded){
			$xp -= $xpNeeded;
			$level++; 
		}

		// On insert les nouvelles valeurs
		$sqlInsert = "UPDATE api_Children SET level = '$level', xp = '$xp' WHERE idChildren = ".$idChild;
		$reqInsert = $this->insert($sqlInsert);
		// On sort les news
		$sqlSelect = "SELECT * FROM api_Children WHERE idChildren = ".$idChild;
		$reqSelect = $this->select($sqlSelect);
		// le petit echo qui va bien ^
		echo json_encode($reqSelect[0]);

	}

}