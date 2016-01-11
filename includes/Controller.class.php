<?php
    class Controller {

        public $content = NULL;
        public $edition_prefixe = false;

        public function __construct(){
            $this->action();
        }

        public function getEditionPrefixe(){
            return $this->edition_prefixe;
        }

        public function isContentDefined(){
            if($this->content==NULL)
                return false;
            else
                return true;
        }

        protected function action(){
        }

        public function getDynamicText(){
            if($this->edition_prefixe!=false)
            {
                Manager::getInstance()->selectAllFrom("content")->where("cont_name LIKE '".$this->edition_prefixe."%'")->query();
                $this->content = Manager::getInstance()->getObjects("content");
                foreach($this->content as $c)
                {
                    $this->content[$c->getAttr('name')]=$c;
                }
            }
            else
            {
                Site::addDevError("Il faut remplir le préfixe édition du controller avant d'appeler getDynamicText()");
            }
        }

        public function printText($name){
            if(isset($_SESSION["edition"]))
                return "<span data-id='".$name."' id='".$name."' class='edition' contenteditable='true'>".$this->content[$name]->getAttr('text')."</span>";
            else
                return $this->content[$name]->getAttr('text');
        }

    }
?>