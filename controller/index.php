<?php
    class index extends Controller {

        public function action(){
            //$this->edition_prefixe = "ACCUEIL";
            //$this->getDynamicText();
            Site::include_view("index");
        }


    }

    $index = new index();
    Site::setController($index);
?>