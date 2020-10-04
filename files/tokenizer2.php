<?php 
    $EOL = PHP_EOL;

    function Tokenizer ($line) {
        $tokens= str_split($line);
        if(strlen($line) > 2){
            unset($tokens[sizeof($tokens) - 1]);
            $stringParsed = implode(array_slice($tokens,0,sizeof($tokens) - 1));
            echo evalStatement($stringParsed)."\n".']'."\n".'Section result:'."\n".sectionResult()."\n\n";
        }
        else {
            echo 'Exception: invalid statement';
        }
    }

    function sectionResult(){
        return '***';
    }

    function evalStatement($statement){
        echo '  '.$statement;        
    }
?>