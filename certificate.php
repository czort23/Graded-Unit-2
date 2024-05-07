<?php
require 'connect_db.php'; //connects to the database

if(isset($_GET['reward_id'])) {

    $reward_id = $_GET['reward_id'];
    $sql1 = "SELECT company_id, order_id FROM rewards WHERE reward_id = $reward_id";
    $result1 = $conn -> query($sql1);

    if($result1 -> num_rows > 0) {

        $row1 = $result1 -> fetch_assoc();
        $company_id = $row1['company_id'];
        $order_id = $row1['order_id'];

        $sql2 = "SELECT u.company_name, o.amount, o.price, o.order_date
                FROM users u LEFT JOIN orders o 
                ON u.company_id = o.company_id
                WHERE o.company_id = $company_id AND o.order_id = $order_id";
        $result2 = $conn -> query($sql2);

        if($result2 -> num_rows > 0) {

            $certificate = $result2 -> fetch_assoc();
        }
    }
}

$conn -> close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="authour" content="Adrian Zalubski">
    <title>Certificate</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="styles/stylesheet.css"> 
</head>
<body>
    <!-- Main -->
    <div class="container">
        <br><br><br><br>
        <div class="row justify-content-center">
            <div class="card m-4" style="width: 38rem; background-color:#C1E1C1; border-style: solid; border-width: 10px;">
                <div class="card-body">
                    <h1 class="centered mt-4 mb-5">Certificate of Donation</h1>
                    <div class="d-flex justify-content-center my-2">
                        <p>This certificate is presented to</p>
                    </div>
                    <div class="d-flex justify-content-center my-2">
                        <h5 style="font-size: 30px;"><?php echo $certificate['company_name']; ?></h5>
                    </div>
                    <div class="d-flex justify-content-center my-2">
                        <p>for the dontaion of <b>£<?php echo $certificate['price']; ?></b></p>
                    </div>
                    <div class="d-flex justify-content-center my-2">
                        <p>in exchange for <?php echo $certificate['amount']; ?> Green Points.</p>
                    </div>
                    <div class="d-flex">
                        <p>Date: <?php echo $certificate['order_date']; ?></p>
                    </div>
                </div>
            </div>    
        </div>
    </div>
    <!-- Main end -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>