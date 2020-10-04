<?php
    require("Tokenizer.php");
    require("evalSectionException.php");

    class Fall2020_PHP_Program {
        static $currentToken;
        static $t;
        static $map = array();
        static $oneIndent = "   ";
        static $result;
        static $EOL = PHP_EOL;
        public static function main(){
            $URL = 'http://localhost/assignment1/fall20Testing.txt';
            // read file
            $inputSource = file($URL);

            $header = "<html>" . self::$EOL
        . "  <head>" . self::$EOL
        . "    <title>CS 4339/5339 PHP assignment</title>" . self::$EOL
        . "  </head>" . self::$EOL
        . "  <body>" . self::$EOL
        . "    <pre>" . self::$EOL;

            $footer = "    </pre>" . self::$EOL
        . "  </body>" . self::$EOL
        . "</html>";
            $inputFile = "";

            $inputFile = "";
            foreach($inputSource as $line) {
                $inputFile =  $inputFile . $line;
            }
            self::$currentToken= new Token("","");
            self::$t = new Tokenizer($inputFile);
            print($header);
            self::$currentToken = self::$t->nextToken();
            $section = 0;

            while (self::$currentToken->type != "EOF") {
                echo "section " . ++$section;
                try {
                    self::evalSection();
                    echo "Section Result:" . self::$EOL;
                    echo self::$result . "" . self::$EOL;
                } catch (EvalSectionException $ex) {
                    while (self::$currentToken->type != "RSQUAREBRACKET" && self::$currentToken->type != "EOF") {
                        self::$currentToken = self::$t->nextToken();
                    }
                    self::$currentToken = self::$t->nextToken();
                }
            }
            echo $footer . "" . self::$EOL;
        }

        public static function evalSection(){
            self::$result = "";
            if (self::$currentToken->type != "LSQUAREBRACKET"){
                throw new EvalSectionException("A section must start with \"[\"");
            }
            echo self::$EOL . "[" . self::$EOL;
            self::$currentToken = self::$t->nextToken();
            while (self::$currentToken->type != "RSQUAREBRACKET" && self::$currentToken->type != self::$EOL){
                self::evalStatement(self::$oneIndent, true);
            }
            echo "]" . self::$EOL;
            self::$currentToken = self::$t->nextToken();
        }

        public static function evalStatement($indent, $exec){
            switch (self::$currentToken->type){
                case "ID":
                    self::evalAssignment($indent,$exec);
                    break;
                case "SWITCH":
                    self::evalSwitch($indent,$exec);
                    break;
                case "OUTPUT":
                    self::evalOutput($indent,$exec);
                    break;
                default:
                    throw new EvalSectionException("invalid statement");
            }
        }
        public static function evalAssignment($indent, $exec){
            $key = self::$currentToken->value;
            echo $indent . $key;
            self::$currentToken = self::$t->nextToken();
            if(self::$currentToken->type != "EQUAL"){
                throw new EvalSectionException("equal sign is expected");
            }
            echo "=";
            self::$currentToken = self::$t->nextToken();
            if(self::$currentToken->type == "INT"){
                $value = self::$currentToken->value;
                echo $value . "" . self::$EOL;
                self::$currentToken = self::$t->nextToken();
                if ($exec){
                    self::$map[$key]= $value;
                }
            }
            else if (self::$currentToken->type == "ID"){
                $key2 = self::$currentToken->value;
                echo $key2 . "" . self::$EOL;
                self::$currentToken = self::$t->nextToken();
                if($exec){
                    if (!array_key_exists($key2, self::$map)){
                        throw new EvalSectionException("undefined variable");
                    }
                    $value = self::$map[$key2];
                    self::$map[$key]=$value;
                }
            }
            else{
                throw new EvalSectionException("ID or Integer expected");
            }
        }
        public static function evalOutput($indent, $exec){
            echo $indent . "output ";
            self::$currentToken = self::$t->nextToken();
            switch (self::$currentToken->type){
                case "STRING":
                    if ($exec){
                        self::$result .= self::$currentToken->value . self::$EOL;
                    }
                    echo "\"" . str_replace("\"","\\\"",self::$currentToken->value) . "\"" . self::$EOL;
                    self::$currentToken = self::$t->nextToken();
                    break;
                case "INT":
                    if ($exec){
                        self::$result .= self::$currentToken->value . self::$EOL;
                    }
                    echo self::$currentToken->value . "" . self::$EOL;
                    self::$currentToken = self::$t->nextToken();
                    break;
                case "ID":
                    $key = self::$currentToken->value;
                    echo $key . "" . self::$EOL;
                    if($exec){
                        if(!array_key_exists($key, self::$map)){
                            throw new EvalSectionException("undefined variable");
                        }
                        $value = self::$map[$key];
                        self::$result .= $value . self::$EOL;
                    }
                    self::$currentToken = self::$t->nextToken();
                    break;
                default:
                    throw new EvalSectionException("expected a string, integer, or ID");
            }
        }
        public static function evalSwitch($indent, $exec){
            echo $indent . "switch ";
            self::$currentToken = self::$t->nextToken();
            if (self::$currentToken->type != "ID"){
                throw new EvalSectionException("ID expected");
            }
            $key =self::$currentToken->value;
            echo $key;
            if($exec){
                if(!array_key_exists($key, self::$map)){
                    throw new EvalSectionException("undefined variable");
                }
                $value = self::$map[$key];
            }
            self::$currentToken = self::$t->nextToken();
            if(self::$currentToken->type != "LBRACKET"){
                throw new EvalSectionException("Left bracket expected");
            }
            echo " {" . self::$EOL;
            self::$currentToken = self::$t->nextToken();
            while(self::$currentToken->type == "CASE"){
                self::$currentToken = self::$t->nextToken();
                echo $indent . self::$oneIndent . "case ";
                $exec = self::evalCase($indent . self::$oneIndent . self::$oneIndent, $exec, $value); 
            }
            if(self::$currentToken->type == "DEFAULT"){
                echo $indent . self::$oneIndent . "default";
                self::$currentToken = self::$t->nextToken();
                if(self::$currentToken != "COLON"){
                    throw new EvalSectionException("colon expected");
                }
                echo ":" . self::$EOL;
                self::$currentToken = self::$t->nextToken();
                while(self::$currentToken->type != "RBRACKET"){
                    self::evalStatement($indent . self::$oneIndent . self::$oneIndent, $exec);
                }
            }
            if (self::$currentToken->type == "RBRACKET"){
                echo $indent . "}" . self::$EOL;
                self::$currentToken = self::$t->nextToken();
            }
            else {
                throw new EvalSectionException("right bracket expected");
            }
        }
        public static function evalCase($indent, $exec, $target){
            if (self::$currentToken->type != "INT"){
                throw new EvalSectionException("Integer Expected");
            }
            $value = self::$currentToken->value;
            echo $value;
            self::$currentToken = self::$t->nextToken();
            if (self::$currentToken->type != "COLON"){
                throw new EvalSectionException("colon expected");
            }
            echo ":" . self::$EOL;
            self::$currentToken = self::$t->nextToken();
            while (self::$currentToken->type != "BREAK"){
                self::evalStatement($indent, $exec && $value == $target);
            }
            echo $indent . "break" . self::$EOL;
            self::$currentToken = self::$t->nextToken();
            return $exec && !($value == $target);
        }
    }
    $newInstance = new Fall2020_PHP_Program;
    $main = $newInstance ->main();
?>