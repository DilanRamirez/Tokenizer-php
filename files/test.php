
    <?php
        require('tokenizer2.php');
        $EOL = PHP_EOL;
        $header = '<html>' . $EOL . "  <head>" . $EOL . "    <title>CS 4339/5339 PHP assignment</title>"
        . $EOL. "  </head>" . $EOL . "  <body>" . $EOL . "    <pre>".$EOL;
        $footer = "    </pre>" . $EOL . "  </body>" . $EOL . "</html>";
        $URL = 'http://localhost/assignment1/fall20Testing.txt';
        $inputSource = file($URL);   

        $inputFile = "";
        foreach($inputSource as $line) {
            $inputFile =  $inputFile . $line;
        }

        $tokens = explode("[",$inputFile);

        unset($tokens[0]);

        foreach($tokens as $token){
            echo 'section '.++$i.$EOL.'['.$EOL;
            Tokenizer($token);
        }



    ?>