<?php

    function goBack($c,$y){
        //GO BACK To INDEX.PHP
        ob_start(); // ensures anything dumped out will be caught

        // do stuff here
        $url = 'http://codingwithkevin.com/green_loan/index.php?island='.$c.'&year='.$y; // this can be set based on whatever
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
    $y = "2019";
    $c = isset($_GET['island']) ? $_GET['island'] :"0";
    $sql = 'SELECT 1 FROM max_income WHERE island = '.$c.' AND year_num = 2019;';
    // echo $sql;
    if($local == 0){
        $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
            
        // Check connection
        if (mysqli_connect_errno())
        {
            // echo "Failed to connect to MySQL: " . mysqli_connect_error()."<br>";
            goBack($c,$y);
            exit();
        }
    }

    if ($local == 0 && $result = mysqli_query($con, $sql)){
        if (mysqli_num_rows($result) != 0) { 
            // echo "Max Income information up to date<br>";
            goBack($c,$y);
            exit();
        }
        // echo "Updating max income information<br>";
    }

    // $url_base = 'https://www.hawaiianelectric.com/documents/billing_and_payment/rates/effective_rate_summary/efs_';
    // $url = $url_base.$y.'_'.$m.'.pdf';

    // if($local == 0 && !file_put_contents('file.pdf', fopen($url, 'r'))){
    //     // echo "Could not find: ".$url."<br>";
    //     goBack($c,$y);
    //     exit();
    // }

    function getCharIndex($text, $index,$a,$b){
        $x = ' ';
        while($x < $a || $x > $b) {
            $x = $text[$index++];
            if(!$index || $index >= strlen($text)){
                // echo "Read File Error.<br>";
                goBack($c,$y);
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
    $pdf    = $parser->parseFile('income_file.pdf');
    $pages= $pdf->getPages();

    $sql = "";
    $p = 0;
    //islands: hawaii, honolulu, maui
    $types = ["first","next","over"];
    foreach ($pages as $page) {
        $text = $page->getText();

        $text = strtolower(str_replace(',', '',preg_replace('/\s+/', '&', nl2br($text))));
        // echo $text."<br>";

        // include('pdf2text.php');

        // $a = new PDF2Text();
        // $a->setFilename('file.pdf'); 
        // $a->decodePDF();
        // $text = strtolower(str_replace(',', '',str_replace(' ', '&', $a->output())));
        if(strlen($text) == 0){
            // echo "Empty file: <br>";
            exit();
        }
        $w = 8; //# of people
        $h = 14;//# of incomes
        $skip = $w * ($h-1);//# of entries to skip to reach bottom row. - scanning horizontally

        $index = 0;
        for($i = 0; $i <= $skip; $i++){
            $index = getCharIndex($text,$index,'$','$')+1;
        }
        for($i = 1; $i <= $w; $i++){
            $index = getCharIndex($text,$index,'$','$')+1;
            $income = getInt($text,$index);
            $sql .= "call add_max_income(".$p.", 2019, ".$i.", ".$income.");";
            // echo getInt($text,$index)."\n<br>";
        }
        // echo $text;
        $p++;
    }
    // echo "Submit SQL";
    // echo $sql."\n<br>";
    if($local == 0){
        if (mysqli_multi_query($con, $sql)) {

        }
        if($err = mysqli_error($con)){
            // echo "Error:".$err;
        }

        mysqli_close($con);
    }
    goBack($c,$y);
?>