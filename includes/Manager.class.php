<?php
class Manager {

    //WARNING: dans le from, mettre les objets dans l'ordre logique de lien des tables.
    //Idée: vérifier au deuxieme objet s'il a un lien avec les précédents, si non l'échanger avec le 3eme, si lui non plus 4eme, ...

	protected static $Instance = null;
	
	protected $Objects = array();
    protected $new_Objects_id = 0;

    protected $select = "";
    protected $from = "";
    protected $where = "";
    protected $extra = "";
    protected $orderby = "";
    protected $mode = "";
    protected $editDataBase = true;

    protected $debug=false;

	protected $toInsert = array();
	protected $toUpdate = array();
	protected $toDelete = array();
	
	protected function __construct(){
	}

    public function isDebug(){
        return $this->debug;
    }

    public function Debug(){
        $this->debug=true;
    }

    public function shouldEditDataBase(){
        return $this->editDataBase;
    }

    public function dontEditDataBase(){
        $this->editDataBase = false;
    }

    public function pleaseEditDataBase(){
        $this->editDataBase = true;
    }

	public static function getInstance(){
		if(self::$Instance==null)
			self::$Instance = new Manager();
		return self::$Instance;
	}
	
	public static function isInstancied(){
		if(self::$Instance==null)
			return false;
		else
			return true;
	}

    public function clear(){
        self::$Instance = new Manager();
        return self::$Instance;
    }
	
	public function addToUpdate($obj){
		$class = get_class($obj);

		if(!array_key_exists($class,$this->toUpdate))
		{
			$this->toUpdate[$class]=array();
		}
        $this->toUpdate[$class][$obj->getPrimaryFormatedForManager()]=$obj;
	}
	
	public function addToInsert($obj){
		$class = get_class($obj);
		if(!array_key_exists($class,$this->toInsert))
		{
			$this->toInsert[$class]=array();
		}
		array_push($this->toInsert[$class],$obj);
	}
	
	public function addToDelete($obj){
		$class = get_class($obj);
		if(!array_key_exists($class,$this->toDelete))
		{
			$this->toDelete[$class]=array();
		}
		$this->toDelete[$class][$obj->getPrimaryFormatedForManager()]=$obj;
	}
	
	public function addToObjects($obj){
        $class = get_class($obj);
        if (!array_key_exists($class,$this->Objects))
        {
            $this->Objects[$class] = array();
        }
        if (!in_array($obj->getPrimaryFormatedForManager(),array('-','')))
        {
            if(!array_key_exists($obj->getPrimaryFormatedForManager(),$this->Objects[$class]))
            {
                $this->Objects[$class][$obj->getPrimaryFormatedForManager()] = $obj;
                return $obj;
            }
            else
                return $this->Objects[$class][$obj->getPrimaryFormatedForManager()];
        }
        else
        {
            $this->Objects[$class]['new_'.$this->new_Objects_id] = $obj;
            $this->new_Objects_id++;
            return $obj;
        }
	}
	
	public function EditDataBase(){
		$this->Insert();
		$this->Update();
		$this->Delete();
	}
	
	public function Insert(){
		foreach($this->toInsert as $tab)
		{
			if(is_array($tab))
			{
				foreach($tab as $obj)
				{
					$obj->Insert();
				}
			}
		}
	}
	
	public function Delete(){
		foreach($this->toDelete as $tab)
		{
			if(is_array($tab))
			{
				foreach($tab as $obj)
				{
					$obj->Delete();
				}
			}
		}
	}

    public function isToDelete($class,$id){
        return isset($this->toDelete[$class][$id]) ? true : false;
    }
	
	public function Update(){
		foreach($this->toUpdate as $tab)
		{
			if(is_array($tab))
			{
				foreach($tab as $obj)
				{
					$obj->Update();
				}
			}
		}
	}

    public function newObject($name){
        if(!is_object($name))
            $obj = new $name(-1,false);
        else
            $obj = $name;
        $this->addToObjects($obj);
        $this->addToInsert($obj);
        return $obj;
    }

    public function getObjects($name){
        if(array_key_exists($name,$this->Objects))
            return $this->Objects[$name];
        else
            return NULL;
    }

    public function getObject($class,$id){
        if(is_object($id))
        {
            $id = $id->getPrimaryFormatedForManager();
        }
        if(array_key_exists($class,$this->Objects))
        {
            if($this->Objects[$class]!='')
            {
                if(array_key_exists($id,$this->Objects[$class]))
                    return $this->Objects[$class][$id];
                else
                    return null;
            }
            else
                return NULL;
        }
        else
            return NULL;
    }

    public function selectAllFrom($from){
        $this->mode="DEFAULT";
        $this->select = "*";
        $this->from = explode(',',$from);
        $this->from = array_map('trim',$this->from);
        $this->extra = "";
        return $this;
    }

    public function selectFrom($what,$from){
        $this->mode="DEFAULT";
        $this->select = "SELECT $what FROM ";
        $this->from = explode(',',$from);
        $this->from = array_map('trim',$this->from);
        $this->extra = "";
        return $this;
    }

    public function selectCountFrom($from){
        $this->mode="COUNT";
        $this->select = "SELECT count(*) as NUMBER FROM ";
        $this->from = explode(',',$from);
        $this->from = array_map('trim',$this->from);
        $this->extra = "";
        return $this;
    }

    public function deleteFrom($from){
        $this->mode="DELETE";
        $this->select = "DELETE FROM ";
        $this->from = explode(',',$from);

        $this->from = array_map('trim',$this->from);
        $this->extra = "";
        return $this;
    }

    public function selectOneObject($obj,$arrayPrimary){
        $primary = $obj::$primary;
        if(sizeof($primary)!=sizeof($arrayPrimary))
            Site::addDevError("Select One Object: Array Dimension Error - Obj: ".$obj." ; Primary=".sizeof($primary)." ; ".sizeof($arrayPrimary)." Given");
        $this->mode="DEFAULT";
        $this->select = "*";
        $this->from = array($obj);
        $this->where = "";
        for($i=0;$i<sizeof($primary);$i++)
        {
            $this->where .= $primary[$i]." = '".$arrayPrimary[$i]."'";
            if($i<sizeof($primary)-1)
                $this->where .= " and ";
        }
        $this->extra = "";
        return $this;
    }

    public function addLinkedTable(){
        if($this->from=='')
        {
            Site::addDevError("Erreur: autoLink doit être appelé après une fonction select.");
            return $this;
        }
        else
        {
            $finalTab = $this->from;
            foreach($this->from as $f)
            {
                foreach($f::$references as $r)
                {
                    $r = explode('->',$r);
                    $origin = explode('#',$r[1]);
                    if(sizeof($origin)>1)
                    {
                        if(!in_array($origin[1],$finalTab))
                            array_push($finalTab,$origin[1]);
                    }
                    if(!in_array($origin[0],$finalTab))
                        array_push($finalTab,$origin[0]);
                }
            }
            $this->from = $finalTab;
            return $this;
        }
    }

    public function where($extra){
        $this->where .= $extra;
        return $this;
    }

    public function having($extra){
        $this->extra .= " HAVING ".$extra;
        return $this;
    }

    public function groupby($extra){
        $this->extra .= " GROUP BY ".$extra;
        return $this;
    }

    public function orderby($extra){
        $temptab = explode(',',$extra);
        $temptab = array_map('trim',$temptab);
        $this->orderby = $temptab;
        return $this;
    }

    public function limit($extra){
        $this->extra .= " LIMIT ".$extra;
        return $this;
    }

    public function offset($extra){
        $this->extra .= " OFFSET ".$extra;
        return $this;
    }

    public function query(){
        $where = '';
        $table_from = '';
        $references_done = array();
        $declaration = array();
        $tempfrom = $this->from;

        if($this->mode!="DELETE")
        {
            if($this->select=='*')
                $select = "SELECT ";
            foreach($tempfrom as $key=>$class)
            {
                if($this->select=='*')
                {
                    $select .= $class::getSqlSelectAttr("obj".$key).',';
                }
                $copyfrom = $this->from;
                $objid = "obj".$key;
                $found = false;
                foreach($references_done as $d)
                {
                    $t = explode('.',$d);
                    if($objid==$t[0])
                    {
                        $found = true;
                        break;
                    }
                }
                if(!$found && !in_array($class,$declaration))
                {
                    $table_from .= DBPRE.$class." as ".$objid." ".strtoupper($objid);
                    array_push($declaration,$class);
                }
                else
                {
                    $table_from = substr($table_from,0,-1);
                }

                //Si il y a au moins une référence
                if(sizeof($class::$references)>0)
                {
                    $refobj = array();
                    //Parcourir ces références
                    foreach($class::$references as $ref)
                    {
                        $tab = Site::multiexplode(array('->','#'),$ref);
                        //Si l'objet référé est bien dans la liste des objets sélectionnés
                        if(in_array($tab[1],$this->from))
                        {
                            $objrefid = "obj".array_search($tab[1],$copyfrom);
                            //Si le champ sélectionné est bien un champ SQL
                            if(!in_array($tab[0],$class::$exclusion))
                            {
                                if(!in_array($objid.".".$tab[0]."->$objrefid",$references_done))
                                {
                                    if(in_array($tab[1],$refobj))
                                    {
                                        $objrefid = "obj".(array_push($this->from,$tab[1])-1);
                                        if($this->select=='*')
                                        {
                                            $klas = $tab[1];
                                            $select .= $klas::getSqlSelectAttr($objrefid).',';
                                        }
                                    }
                                    $prim = $tab[1]::getStaticPrimary();
                                    $table_from = str_replace(strtoupper($objid)," LEFT JOIN ".DBPRE.$tab[1]." as ".$objrefid." ".strtoupper($objrefid)." ON ".$objid.".".$tab[0]." = ".$objrefid.".".$prim[0]." ".strtoupper($objid),$table_from);
                                    array_push($declaration,$tab[1]);
                                    array_push($references_done,$objid.".".$tab[0]."->".$objrefid);
                                    unset($copyfrom[array_search($tab[1],$copyfrom)]);
                                    array_push($refobj,$tab[1]);
                                }
                                else
                                {
                                    array_push($refobj,$tab[1]);
                                }
                            }
                            else
                            {
                                array_push($references_done,$objid.".".$tab[0]."->".$tab[1]);
                            }
                        }
                    }
                }

                //Si il y a au moins une référence inverse
                if(sizeof($class::$referenced)>0)
                {
                    $refobj = array();
                    //Parcourir ces références
                    foreach($class::$referenced as $ref)
                    {
                        $tab = Site::multiexplode(array('<-'),$ref);
                        //Si l'objet qui nous réfère est bien dans la liste des objets sélectionnés
                        if(in_array($tab[1],$this->from) && !in_array($tab[1],$refobj))
                        {
                            $objrefid = "obj".array_search($tab[1],$copyfrom);
                            if(!in_array($objrefid.".".$tab[0]."->$objid",$references_done))
                            {
                                $prim = $class::getStaticPrimary();
                                $table_from = str_replace(strtoupper($objid)," LEFT JOIN ".DBPRE.$tab[1]." as ".$objrefid." ".strtoupper($objrefid)." ON ".$objrefid.".".$tab[0]." = ".$objid.".".$prim[0]." ".strtoupper($objid),$table_from);
                                array_push($declaration,$tab[1]);
                                array_push($references_done,$objrefid.".".$tab[0]."->".$objid);
                                unset($copyfrom[array_search($tab[1],$copyfrom)]);
                            }
                            array_push($refobj,$tab[1]);
                        }
                    }
                }

                if($key!=sizeof($tempfrom)-1)
                {
                    $table_from.=',';
                }
            }

            foreach($this->from as $key=>$t)
            {
                $table_from = str_replace("OBJ$key","",$table_from);
            }

            if($this->select=='*')
            {
                $this->select = substr($select,0,-1)." FROM ";
            }

        }
        else
        {
            foreach($this->from as $key=>$class)
            {
                $table_from .= DBPRE.$class.',';
            }
            $table_from = substr($table_from,0,-1);
        }

        if($where != '')
        {
            $where = ' WHERE '.$where;
        }

        if($this->where != '')
        {
            if($where != '')
                $where .= $this->where;
            else
                $where = ' WHERE '.$this->where;
        }
        else
        {
            if($where!='')
            {
                $where = substr($where,0,-4);
            }
        }

        $orderby = '';
        if($this->orderby!='')
        {
            $orderby = " ORDER BY ";
            foreach($this->orderby as $o)
            {
                foreach($this->from as $key=>$f)
                {
                    if(property_exists($f,$o))
                    {
                        $orderby .= 'obj'.$key.'.'.$o.',';
                        break;
                    }
                }
            }
            $orderby = substr($orderby,0,-1);
        }


        $request = $this->select.$table_from.$where.$this->extra.$orderby;
        if($this->debug)
            Site::debug($request);
        $pdo = DB::getInstance();
        $result = $pdo->query($request);

        if($this->mode=="DEFAULT")
        {
            while($row = $result->fetch(PDO::FETCH_ASSOC))
            {
                //Créer ou récupérer les objets
                $objects = array();
                foreach ($this->from as $key => $t)
                {
                    $primary = $t::getStaticPrimary();

                    if($row[$primary[0]."_obj".$key]!=NULL)
                    {
                        $obj = new $t(0,false);
                        $obj->CreateWithObjId($row,'_obj'.$key);
                        $obj = $this->addToObjects($obj);
                        $objects["obj".$key]=$obj;
                        if(array_key_exists($t,$objects))
                        {
                            if(is_array($objects[$t]))
                                array_push($objects[$t],$obj);
                            else
                                $objects[$t] = array($objects[$t],$obj);
                        }
                        else
                            $objects[$t]=$obj;
                    }
                    else
                    {
                        $objects["obj".$key] = NULL;
                    }
                }
                //Linker les objets entre eux
                foreach($references_done as $ref)
                {
                    $tab = Site::multiexplode(array('->','.'),$ref);
                    //if(preg_match("^obj[0-9]+$",$tab[0]))
                    if($objects[$tab[0]]!=NULL && array_key_exists($tab[2],$objects))
                    {
                        $objects[$tab[0]]->setViaManager($tab[1],$objects[$tab[2]]);
                    }
                }
            }
        }
        else if($this->mode=="COUNT")
        {
            $rs = $result->fetch(PDO::FETCH_ASSOC);
            $result->closeCursor();

            $this->select = "";
            $this->from = "";
            $this->where = "";
            $this->extra = "";
            $this->mode = "";
            $this->orderby = "";
            return $rs["NUMBER"];
        }

        $result->closeCursor();

        $this->select = "";
        $this->from = "";
        $this->where = "";
        $this->extra = "";
        $this->mode = "";
        $this->orderby = "";
    }

    public function existObjectEquals($class,$field,$value){
        foreach($this->getObjects($class) as $key=>$obj)
        {
            if($obj->getAttr($field) == $value)
                return $key;
        }
        return null;
    }


}

?>