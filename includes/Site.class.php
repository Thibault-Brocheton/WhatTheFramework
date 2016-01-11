<?php

class Site {
	
	public static $page = NULL;
	public static $slug = NULL;
	public static $token = NULL;
	public static $name_view = array();
    public static $style = array("style");
    public static $header = true;
    public static $footer = true;
    public static $redirect = "";
    public static $autocompleteform =false;
    public static $controller = NULL;

    public static function setController($controller){
        self::$controller = $controller;
    }

    public static function getController(){
        return self::$controller;
    }

    public static function addAutoCompleteForm(){
        self::$autocompleteform = true;
    }

    public static function isAutoCompleteForm(){
        return self::$autocompleteform;
    }

    public static function doIfRedirect(){
        if(self::$redirect!="")
        {
            Site::redirect(self::$redirect);
            exit();
        }
    }

    public static function setRedirect($url){
        self::$redirect = $url;
    }

    public static function disableHeader(){
        self::$header = false;
    }

    public static function disableFooter(){
        self::$footer = false;
    }

    public static function isHeader()
    {
        return self::$header;
    }

    public static function isFooter()
    {
        return self::$footer;
    }

    public static function resetStyle(){
        self::$style = array();
    }

    public static function addStyle($name){
        array_push(self::$style,$name);
    }

    public static function getStyle(){
        return self::$style;
    }

	public static function debug($var){
        echo "<br /><pre>";
        print_r($var);
        echo "</pre><br />";
	}
	
	public static function get_page(){
		return self::$page;
	}

    public static function get_page_no_empty(){
        if(self::$page=="")
            return 'index';
        return self::$page;
    }
	
	public static function get_slug(){
		return self::$slug;
	}
	
	public static function get_token(){
		return self::$token;
	}
	
	public static function setUrlParameters($param1,$param2,$param3){
		self::$page = $param1;
		self::$slug = $param2;
		self::$token = $param3;
	}
	
	public static function get_view(){
		return self::$name_view;
	}

	//envoie un header de redirection au navigateur
	//et quitte le script
	public static function redirect($url){
		if (!headers_sent())
			header("Location: ".$url);
		else
			echo "<script language='JavaScript'>window.location='".$url."'</script>";
	}

    public static function addError($id,$mess){
        $_SESSION['message_error'][$id]=$mess;
    }

    public static function addWarning($id,$mess){
        $_SESSION['message_warning'][$id]=$mess;
    }

    public static function addSuccess($id,$mess){
        $_SESSION['message_success'][$id]=$mess;
    }

    public static function addInfo($id,$mess){
        $_SESSION['message_info'][$id]=$mess;
    }

    public static function printMessages(){
        $mess_types = array('error','warning','success','info');
        foreach($mess_types as $type)
        {
            if(array_key_exists('message_'.$type,$_SESSION))
            {
                foreach($_SESSION['message_'.$type] as $key=>$val)
                {
                    if($val!='')
                        echo "<div class='message'><div class='message_$type'>$val</div></div>";
                }
                echo '<script>$(".message").click(function(){$(this).remove();})</script>';
            }
        }
    }

    public static function isError($name){
        if(array_key_exists("message_error",$_SESSION))
        {
            if($_SESSION['message_error']==null)
                return false;
            return array_key_exists($name,$_SESSION['message_error']) ? true : false;
        }
        return false;
    }

    public static function isWarning($name){
        if(array_key_exists("message_warning",$_SESSION))
        {
            if($_SESSION['message_warning']==null)
                return false;
            return array_key_exists($name,$_SESSION['message_warning']) ? true : false;
        }
        return false;
    }

    public static function clearMessages(){
        unset($_SESSION['message_error']);
        unset($_SESSION['message_warning']);
        unset($_SESSION['message_success']);
        unset($_SESSION['message_info']);
    }
  
  static function crypt_pwd($text){
    return sha1(SALT_BEFORE.$text.SALT_AFTER);
  }
	
	static function ai_ci($text){
    $text = htmlentities($text, ENT_NOQUOTES, 'utf-8');
    $text = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $text);
    $text = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $text);
    $text = preg_replace('#&[^;]+;#', '', $text);
		$text = strtolower($text);
		return $text;
	}

	static function include_view($name){
        array_push(self::$name_view,$name);
	}
	
	static function is_view_defined(){
		if(sizeof(self::get_view())>0)
			return true;
		else
			return false;
	}
  
  static function obtain_post($field)
  {
      if($field!='')
      {
          if(array_key_exists($field,$_POST))
          {
            $field = $_POST[$field];
            $field = stripslashes($field);
            $field = str_replace("\"", "&quot;", $field);
            $field = str_replace("\"", "\"\"", $field);
            $field = trim($field);
            return $field;
          }
          else
              return NULL;
      }
      return NULL;
  }
  
  static function check_name($name)
  {
    return preg_match('/[A-Za-z]+([\-]?[A-Za-z])*/',$name);
  }
  
  static function generate_uniquid($length)
  {
    $code = "";
    $chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNOPGRSTUVWXYZ0123456789";
    for($i=0; $i<$length; $i++)
    {
      $code .= $chaine[rand()%strlen($chaine)];
    }
    return $code;
  }
  
  static function now()
  {
    return @strftime("%Y-%m-%d %H:%M:%S", mktime());
  }
  
  static function dow()
  {
    return @strftime("%Y-%m-%d", mktime());
  }
  
  static function tow()
  {
    return @strftime("%H:%M:%S", mktime());
  }
  
  static function GetInfoByAddress($address){
    $geocoder = "http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&language=fr";
    $url_address = urlencode($address);
    $query = sprintf($geocoder,$url_address);
    return json_decode(file_get_contents($query));
  }
  
  static function GetCoords($address){
    @$rs = self::GetInfoByAddress($address);
    @$coord = $rs->{'results'}[0]->{'geometry'}->{'location'};
    return array(@$coord->{'lat'},@$coord->{'lng'});
  }
  
  static function GetFullAddress($address){
    @$rs = self::GetInfoByAddress($address);
    @$full_address = $rs->{'results'}[0]->{'formatted_address'};
    @$tab_address = explode(',',$full_address);
    @$adr = $tab_address[0];
    @$zip = substr($tab_address[1],1,5);
    @$city_name = substr($tab_address[1],7);
    return array($adr,$zip,$city_name);
  }

    static function en_to_fr_date($date){
        return substr($date,8,2)."/".substr($date,5,2)."/".substr($date,0,4);
    }


  /*
	static function fd2md($d){
    if ($d!="")
      return substr($d,6,4)."-".substr($d,3,2)."-".substr($d,0,2);
    else
      return "";
  }
  
  static function md2fd2($d, $fmt){
    $res = "";
    if ($d != "")
    {
		  $res = $fmt;
		  $res = str_replace("YYYY", substr($d,0,4), $res);
		  $res = str_replace("YY", substr($d,2,2), $res);
		  $res = str_replace("MM", substr($d,5,2), $res);
		  $res = str_replace("DD", substr($d,8,2), $res);
		  $res = str_replace("HH", substr($d,11,2), $res);
		  $res = str_replace("II", substr($d,14,2), $res);
		  $res = str_replace("SS", substr($d,17,2), $res);
    }	
    return $res;
  }*/
  
    static function combo_contents($tab,$display_value='',$save_value='', $default=''){
        $res='';
        foreach($tab as $key=>$name)
        {
            $res .= "<option";
            if($display_value!='' && $display_value!='KEY')
            {
                if($save_value!='')
                {
                    $res .= " value='".$name[$value]."' ";
                }
                if($default!='')
                {
                    if($name[$save_value]==$default)
                    $res .= "selected ";
                }
                $res .= ">";
                $res.=$name[$display_value];
            }
            else if($display_value=='KEY')
            {
                $res .= " value='$key'>$name";
            }
            else
            {
                $res .= ">$name</option>";
            }
            $res .= "</option>";
        }
        return $res;
    }

    public static function combo_contents_obj($tab,$display_value,$save_value, $default=''){
        $res='';
        //Site::debug($tab);
        if(is_array($tab))
        {
            foreach($tab as $obj)
            {
                $res .= "<option ".($obj->getAttr($save_value)==$default?"selected":"")." value='".$obj->getAttr($save_value)."'>".
                    (method_exists($obj,$display_value)?$obj->$display_value():$obj->getAttr($display_value)).
                    "</option>";
            }
        }
        else if($tab!='')
        {
            $res .= "<option ".($tab->getAttr($save_value)==$default?"selected":"")." value='".$tab->getAttr($save_value)."'>".
                (method_exists($tab,$display_value)?$tab->$display_value():$tab->getAttr($display_value)).
                "</option>";
        }
        return $res;
    }
/*
  static function md2php($d){
    if ($d!="")
    {
      return @mktime(substr($d, 11, 2)
        , substr($d, 14, 2)
        , substr($d, 17, 2)
        , substr($d, 5, 2)
        , substr($d, 8, 2)
        , substr($d, 0, 4));
    }
    else
    {
      return "";
    }
  }*/
  
  static function setNextUrl(){
    $_SESSION['next_url'] = WEBDIR.substr($_SERVER['REQUEST_URI'],1);
  }
  
  static function goToNextUrl(){
    Site::redirect($_SESSION['next_url']);
    unset($_SESSION['next_url']);
    exit();
  }
  
  static function isNextUrl(){
    if($_SESSION['next_url']!='')
      return true;
    else
      return false;
  }
  
	static function multiexplode ($delimiters,$string) {
		$ready = str_replace($delimiters, $delimiters[0], $string);
		$launch = explode($delimiters[0], $ready);
		return  $launch;
	}
  
	static function getLevenshteinCoeff($str){
		return floor(strlen($str)/2);
	}
  
  static function getWidget($name,$params=array()){
		$widget_id = self::generate_uniquid(10);
    echo "<div class='widget'>";
    $GLOBALS['params']= $params;
    if(!empty($name) && is_file('widget/'.$name.'.php'))
      include 'widget/'.$name.'.php';
    else
      return "Widget $name not Found.";
    echo "</div>";
  }
  
	static function getWidgetParams($key,$default){
		$params=$GLOBALS['params'];
		if(@$params[$key]!='')
			return $params[$key];
		else
			return $default;
	}

    public static function arrayToList($array){
        $list = '';
        $cpt = 0;
        foreach($array as $key=>$a)
        {
            $cpt++;
            $list .= $a;
            if($cpt!=sizeof($array))
            {
                $list .= ',';
            }
        }
        return $list;
    }

    public static function addDevError($error){
        if(DEVMODE)
        {
            echo "<br />********<br />";
            echo $error;
            echo "<br />********<br />";
        }
    }

    public static function saveData($name,$data){
        $_SESSION[$name] = serialize($name);
    }

    public static function loadData($name){
        return unserialize($_SESSION[$name]);
    }

    public static function eraseData($name){
        unset($_SESSION[$name]);
    }


};
?>