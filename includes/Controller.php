<?php
if(strtolower(Site::get_page())=='index')
{
    Site::redirect(WEBDIR);
    exit();
}
if(Site::get_page()!='' && is_file('controller/'.Site::get_page().'.php'))
	include 'controller/'.Site::get_page().'.php';
else if(Site::get_page()=='')
  include 'controller/index.php';
else
	include('pages/404.php');
if(Manager::isInstancied())
{
    if(Manager::getInstance()->shouldEditDataBase())
	    Manager::getInstance()->EditDataBase();
}

Site::doIfRedirect();

if(Site::is_view_defined())
{
    //Inclure le header, sauf si la requête est AJAX
    if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
    {
		header('Content-type:text/html; charset=utf-8');
		?>

		<html lang="fr">
			<head>

				  <!-- Meta -->
				  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

				  <!-- Inclusions CSS -->
				   <?php
						foreach(Site::getStyle() as $s)
						{
							 echo "<link rel='stylesheet' type='text/css' href='".WEBDIR."css/".$s.".css' />";
						}
					?>

				  <!-- Inclusions JS -->
				  <script src="<?=WEBDIR?>js/jquery-2.1.1.js"></script>
                  <script src="<?=WEBDIR?>js/jquery.form.js"></script>
                  <script src="<?=WEBDIR?>js/download.js"></script>
                  <script src="<?=WEBDIR?>lib/ckeditor/ckeditor.js"></script>

				  <!-- Title -->
				  <title>Fete de la Science à l'UTC</title>
			 
			</head>
        <?php
        if(Site::isHeader())
        {
            include('pages/header.php');
            Site::printMessages();
        }
    }

    //Ajouter le header Access-Control-Allow-Origin pour le requetes AJAX
    /*if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        header("Access-Control-Allow-Origin: ".DOMAIN);
    }*/

    foreach(Site::get_view() as $tab)
    {
        include('pages/'.strtolower(Site::get_page()).'/'.$tab.'.php');
    }

    if(Site::isAutoCompleteForm())
    {
        ?>
        <script>
            $(document).ready(function(){
                var post = jQuery.parseJSON('<?=json_encode($_POST)?>');
                $("input").each(function(){
                    if($(this).attr("name")!='')
                    {
                        if($(this).attr("type")=="checkbox" || $(this).attr("type")=="radio")
                        {
                            if($(this).attr("value")==post[$(this).attr("name")])
                                $(this).attr("checked","checked");
                        }
                        else
                        {
                            if(post[$(this).attr("name")]!='' && post[$(this).attr("name")]!=null && post[$(this).attr("name")]!=undefined)
                            {
                                $(this).val(post[$(this).attr("name")]);
                            }
                        }
                    }
                });
                $("select").each(function(){
                    var select = $(this);
                    $(this).children("option").each(function(){
                        if($(this).attr("value")==post[select.attr("name")])
                        {
                            $(this).attr("selected","selected");
                        }
                    });
                });
                $("textarea").each(function(){
                    if($(this).attr("name")!='')
                    {
                        if(post[$(this).attr("name")]!='' && post[$(this).attr("name")]!=null && post[$(this).attr("name")]!=undefined)
                        {
                            $(this).val(post[$(this).attr("name")]);
                        }
                    }
                });
            });
        </script>
        <?php
    }

    //Inclure le footer, sauf si la requête est AJAX
    if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
    {
        if(Site::isFooter())
            include('pages/footer.php');
        echo "</html>";
    }
}

//Suppression des messages d'erreurs, alertes, succès et infos.
Site::clearMessages();

?>