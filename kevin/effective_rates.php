<?php
    $local = 0;
    if(!isset($_SERVER['REMOTE_ADDR'])){
        echo "Running Local<br>";
        $local = 1;
    }
    if($local == 0){
        $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
            
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error()."<br>";
            exit();
        }
    }

    $y = "20".(isset($_GET['year']) ? $_GET['year'] :date('y'));
    $m = sprintf("%02d", isset($_GET['month']) ? $_GET['month'] :date('m')-1);
    $sql = 'SELECT 1 FROM effective_rate WHERE month_num = \''.$m.'\' AND year_num = '.$y.';';

    if ($local == 0 && $result = mysqli_query($con, $sql)){
        if (mysqli_num_rows($result) != 0) { 
            echo "HECO effective rates already up to date<br>";
            exit();
        }
        echo "Updating HECO effective rates<br>";
    }

    $url_base = 'https://www.hawaiianelectric.com/documents/billing_and_payment/rates/effective_rate_summary/efs_';
    $url = $url_base.$y.'_'.$m.'.pdf';

    if($local == 0 && !file_put_contents('file.pdf', fopen($url, 'r'))){
        echo "Could not find: ".$url."<br>";
        exit();
    }

    function getCharIndex($text, $index,$a,$b){
        $x = ' ';
        while($x < $a || $x > $b) {
            $x = $text[$index++];
            if(!$index || $index >= strlen($text)){
                // echo "Read File Error.<br>";
                exit();
            }
        }
        return $index-1;
    }

    function getInt($text, $index){
        $res = '';
        $x = '';
        do{
            $res .= $x;
            $x = $text[$index++];
        }while($x >= '0' && $x <= '9');
        return $res;
    }

    function between($ch, $a, $b){
        return $ch >= $a && $ch <= $b;
    }

    function isValid($text, $index){
        return !between($text[$index-2],'0','9') && $text[$index-1] == '0' && between($text[$index+1],'2','9');
    }

    // Include Composer autoloader if not already done.
    include 'vendor/autoload.php';
    
    // Parse pdf file and build necessary objects.
    $parser = new \Smalot\PdfParser\Parser();
    $pdf    = $parser->parseFile('file.pdf');
    $pages= $pdf->getPages();

    $sql = "";
    $p = 0;

    $types = ["first","next","over"];
    foreach ($pages as $page) {
        $text = $page->getText();
        $p++;
        if(($p & 1) != 0){
            continue;
        }

        $text = strtolower(str_replace(',', '',preg_replace('/\s+/', '&', nl2br($text))));
        // echo $text."<br>";

        // include('pdf2text.php');

        // $a = new PDF2Text();
        // $a->setFilename('file.pdf'); 
        // $a->decodePDF();
        // $text = strtolower(str_replace(',', '',str_replace(' ', '&', $a->output())));
        if(strlen($text) == 0){
            echo "Empty file: ".$url."<br>";
            exit();
        }
        // echo $text;

        $index = 0;
        $kwhs = [];
        for($i = 0; $i < count($types); $i++){
            if(!$index = strpos($text,$types[$i],$index)){
                break;
            }
            $index = getCharIndex($text,$index,'0','9');
            $kwh = getInt($text,$index);
            array_push($kwhs,$kwh);
        }

        $index = 0;
        $costs = [];
        while(true){
            do{
                $index = strpos($text,".",$index+1);
            }while(!isValid($text,$index));

            $success = 2;
            for($i = 0; $i < count($types); $i++){
                if(!isValid($text,$index) ){
                    if(--$success == 0){
                        $costs = [];
                        break;
                    }
                    $i--;
                    $index = strpos($text,".",$index+1);
                    continue;
                }else{
                    $success = 2;
                }
                $cost = '0.'.getInt($text,$index+1);
                array_push($costs,$cost);
                $index = strpos($text,".",$index+1);
            }
            if($success){
                for($i = 0; $i < count($types); $i++){
                    $sql .= "call add_entry('".$m."', ".$y.", ".($p/2-1).", ".$kwhs[$i].", ".$costs[$i].", ".$i.");";
                }
                break;
            }
        }
    }
    // echo $sql."<br>";
    if($local == 0){
        if (mysqli_multi_query($con, $sql)) {

        }
        if($err = mysqli_error($con)){
            echo "Error:".$err;
        }

        mysqli_close($con);
    }

?>