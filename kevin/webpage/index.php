<html>
    <head>
        <title>Data Collection</title>
        <link rel="stylesheet" href="index.css">
        <script src="index.js"></script>

    </head>
    <body>
        <h1>HGIA Data Backend</h1>
        <?php 
        //START DATABASE
            $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
        ?>
        <h2>Utility Effective Rates</h2>
        <form action="effective_rates.php" method="get">
            <div class="select">
                <select name="month">
                    <option>--Month--</option>
                    <option value="01">Jan</option>
                    <option value="02">Feb</option>
                    <option value="03">Mar</option>
                    <option value="04">Apr</option>
                    <option value="05">May</option>
                    <option value="06">Jun</option>
                    <option value="07">Jul</option>
                    <option value="08">Aug</option>
                    <option value="09">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </select>
                <div class="select_arrow"> </div>
            </div>
            <div class="select">
                <select name="year">
                    <option>--Year--</option>
                    <option value="19">2019</option>
                    <option value="18">2018</option>
                    <option value="17">2017</option>
                    <option value="16">2016</option>
                    <option value="15">2015</option>
                </select>
                <div class="select_arrow"> </div>
            </div>
            <input type="submit">
        </form>

        <!-- TABLE -->
        <table class="effective_table">
            <tr class="header">
                <td>Type</td>
                <td>Utility</td>
                <td>Energy</td>
                <td>Cost</td>
            </tr>
            <?php
            //FETCH
                $y = "20".(isset($_GET['year']) ? $_GET['year'] :date('y'));
                $m = sprintf("%02d", isset($_GET['month']) ? $_GET['month'] :date('m')-1);
                $sql = "SELECT type,island,kwh,cost_kwh FROM `effective_rate` WHERE month_num =$m AND year_num=$y";
                // echo $sql;
                $result = mysqli_query($con, $sql);
                while ($row = $result->fetch_assoc()) {
                    $type = $row[type] == 0 ? "First" : ($row[type] == 1 ? "Next" : "All");
                    $utility = $row[island] == 0 ? "HECO-Oahu" : ($row[island] == 1 ? "MECO-Maui" : ($row[island] == 2 ? "MECO-Lanai" : ($row[island] == 3 ? "MECO-Molokai" : "HELCO-Hawaii")));
                    echo "<tr>";
                    echo "<td>".$type."</td>";
                    echo "<td>".$utility."</td>";
                    echo "<td>".$row[kwh]."</td>";
                    echo "<td>".$row[cost_kwh]."</td>";
                    echo "</tr>";
                }
            ?>
        </table>

        <!-- MAX INCOME -->
        <h2>Max Income By County</h2>
        <form action="max_income.php" method="get">
            <div class="select">
                <select name="island">
                    <option>--County--</option>
                    <option value="0">Hawaii</option>
                    <option value="1">Honolulu</option>
                    <option value="2">Maui</option>
                </select>
                <div class="select_arrow"> </div>
            </div>
            <div class="select">
                <select name="year">
                    <option>--Year--</option>
                    <option value="2019">2019</option>
                </select>
                <div class="select_arrow"> </div>
            </div>
            <input type="submit">
        </form>

        
        <!-- TABLE -->
        <table class="income_table">
            <tr class="header">
                <td>Person</td>
                <td>Max Income</td>
            </tr>
            <?php
            //FETCH
                $island = isset($_GET['island']) ? $_GET['island'] :0;
                $sql = "SELECT num_person,max_income FROM `max_income` WHERE island =$island AND year_num=2019";
                // echo $sql;
                $result = mysqli_query($con, $sql);
                $index = 0;
                while ($row = $result->fetch_assoc()) {
                    if($index++ == 8){
                        break;
                    }
                    echo "<tr>";
                    echo "<td>".$row[num_person]."</td>";
                    echo "<td>$".number_format($row[max_income], 2, '.', ',')."</td>";
                    echo "</tr>";
                    
                }
            ?>
        </table>
    </body>
</html>