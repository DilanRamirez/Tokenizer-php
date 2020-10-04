
    <?php 
        require('Tokenizer.php');
        require("EvalSectionException.php");

        class Fall2020_PHP_Program {

            static $currentToken;
            static $t;
            static $map = array();
            static $oneIndent = '   ';
            static $result; // string containing the result of execution
            static $EOL = PHP_EOL; // new line, depends on OS

            public static function main(){
                // Test file stored on localhost
                $URL = 'http://localhost/assignment1/fall20Testing.txt';
                // read file
                $inputSource = file($URL);

                $header = '<html>' . self::$EOL . "  <head>" . self::$EOL .
                "    <title>CS 4339/5339 PHP assignment</title>".
                self::$EOL. "  </head>" . self::$EOL . "  <body>" . self::$EOL . "    <pre>". self::$EOL;

                $footer = "    </pre>" . self::$EOL . "  </body>" . self::$EOL . "</html>";

                // generate a string with the file
                $inputFile = "";
                foreach($inputSource as $line) {
                    $inputFile =  $inputFile . $line;
                }

                self::$currentToken = new Token("","");
                self::$t = new Tokenizer($inputFile);
                print $header;
                self::$currentToken = self::$t -> nextToken();
                $section = 0;

                // Loop through all sections, for each section printing result
                // If a section causes exception, catch and jump to next section
                while(self::$currentToken->type !== "EOF") {
                    echo 'section' . ++$section;
                    try {
                        self::evalSection();
                        echo "Section results:" . self::$EOL;
                        echo self::$result. " ". self::$EOL;
                    } catch (EvalSectionException $ex) {
                        // skip to the end of section
                        while (self::$currentToken->type != "RSQUAREBRACKET" && self::$currentToken->type  != "EOF") {
                            self::$currentToken = self::$t->nextToken();
                        }
                        self::$currentToken = self::$t->nextToken();
                    }
                }
            }

            public static function evalSection() {
                // <section> ::= [ <statement>* ]
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

            public static function evalStatement ($indent, $exec) {
                // exec is true if we are executing the statements in addition to parsing
                // <statement> ::= <assignment> | <switch> | <output>
                switch (self::$currentToken->type) {
                    case "ID":
                        self::evalAssignment($indent, $exec);
                        break;
                    case "SWITCH":
                        self::evalSwitch($indent, $exec);
                        break;
                    case "OUTPUT":
                        self::evalOutput($indent, $exec);
                        break;
                    default:
                        throw new EvalSectionException("invalid statement");
                }
            }

            public static function evalAssignment($indent, $exec) {
                // <assignment> ::= ID '=' (INT | ID)
                // we know currentToken is ID
                $key = self::$currentToken->value;
                echo $indent . $key;
                self::$currentToken = self::$t->nextToken();
                if (self::$currentToken->type != "EQUAL") {
                    throw new EvalSectionException("equal sign expected");
                }
                echo "=";
                self::$currentToken = self::$t->nextToken();
                if (self::$currentToken->type == "INT") {
                    $value = self::$currentToken->value;
                    echo $value . " " . self::$EOL;
                    self::$currentToken = self::$t->nextToken();
                    if ($exec) {
                        self::$map[$key] = $value;
                    }
                } else if (self::$currentToken->type == "ID") {
                    $key2 = self::$currentToken->value;
                    echo $key2 . " " . self::$EOL;
                    self::$currentToken = self::$t->nextToken();
                    if ($exec) {
                        if (!array_key_exists($key2, self::$map)){
                            throw new EvalSectionException("undefined variable");
                        }
                        $value = self::$map[$key2];
                        self::$map[$key] = $value;
                    }
                } else {
                    throw new EvalSectionException("ID or Integer expected");
                }
            }

            public static function evalOutput($indent, $exec) {
                // <output> ::= 'output' (INT | ID | STRING)
                // we know currentToken is 'output'
                echo $indent . "output ";
                self::$currentToken = self::$t->nextToken();
                // <value> ::= INT | ID | STRING
                switch (self::$currentToken->type) {
                    case "STRING":
                        if ($exec) {
                            self::$result = self::$result . self::$currentToken->value . self::$EOL;
                        }
                        // To print exactly the input, we need to re-escape the quotes in the string
                        echo "\"" . str_replace("\"","\\\"",self::$currentToken->value) . "\"" . self::$EOL;
                        self::$currentToken = self::$t->nextToken();
                        break;
                    case "INT":
                        if ($exec) {
                            self::$result = self::$result . self::$currentToken->$value . self::$EOL;
                        }
                        echo self::$currentToken->value;
                        self::$currentToken = self::$t->nextToken();
                        break;
                    case "ID":
                        $key = self::$currentToken->value;
                        echo $key . " " . self::$EOL;
                        if ($exec) {
                            // value associated with ID
                            if(!array_key_exists($key, self::$map)){
                                throw new EvalSectionException("undefined variable");
                            }
                            $value = self::$map[$key];
                            self::$result = self::$result . $value . self::$EOL;
                        }
                        self::$currentToken = self::$t->nextToken();
                        break;
                    default:
                        throw new EvalSectionException("expected a string, integer, or Id");
                }
            }

            public static function evalSwitch($indent, $exec){
                // <switch> ::= 'switch' ID '{' <case>* [ 'default' ':' <statement>* ] '}'
                // We know currentToken is "switch"
                echo $indent . "switch ";
                self::$currentToken = self::$t->nextToken();
                if (self::$currentToken->type != "ID"){
                    throw new EvalSectionException("ID expected");
                }
                $key = self::$currentToken->value;
                echo $key;
                if($exec){
                    if(!array_key_exists($key, self::$map)){
                        throw new EvalSectionException("undefined variable");
                    }
                    // value of the switch ID
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

            public static function evalCase($indent, $exec, $target) {
                // <case> ::= 'case' 'INT' ':' <statement>* 'break'
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

                // only one case is executed
                return $exec && !($value == $target);
            }
        }

        $newInstance = new Fall2020_PHP_Program;
        $main = $newInstance ->main();
    ?>