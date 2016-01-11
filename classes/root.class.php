<?php

class root{

    /* Si $edited = 0, l'objet pourra etre mis en update
    Si $edited = 1, l'objet a été mis en update
    Sinon l'objet ne sera jamais mis en update (par choix, $edited = -1) */
	private $edited;

    public function __construct ($edited='-1',$addToManager=true){
        $this->edited = $edited;
        if($addToManager)
            Manager::getInstance()->newObject($this);
    }

    public static function removeRootProperties($tab){
        unset($tab['edited']);
        return $tab;
    }

    public function Create ($tab){
        $attr = $this->getSqlAttr();
        foreach($attr as $key=>$value)
        {
            if(array_key_exists($key,$tab))
            {
                $this->$key = $tab[$key];
            }
        }
    }

    public function CreateWithObjId($tab,$id){
        $attr = $this->getSqlAttr();
        foreach($attr as $key=>$value)
        {
            if(array_key_exists($key.$id,$tab))
            {
                $this->$key = $tab[$key.$id];
            }
        }
    }

    /*
    function getParentsPref(){
        $tab_parent = class_parents(get_class($this));
        $tab_pref = array();
        foreach($tab_parent as $t)
        {
            if($t!='root')
            {
                array_push($tab_pref,$t::getStaticPref());
            }
        }
        return $tab_pref;
    }*/

    public function getAttr($attr){
        return $this->getAttrWithPref($this->getPref().$attr);
    }
  
    public function getAttrWithPref($attr){
        $tab_var = $this->getAllAttr();
        if(array_key_exists($attr,$tab_var))
        {
            return $this->$attr;
        }

        Site::addDevError("L'attribut ".$attr." n'existe pas dans la classe ".get_class($this));
        /*
        else
        {
            foreach($this->getParentsPref() as $t)
            {
                $var = $t.$attr;
                if(in_array($var,$tab_var))
                {
                    return $this->$var;
                }
            }
        }*/
    }

    /*
    function CreateMyTrueParent(){
    $tab_parent = array_keys(class_parents(get_class($this)));
    if(sizeof($tab_parent)>1)
    {
      $name_parent = $tab_parent[0];
      $obj_parent = new $name_parent();
      $tab_var_parent = array_keys(get_object_vars($obj_parent));
      foreach($tab_var_parent as $t)
      {
        $obj_parent->$t = $this->$t;
      }
      return $obj_parent;
    }
    else
    {
      return false;
    }
    }

    function CreateMyOlderParent(){
    $tab_parent = array_keys(class_parents(get_class($this)));
    if(sizeof($tab_parent)>1)
    {
      $name_parent = $tab_parent[sizeof($tab_parent)-2];
      $obj_parent = new $name_parent();
      $tab_var_parent = array_keys(get_object_vars($obj_parent));
      foreach($tab_var_parent as $t)
      {
        $obj_parent->$t = $this->$t;
      }
      return $obj_parent;
    }
    else
    {
      return false;
    }
    }*/
	
	public function Insert(){
        /*
		$obj_parent = $this->CreateMyTrueParent();
		if($obj_parent!=false)
		{
			$return_id = $obj_parent->Insert();
		}

		if(@$return_id=='')
		{
            $temp = get_object_vars($this);
            $temp = root::removeRootProperties($temp);
			$attr = array_keys($temp);
		}
		else if($obj_parent!='')
		{
			//Attributs du parent
            $temp = get_object_vars($obj_parent);
            $temp = root::removeRootProperties($temp);
            $attr_parent = array_keys($temp);
			//Attributs du actuel
            $temp = get_object_vars($this);
            $temp = root::removeRootProperties($temp);
            $attr = array_keys($temp);
			//virer les clés similaires entre parent et actuel
			array_splice($attr,sizeof($attr)-sizeof($attr_parent),sizeof($attr_parent));
			$foreign_key_id = $this->getParentsPref()[0].'id';
			$this->setAttr($foreign_key_id,$return_id);
		}*/

        //Si l'objet a déjà été inséré, ne pas insérer l'objet
        if(sizeof($this->getPrimary())==1)
        {
            $prim = $this->getPrimary();
            if($this->getAttrWithPref($prim[0])!='')
                return;
        }
		
		$attr = $this->getSqlAttr();
		
		//Gestion des objets dans les attributs
		
		foreach($attr as $key=>$val)
		{
			if(is_object($val))
			{
                $primary = $val->getPrimary();
                //On ne gère que les tables ayant une seule clé primaire. Celles en ayant deux ne seront jamais pointées par une table (association * --- *)
                if($val->getAttrWithPref($primary[0])=='')
                {
                    $val->Insert();
                }
			}
		}
		
		//Préparation de la liste des attributs formatés pour le sql
		$dotliste = '';
        $cpt=0;
		foreach($attr as $key=>$val)
		{
            $cpt++;
			$dotliste.=':'.$key;
			if($cpt!=sizeof($attr))
			{
				$dotliste.=',';
			}
		}

        if(Manager::getInstance()->isDebug())
            Site::debug("INSERT INTO ".DBPRE.get_class($this)." VALUES (".$dotliste.")");
		
		$req = DB::getInstance()->prepare("INSERT INTO ".DBPRE.get_class($this)." VALUES (".$dotliste.")");
        foreach($attr as $key=>$val)
		{
            if(is_object($this->getAttrWithPref($key)))
            {
                $obj = $this->getAttrWithPref($key);
                $pri = $obj->getPrimary();
                $req->bindParam(':'.$key, $obj->getAttrWithPref($pri[0]));
            }
            else
            {
                $req->bindParam(':'.$key, $this->getAttrWithPref($key));
            }
		}
		$req->execute();
		//if(@$return_id=='')
		//{

		//}
        if(sizeof($this->getPrimary())==1)
        {
            $return_id=DB::LastId();
            $prima = $this->getPrimary();
		    $this->setAttrWithPref($prima[0],$return_id);
        }
		//return $return_id;
	}

  
    public function Update(){
        //$obj_parent = $this->CreateMyTrueParent();

        /*if($obj_parent!=false)
        {
            $obj_parent->Update();
        }
        if($obj_parent!=false)
        {
            //Attributs du parent
            $temp = get_object_vars($obj_parent);
            $temp = root::removeRootProperties($temp);
            $attr_parent = array_keys($temp);
            //Attributs du actuel
            $temp = get_object_vars($this);
            $temp = root::removeRootProperties($temp);
            $attr = array_keys($temp);
            //virer les clés similaires entre parent et actuel
            array_splice($attr,sizeof($attr)-sizeof($attr_parent),sizeof($attr_parent));
            //récupérer le nom de la clé étrangère unique
            $foreign_key_id = $this->getParentsPref()[0].'id';
        }
        else
        {
            $attr = get_object_vars($this);
            $attr = root::removeRootProperties($attr);
            $attr = array_keys($attr);
            $foreign_key_id = 'id';
        }
	    */
	    $attr = $this->getSqlAttr();

        $dotliste = '';
        $cpt=0;
        foreach($attr as $key=>$val)
        {
            $cpt++;
            if(!in_array($key,$this->getPrimary()))
            {
                $dotliste.=$key.'=:'.$key;
                if($cpt!=sizeof($attr))
                {
                    $dotliste.=',';
                }
            }
        }
        if($dotliste=='')
            return;

        //Préparation de la requete WHERE
        if(Manager::getInstance()->isDebug())
            Site::debug("UPDATE ".DBPRE.get_class($this)." SET ".$dotliste." WHERE ".$this->getPrimaryFormatedForSql());
		$req = DB::getInstance()->prepare("UPDATE ".DBPRE.get_class($this)." SET ".$dotliste." WHERE ".$this->getPrimaryFormatedForSql());
		foreach($attr as $key=>$val)
		{
            if(!in_array($key,$this->getPrimary()))
            {
                if(is_object($this->getAttrWithPref($key)))
                {
                    $obj = $this->getAttrWithPref($key);
                    $prim = $obj->getPrimary();
                    $req->bindParam(':'.$key, $obj->getAttrWithPref($prim[0]));
                }
                else
                {
                    $req->bindParam(':'.$key, $this->getAttrWithPref($key));
                }

            }
		}
		$req->execute();
	}
	
	public function Delete(){
        $class = get_class($this);

        if(Manager::getInstance()->isToDelete($class,$this->getPrimaryFormatedForManager()))
        {
            //$obj_parent = $this->CreateMyTrueParent();

            //if($obj_parent==false)
            //{
                $req = DB::getInstance()->prepare("DELETE FROM ".DBPRE.get_class($this)." WHERE ".$this->getPrimaryFormatedForSql());
                $req->execute();
            //}
            //else
            //{
                //On ne supprime que le plus vieux parent, il faut gérer la bdd en cascade.
                //$this->CreateMyOlderParent()->Delete();
            //}
            //$this->setAttr('id',NULL);
        }
        else
        {
            Manager::getInstance()->addToDelete($this);
        }
	}
  
    public static function ArrayToObj($tab){
        $class = get_called_class();
        $obj = new $class();
        $obj->Create($tab);
        return $obj;
    }
  
    public static function ArrayOfArrayToArrayOfObj($tab){
        $class = get_called_class();
        $obj_tab = array();
        foreach($tab as $t)
        {
            array_push($obj_tab,$class::ArrayToObj($t));
        }
        return $obj_tab;
    }

    public function setAttr($attr,$value){
        return $this->setAttrWithPref($this->getPref().$attr,$value);
    }
  
    public function setAttrWithPref($attr,$value){

        $vars = $this->getAllAttr();
        if(array_key_exists($attr,$vars))
        {
            $this->$attr = $value;
        }
        /*
        else
        {
            foreach($this->getParentsPref() as $t)
            {
                $var = $t.$attr;
                if(in_array($var,$tab_var))
                {
                    $this->$var = $value;
                }
            }
        }*/
        if(!$this->isNonSqlProperties($attr) && $this->edited==0)
        {
            Manager::getInstance()->addToUpdate($this);
            $this->edited=true;
        }
        return $this;
    }

    public function setViaManager($attr,$value){
        if(is_object($this->$attr))
        {
            if($this->$attr != $value)
                $this->$attr = array($this->$attr,$value);
        }
        else if(is_array($this->$attr))
        {
            array_push($this->$attr,$value);
        }
        else
        {
            $this->$attr = $value;
        }
    }
	
    /*public static function getAll(){
        return DB::SqlToArray("select * from ".DBPRE.get_called_class());
    }*/
  
    public function getPref(){
        $class = get_class($this);
        return $class::$pref;
    }
  
    public static function getStaticPref(){
        $class = get_called_class();
        return $class::$pref;
    }
  
	public static function removeNonSqlProperties($attr){
		$class = get_called_class();
		if(property_exists($class,'exclusion'))
		{
			foreach($class::$exclusion as $ex)
			{
				unset($attr[$ex]);
			}
		}
		return $attr;
	}
	
	public function isNonSqlProperties($attr){
		$class = get_class($this);
		if(property_exists($class,'exclusion'))
		{
			if(in_array($attr,$class::$exclusion))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
    }

    public function getPrimary(){
        $class = get_class($this);
        return $class::$primary;
    }

    public function getPrimaryFormatedForManager(){
        $form = '';
        $cpt = 0;
        foreach($this->getPrimary() as $p)
        {
            $cpt++;
            $valprim = $this->getAttrWithPref($p);
            while(is_object($valprim))
            {
                $prim = $valprim->getPrimary();
                $valprim = $valprim->getAttrWithPref($prim[0]);
            }
            $form .= $valprim;

            if($cpt!=sizeof($this->getPrimary()))
                $form.='-';
        }
        return $form;
    }

    public function getPrimaryFormatedForSql(){
        $where = '';
        $cpt = 0;
        foreach($this->getPrimary() as $p)
        {
            $cpt++;
            $valprim = $this->getAttrWithPref($p);
            while(is_object($valprim))
            {
                $prim = $valprim->getPrimary();
                $valprim = $valprim->getAttrWithPref($prim[0]);
            }
            $where .= $p." = '".$valprim."'";
            if($cpt!=sizeof($this->getPrimary()))
                $where .= ' AND ';
        }
        return $where;
    }

    public static function getStaticPrimary(){
        $class = get_called_class();
        return $class::$primary;
    }

    public function getAllAttr(){
        $vars = get_object_vars($this);
        return self::removeRootProperties($vars);
    }

    public function getSqlAttr(){
        $vars = get_object_vars($this);
        $vars = self::removeNonSqlProperties($vars);
        return self::removeRootProperties($vars);
    }

    public static function getStaticSqlAttr(){
        $class = get_called_class();
        $obj = new $class(-1,false);
        $vars = get_object_vars($obj);
        $vars = self::removeNonSqlProperties($vars);
        return self::removeRootProperties($vars);
    }

    public static function getSqlSelectAttr($id){
        $class = get_called_class();
        $attrs = $class::getStaticSqlAttr();
        $ret = '';
        foreach($attrs as $k=>$a)
        {
            $ret .= $id.".".$k." as ".$k."_".$id.',';
        }
        $ret = substr($ret,0,-1);
        return $ret;
    }
  
};

?>