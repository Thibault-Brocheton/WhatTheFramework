<?php

    class Error {

        protected $error_array = array();

        public function __construct(){

        }

        public function addDifferentOf($value,$test,$id_error='',$error=''){
            array_push($this->error_array,array('DIFFERENT',$value,$test,$id_error,$error));
            return $this;
        }

        public function addNotNull($value,$id_error='',$error=''){
            array_push($this->error_array,array('NOT_NULL',$value,$id_error,$error));
            return $this;
        }

        public function addMinChar($char,$value,$id_error='',$error=''){
            array_push($this->error_array,array('MIN_CHAR',$char,$value,$id_error,$error));
            return $this;
        }

        public function addMaxChar($char,$value,$id_error='',$error=''){
            array_push($this->error_array,array('MAX_CHAR',$char,$value,$id_error,$error));
            return $this;
        }

        public function addMinInt($min,$value,$id_error='',$error=''){
            array_push($this->error_array,array('MIN_INT',$min,$value,$id_error,$error));
            return $this;
        }

        public function addMaxInt($max,$value,$id_error='',$error=''){
            array_push($this->error_array,array('MAX_INT',$max,$value,$id_error,$error));
            return $this;
        }

        public function addEmail($value,$id_error='',$error=''){
            array_push($this->error_array,array('REGEXP',REGEXP_MAIL,$value,$id_error,$error));
            return $this;
        }

        public function addDate($value,$id_error='',$error=''){
            array_push($this->error_array,array('REGEXP',REGEXP_DATE,$value,$id_error,$error));
            return $this;
        }

        public function addRegExp($regexp,$value,$id_error,$error){
            array_push($this->error_array,array('REGEXP',$regexp,$value,$id_error,$error));
            return $this;
        }

        public function test(){
            $no_error = true;
            foreach($this->error_array as $e)
            {
                switch($e[0]){
                    case 'DIFFERENT':
                        if($e[1]==$e[2])
                        {
                            if($e[3]!='')
                                Site::addError($e[3],$e[4]);
                            $no_error = false;
                        }
                        break;
                    case 'NOT_NULL':
                        if($e[1]=='')
                        {
                            if($e[2]!='')
                                Site::addError($e[2],$e[3]);
                            $no_error = false;
                        }
                        break;
                    case 'MIN_CHAR':
                        if(strlen($e[2])<$e[1])
                        {
                            if($e[3]!='')
                                Site::addError($e[3],$e[4]);
                            $no_error = false;
                        }
                        break;
                    case 'MAX_CHAR':
                        if(strlen($e[2])>$e[1])
                        {
                            if($e[3]!='')
                                Site::addError($e[3],$e[4]);
                            $no_error = false;
                        }
                        break;
                    case 'MIN_INT':
                        if($e[2]<$e[1])
                        {
                            if($e[3]!='')
                                Site::addError($e[3],$e[4]);
                            $no_error = false;
                        }
                        break;
                    case 'MAX_INT':
                        if($e[2]>$e[1])
                        {
                            if($e[3]!='')
                                Site::addError($e[3],$e[4]);
                            $no_error = false;
                        }
                        break;
                    case 'REGEXP':
                        if(!preg_match($e[1],$e[2]))
                        {
                            if($e[3]!='')
                                Site::addError($e[3],$e[4]);
                            $no_error = false;
                        }
                        break;
                    default:
                        Site::addDevError("La classe Error a été mal utilisée. Référence d'erreur: ".$e[0]);
                        break;
                }
            }
            $this->error_array = array();
            return $no_error;
        }

    }

?>