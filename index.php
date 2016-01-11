<?php
	require_once("includes/Params.ini.php");
	require_once("includes/Autoload.php");

	$page = (isset($_GET['page'])?$_GET['page']:null);
	$slug = (isset($_GET['slug'])?$_GET['slug']:null);
	$token = (isset($_GET['token'])?$_GET['token']:null);
	
	Site::setUrlParameters($page,$slug,$token);
	
	if(MODLOGIN_ACTIVATE)
	{
		Session::Login();
		if(Site::get_page()=='logout' && Session::Online())
			Session::Logout();
		//Session::LoginAfterRegister();
	}
  
	require_once('includes/Controller.php');

?>