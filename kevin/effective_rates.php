<?php

    function goBack($m,$y){
        //GO BACK To INDEX.PHP
        ob_start(); // ensures anything dumped out will be caught

        // do stuff here
        $url = 'http://codingwithkevin.com/green_loan/index.php?month='.$m.'&year='.$y; // this can be set based on whatever
        // echo $url;
        // clear out the output buffer
        while (ob_get_status()) 
        {
            ob_end_clean();
        }

        // no redirect
        header( "Location: $url" );
    }
    $local = 0;
    if(!isset($_SERVER['REMOTE_ADDR'])){
        // echo "Running Local<br>";
        $local = 1;
    }
    $y = "20".(isset($_GET['year']) ? $_GET['year'] :date('y'));
    $raw_y = $y[2].$y[3];
    $m = sprintf("%02d", isset($_GET['month']) ? $_GET['month'] :date('m')-1);
    $sql = 'SELECT 1 FROM effective_rate WHERE month_num = \''.$m.'\' AND year_num = '.$y.';';
    if($local == 0){
        $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
            
        // Check connection
        if (mysqli_connect_errno())
        {
            // echo "Failed to connect to MySQL: " . mysqli_connect_error()."<br>";
            goBack($m,$raw_y);
            exit();
        }
    }

    if ($local == 0 && $result = mysqli_query($con, $sql)){
        if (mysqli_num_rows($result) != 0) { 
            // echo "HECO effective rates already up to date<br>";
            goBack($m,$raw_y);
            exit();
        }
        // echo "Updating HECO effective rates<br>";
    }

    $url_base = 'https://www.hawaiianelectric.com/documents/billing_and_payment/rates/effective_rate_summary/efs_';
    $url = $url_base.$y.'_'.$m.'.pdf';

    if($local == 0 && !file_put_contents('file.pdf', fopen($url, 'r'))){
        // echo "Could not find: ".$url."<br>";
        goBack($m,$raw_y);
        exit();
    }

    function getCharIndex($text, $index,$a,$b){
        $x = ' ';
        while($x < $a || $x > $b) {
            $x = $text[$index++];
            if(!$index || $index >= strlen($text)){
                // echo "Read File Error.<br>";
                goBack($m,$raw_y);
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
            // echo "Empty file: ".$url."<br>";
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
    // echo "Submit SQL";
    echo $sql."<br>";
    if($local == 0){
        if (mysqli_multi_query($con, $sql)) {

        }
        if($err = mysqli_error($con)){
            // echo "Error:".$err;
        }

        mysqli_close($con);
    }
    goBack($m,$raw_y);
?>