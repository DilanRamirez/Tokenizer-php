<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>assignment1</title>
</head>
<body>
    <?php 
        $URL = 'http://localhost/assignment1/fall20Testing.txt';
        $inputSource = file($URL);   


        $inputFile = "";
        foreach($inputSource as $line) {
            $inputFile =  $inputFile . $line;
        }

        function Tokenizer($s){
            $e = str_split($s);
            return $e;
        }

        function Token1($theType){
            $type = $theType;
            $value = "";
        }

        function Token2($theType, $theValue){
            $type = $theType;
            $value = $theValue;
        }

        function nextToken($t) {
            $i = 0;
            while($i < count($t) && strpos($t[$i],"\n\t\r")){
                $i++;
            }
            if ($i < count($t)) {
                return Token2(PHP_EOL,"");
            }

            $inputString_nexToken = "";
            while ($i < count($t) && strpos($t[$i],"0123456789") >= 0) {
                $inputString = $inputString . $t[$i++];
            }
            if (strcmp("", $inputString_nexToken) !== 0) {
                return Token2("INT",$inputString_nexToken);
            }
            while ($i < count($t) && strpos($t[$i],"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_") >= 0) {
                $inputString = $inputString . $t[$i++];
            }
            if (strcmp("", $inputString_nexToken) !== 0) {
                if(strcmp("output", $inputString_nexToken) === 0) {
                    return Token1("OUTPUT");
                }
                if(strcmp("switch", $inputString_nexToken) === 0) {
                    return Token1("SWITCH");
                }
                if(strcmp("case", $inputString_nexToken) === 0) {
                    return Token1("CASE");
                }
                if(strcmp("break", $inputString_nexToken) === 0) {
                    return Token1("BREAK");
                }
                if(strcmp("default", $inputString_nexToken) === 0) {
                    return Token1("DEFAULT");
                }

                return Token2("ID",$inputString_nexToken);
            }
            switch ($t[$i++]) {
                case '{':
                    return Token2("LBRACKET","{");
                    case '}':
                        return Token2("RBRACKET","}");                
                    case '[':
                        return Token2("LSQUAREBRACKET","[");
                    case ']':
                        return Token2("RSQUAREBRACKET","]");
                    case '=':
                        return Token2("EQUAL","=");
                    case ':':
                        return Token2("COLON",":");
                    case '"':
                        $value="";
                        while ($i < count($t) && $t[$i]!='"'){
                            $c = $t[$i++];
                            if($i < count($t)){
                                return Token1("OTHER");
                            } 
                                // check for escaped double quote
                            if ($c ==='\\' && $t[$i] === '"'){
                                $c='"';
                                $i++;
                            }
                            $value = $value . $c;
                        } 
                        $i++;
                        return Token2("STRING", $value);
                default:
                    return Token1("OTHER");
            }
        }
        
        $t = Tokenizer($inputFile);
        $currentToken = nextToken($t);

    
    ?>
</body>
</html>