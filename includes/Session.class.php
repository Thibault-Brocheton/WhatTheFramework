<?php
class Session
{
	private static $me = null ;

	private function __construct (){
	}

	public function setMe($object){
		self::$me = $object;
	}

	public static function Me(){
		return self::$me;
	}
	
	public static function MyId(){
		return self::$me->getAttr('id');
	}
  
	public static function Login(){
    if(@$_COOKIE[MODLOGIN_LOGIN]!='' || @$_POST[MODLOGIN_LOGIN]!='')
    {
      $new_connection=true;
      @$login = $_POST[MODLOGIN_LOGIN];
      @$pass = Site::crypt_pwd($_POST[MODLOGIN_PASSWORD]);
      @$keep = $_POST['keep'];
      if(@$_COOKIE[MODLOGIN_LOGIN]!='')
      {
        $new_connection=false;
        $login = $_COOKIE[MODLOGIN_LOGIN];
        $pass = $_COOKIE[MODLOGIN_PASSWORD];
      }

      $login = DB::ProtectData($login);
        $class = MODLOGIN_OBJECT;
        $pref = $class::getStaticPref();
      //Récupération du mot de passe de la bdd lié au compte $login
      $pass_crypt = DB::SqlOne("select ".$pref.MODLOGIN_PASSWORD." from ".DBPRE.MODLOGIN_OBJECT." where ".$pref.MODLOGIN_LOGIN."='$login'");
      //vérification des conditions de connexion (compte existant et mot de passe valide)
      if($pass_crypt!='' && $pass==$pass_crypt)
      {
        if($new_connection)
        {
          if($keep==1)
          {
            setcookie(MODLOGIN_LOGIN,$login,time()+31536000,'/');
            setcookie(MODLOGIN_PASSWORD,$pass,time()+31536000,'/');
          }
          else
          {
            setcookie(MODLOGIN_LOGIN,$login,0,'/');
            setcookie(MODLOGIN_PASSWORD,$pass,0,'/');
          }
          
          //Link du compte Facebook
          /*if($_SESSION['fb_action']=='link')
          {
            user::LinkFacebook($login);
          }*/
          
          //Redirection vers la page qui nous amenait à nous connecter, ou sur l'accueil
          if(Site::isNextUrl())
            Site::goToNextUrl();
          else
            Site::redirect(WEBDIR);
          exit();
        }
        else
        {
           Session::setMe(Manager::getInstance()->selectAll(MODLOGIN_OBJECT)->where($pref.MODLOGIN_LOGIN."='$login'")->query()[0][MODLOGIN_OBJECT]);
        }
      }
      else
      {
        if($new_connection)
        {
          Site::message_info("Les informations de connexion sont fausses. Vérifiez votre nom d'utilisateur et votre mot de passe. Avez vous bien créé un compte sur ce site ?",'ERROR');
        }
        else
        {
          setcookie(MODLOGIN_LOGIN,'',0,'/');
          setcookie(MODLOGIN_PASSWORD,'',0,'/');
          Site::message_info("Session expirée, vous avez été déconnecté.",'WARNING');
          Site::redirect(WEBDIR);
          exit();
        }
      }
    }
  }
  
  public static function Logout(){
    if(self::$me!=null)
    {
      setcookie(MODLOGIN_LOGIN,'',0,'/');
      setcookie(MODLOGIN_PASSWORD,'',0,'/');
      Site::redirect(WEBDIR);
      exit();
    }
  }
  
  
  public static function Online(){
    if(self::$me==null)
      return false;
    else
      return true;
  }
  
  public static function IsAdmin(){
    if(self::$me->getAttr('level')=='2')
        return true;
    else
        return false;
    }

    public static function IsPremium(){
        if(self::$me->getAttr('level')=='1')
            return true;
        else
            return false;
    }

    /*
  public static function LoginAfterRegister(){
    if(@$_SESSION['register_email']!='' && @$_SESSION['register_pass']!='')
    {
      setcookie('email',$_SESSION['register_email'],0,'/');
      setcookie('pass',$_SESSION['register_pass'],0,'/');
      unset($_SESSION['register_email']);
      unset($_SESSION['register_pass']);
      Site::redirect(WEBDIR);
      exit();
    }
  }
  
  public static function FacebookConnect($user_id){
    $infos = user::getEmailPass($user_id);
    setcookie('email',$infos['user_email'],time()+31536000,'/');
    setcookie('pass',$infos['user_password'],time()+31536000,'/');
    unset($_SESSION['fb_action']);
    unset($_SESSION['fb_id']);
    unset($_SESSION['fb_name']);
    unset($_SESSION['fb_profile_picture']);
    Site::redirect(WEBDIR);
    exit();
  }*/
  
  
}
?>