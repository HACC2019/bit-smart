<?php
    $local = 0;
    if(!isset($_SERVER['REMOTE_ADDR'])){
        echo "Running Local\n";
        $local = 1;
    }
    if($local == 0){
        $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
            
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }
    }

    $y = "20".(isset($_GET['year']) ? $_GET['year'] :date('y'));
    $m = sprintf("%02d", isset($_GET['month']) ? $_GET['month'] :date('m')-1);
    $sql = 'SELECT 1 FROM effective_rate WHERE month_num = \''.$m.'\' AND year_num = '.$y.';';

    if ($local == 0 && $result = mysqli_query($con, $sql)){
        if (mysqli_num_rows($result) != 0) { 
            echo "HECO effective rates already up to date";
            exit();
        }
        echo "Updating HECO effective rates";
    }

    $url_base = 'https://www.hawaiianelectric.com/documents/billing_and_payment/rates/effective_rate_summary/efs_';
    $url = $url_base.$y.'_'.$m.'.pdf';

    if($local == 0 && !file_put_contents('file.pdf', fopen($url, 'r'))){
        echo " -> Could not find: ".$url;
        exit();
    }

    function getCharIndex($text, $index,$a,$b){
        $x = ' ';
        while($x < $a || $x > $b) {
            $x = $text[$index++];
            if(!$index || $index >= strlen($text)){
                echo "Read File Error.\n";
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
        echo "Page: ".$p."\n";

        $text = strtolower(str_replace(',', '',preg_replace('/\s+/', '&', nl2br($text))));
        // echo $text;

        // include('pdf2text.php');

        // $a = new PDF2Text();
        // $a->setFilename('file.pdf'); 
        // $a->decodePDF();
        // $text = strtolower(str_replace(',', '',str_replace(' ', '&', $a->output())));
        if(strlen($text) == 0){
            echo "Empty file: ".$url."\n";
            exit();
        }
        // echo $text;

        $index = 0;

        $a = strpos($text,$types[0],$index);
        $point = strpos($text,".",$a);
        $b = strpos($text,$types[1],$index);

        $good_order = $point < $b;
        if(!$good_order){
            echo "BAD\n";
            $kwhs = [];
            for($i = 0; $i < count($types); $i++){
                if(!$index = strpos($text,$types[$i],$index)){
                    echo "Could not find: ".$types[$j]."\n";
                    break;
                }
                $index = getCharIndex($text,$index,'0','9');
                array_push($kwhs,getInt($text,$index));
            }
            $index = 0;
            do{
                $index2 = strpos($text,"all",$index);
                if($index2){
                    $index = $index2 + 1;
                }
            }while($index2);

            for($i = 0; $i < 15; $i++){
                $index = strpos($text,".",$index)+1;
            }
            for($i = 0; $i < count($types); $i++){
                $index = strpos($text,".",$index)+1;
                $cost = '0.'.getInt($text,$index);
                $sql .= "call add_entry(".$m.", ".$y.", ".($p/2-1).", ".$kwhs[$i].", ".$cost.", ".$i.");\n";
            }
        }else{
            for($i = 0; $i < count($types); $i++){
                if(!$index = strpos($text,$types[$i],$index)){
                    echo "Could not find: ".$types[$i]."\n";
                    break;
                }
                $index = getCharIndex($text,$index,'0','9');
                $kwh = getInt($text,$index);
                $index = getCharIndex($text,getCharIndex($text,$index,'.','.') + 1,'.','.');
                
                $next_index = getCharIndex($text,$index+1,'.','.');
                if($next_index - $index < 10){
                    $index = $next_index;
                    $next_index = getCharIndex($text,$index+1,'.','.');
                }
                $cost = '0.'.getInt($text,$index+1);
                // echo "\nNEXT:".substr($text,$index,10)."\n";
                $sql .= "call add_entry(".$m.", ".$y.", ".($p/2-1).", ".$kwh.", ".$cost.", ".$i.");\n";
            }
        }
    }
    echo $sql;
    if($local == 0){
        if (mysqli_multi_query($con, $sql)) {

        }
        if($err = mysqli_error($con)){
            echo "Error:".$err;
        }

        mysqli_close($con);
    }

?>