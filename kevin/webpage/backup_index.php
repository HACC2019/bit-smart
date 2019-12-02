<html>
    <head>
        <title>Data Collection</title>
        <link rel="stylesheet" href="index.css">
        <script src="index.js"></script>

    </head>
    <body>
        <h1>Header</h1>
        <?php 
        //START DATABASE
            $con=mysqli_connect("localhost","codingx7_kcheval","k-mGXrdgSi8-jm-","codingx7_green_loan");
        ?>
        <!-- MONTH -->
        <div class="dropdown">
            <button onclick="toggleDropdown('myDropdown-month')" class="dropbtn">Select Month</button>
            <div id="myDropdown-month" class="dropdown-content">
                <a> Jan</a> <a> Feb</a> <a> Mar</a> <a> Apr</a> <a> May</a> <a> Jun</a>
                <a> Jul</a> <a> Aug</a> <a> Sep</a> <a> Oct</a> <a> Nov</a> <a> Dec</a>
            </div>
        </div>
        <!-- YEAR -->
        <div class="dropdown">
            <button onclick="toggleDropdown('myDropdown-year')" class="dropbtn">Select Year</button>
            <div id="myDropdown-year" class="dropdown-content">
                <a> 2019</a> <a> 2018</a> <a> 2017</a> <a> 2016</a> <a> 2015</a>
            </div>
        </div>
        <!-- UTILITY -->
        <div class="dropdown">
            <button onclick="toggleDropdown('myDropdown-utility')" class="dropbtn" id ="Select Utility">Select Utility</button>
            <div id="myDropdown-utility" class="dropdown-content">
                <a> HECO-Oahu</a> <a> MECO-Maui</a> <a> MECO-Lanai</a> <a> MECO-Molokai</a> <a> HELCO-Hawaii</a>
            </div>
        </div>
        <!-- SUBMIT -->
        <button onclick="toggleDropdown('myDropdown-year')" class="dropbtn" id ="Submit">Submit</button>
        <form action="welcome_get.php" method="get">
            Name: <input type="text" name="name"><br>
            E-mail: <input type="text" name="email"><br>
            <input type="submit">
        </form>

        <!-- TABLE -->
        <table class="effective_table" style="border:2px solid red;">
            <tr class="header">
                <td>Type</td>
                <td>Energy</td>
                <td>Cost</td>
            </tr>
            <?php
            //FETCH
                $result = mysqli_query($con, "SELECT * FROM `effective_rate` WHERE 1");
                while ($row = $result->fetch_assoc()) {
                    $type = $row[type] == 0 ? "First" : ($row[type] == 1 ? "Next" : "All");
                    echo "<tr>";
                    echo "<td>".$type."</td>";
                    echo "<td>".$row[kwh]."</td>";
                    echo "<td>".$row[cost_kwh]."</td>";
                    echo "</tr>";
                }
            ?>
        </table>
    </body>
</html>