<?php 

namespace App\Controller;

class GetController extends MailController{


	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$table = json_decode($_POST['json'], true)['entity']; 
		$struct = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		foreach ($struct as $key => $value) {
			if(preg_match('/^id[a-zA-Z]+/', $key)){
				$parentField = $key;
				$parentId = $value;
				break;
			}
		}

		if(!empty($parentField) && !empty($parentId))
			$this->mainTraitment($table, $parentField, $parentId, $struct);
		else
			echo 'No traitment to do.';
	}



	public function mainTraitment($table, $parentField, $parentId, $struct){
		// on switch le nom de la table avec celui qui correspond en bdd
		$table = $this->switcher($table);

		// Requete
		$rep = $this->select("SELECT * FROM $table WHERE $parentField = '$parentId'");
		$idKey = '';
		$idValue = '';

		// traitement
		foreach ($rep as $result) {
			foreach ($result as $key => $value) {
				// Si le champ est demandé
				if(in_array($key, $struct))
					echo '<br/>'.$key." : ".$value;
				// Si id il y a on le chope pour construire les conditions des enfants
				if(empty($idKey) && empty($idValue) && preg_match('/^id[a-zA-Z]+/', $key)){
					$idKey = $key;
					$idValue = $value;
					echo "<br/><b>Futur clause :</b> ".$idKey."->".$idValue;
				}
			}
			if(empty($idKey) && empty($idValue)){
				echo 'No condition for futur clause.';
			// si il a les futur conditions, on véirifie si des tableau sont demandés.
			}else{
				$tableau = '';
				echo "<br/><br/>TABLEAU(X) :";
				foreach ($struct as $key => $value) {
					if(is_array($value) && !empty($value))
						$tableau[] = $key; echo '<br/>'.$key;
				}
				foreach ($tableau as $key) {
					// on récursive pour les tableaux voulu.
					echo '<br/><br/>Go Traitment : '.$key.' -> '.$idKey.' = '.$idValue;
					$this->mainTraitment($tableau, $idKey, $idValue, $struct[$tableau]);
				}
			}
		}
	}



	public function switcher($table){
		$switcher = array(
				"FamilyData" => "api_Family",
				"FamilyMember" => "api_Children",
				"Chore" => "api_ChoreRec",
				"ChoreChild" => "api_ChoreDone", 
				"Settings" => "api_Settings",
				"hero" => "api_Hero",
				// "listeDebloque" => "api_ObjectUnlock"
			);

		return $switcher[$table];
	}

}

?>