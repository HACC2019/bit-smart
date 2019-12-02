<?php
    $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
        
    // Check connection
    if ($con->connect_errno)
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error()."<br>";
        return null;
    }
    echo "CONNECTED<br>";

    $sql = "SELECT * FROM `effective_rate` WHERE 1";

    if ($result = mysqli_query($con, $sql)){
        echo "GOT RESULT<br>";
        $arr = array();
        while ($row = $result->fetch_assoc()) {
            $r = array();
            for($i = 0; $i < count($headers); $i++){
                array_push($r,$row[$headers[$i]]);
            }
            array_push($arr,$r);
        }
        $result->free();
        return $arr;
    }else{
        echo "QUERY FAILED<br>";
    }
    return null;

?>