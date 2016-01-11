<?php

if(Site::get_slug()=='create') {

    $name = Site::get_token();



    $cols = DB::SqlToArray("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".DBPRE."$name'");
    $pref = explode('_',$cols[0]['COLUMN_NAME']);
    $pref = $pref[0];

    $references = "";
    $ref = DB::SqlToArray("SELECT DISTINCT k.CONSTRAINT_SCHEMA, k.CONSTRAINT_NAME, k.TABLE_NAME, k.COLUMN_NAME
     , k.REFERENCED_TABLE_SCHEMA, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS k
INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS c
       ON k.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
WHERE c.CONSTRAINT_TYPE = 'FOREIGN KEY' and k.TABLE_NAME = '".DBPRE.$name."'");

    foreach($ref as $key=>$r)
    {
        $references .= "\"".$r['COLUMN_NAME']."->".str_replace(DBPRE,'',$r['REFERENCED_TABLE_NAME']."\"");
        if($key<sizeof($ref)-1)
            $references .= ",";
    }

    $referenced = "";
    $refd = DB::SqlToArray("SELECT DISTINCT k.CONSTRAINT_SCHEMA, k.CONSTRAINT_NAME, k.TABLE_NAME, k.COLUMN_NAME
     , k.REFERENCED_TABLE_SCHEMA, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS k
INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS c
       ON k.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
WHERE c.CONSTRAINT_TYPE = 'FOREIGN KEY' and k.REFERENCED_TABLE_NAME = '".DBPRE.$name."'");

    foreach($refd as $key=>$r)
    {
        $referenced .= "\"".$r['COLUMN_NAME']."<-".str_replace(DBPRE,'',$r['TABLE_NAME']."\"");
        if($key<sizeof($refd)-1)
            $referenced .= ",";
    }

    $primary = "";
    $prim = DB::SqlToArray("SELECT DISTINCT k.CONSTRAINT_SCHEMA, k.CONSTRAINT_NAME, k.TABLE_NAME, k.COLUMN_NAME
     , k.REFERENCED_TABLE_SCHEMA, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS k
INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS c
       ON k.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
WHERE c.CONSTRAINT_TYPE = 'PRIMARY KEY' and k.TABLE_NAME = '".DBPRE.$name."'");

    foreach($prim as $key=>$r)
    {
        $primary .= "\"".$r['COLUMN_NAME']."\"";
        if($key<sizeof($prim)-1)
            $primary .= ",";
    }

    $text = "<?php\n";
    $text .= "  class $name extends root {\n";
    $text .= "    //SqlAttributs\n";
    foreach($cols as $c)
    {
        $text .= "    protected \$".$c['COLUMN_NAME'].";\n";
    }
    $text .= "\n    //NonSqlAttributs\n\n";
    $text .= "    //PrimaryAttributs\n";
    $text .= "    public static \$primary = array($primary);\n\n";
    $text .= "    //Prefixe\n";
    $text .= "    public static \$pref = \"".$pref."_\";\n\n";
    $text .= "    //Exclusion\n";
    $text .= "    public static \$exclusion = array();\n\n";
    $text .= "    //References\n";
    $text .= "    public static \$references = array($references);\n";
    $text .= "    public static \$referenced = array($referenced);\n\n";
    $text .= "    //Functions\n\n";
    $text .= "  }\n";
    $text .= "?>";

    $monfichier = fopen("classes/$name.class.php", 'w+');
    fseek($monfichier, 0); // On remet le curseur au début du fichier
    fputs($monfichier, $text); // On écrit le nouveau nombre de pages vues
    fclose($monfichier);

    //echo "<textarea style='width:500px;height:500px;'>$text</textarea>";
    Site::redirect(WEBDIR."manageClasses");
    exit();
}
else
{
    echo "WARNING: verifier ordonnancement des references et des referenced pour les multi liaisons d'une table sur une autre";
    $rs = DB::SqlToArray("SELECT table_name FROM information_schema.tables WHERE table_schema = '".DB_BASE."'");
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />";
    echo "Classes: <br />";
    foreach($rs as $r)
    {
        $r = $r['table_name'];
        $r = str_replace(DBPRE,"",$r);
        if(file_exists("classes/".$r.".class.php"))
        {
            echo "<span color='green'>$r</span><br />";
        }
        else
        {
            echo "<span color='red'>$r</span> - <a href='".WEBDIR."manageClasses/create/$r'>Créer la classe</a><br />";
        }
    }
}
?>