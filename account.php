<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'

//Will redirect to index.php if the user is not logged in
if(!loggedIn()) {

    header("Location:index.php");
    exit();
}

$company_id = $_SESSION['user_id'];

//sql query for the navbar search bar
$sql1 = "SELECT company_id, company_name FROM users WHERE company_id != 1";
$result1 = $conn -> query($sql1);

//sql query to show user's payment cards in the purchase section
$sql2 = "SELECT u.points, p.payment_id, p.card_number 
        FROM users u LEFT JOIN payment_details p 
        ON u.company_id = p.company_id
        WHERE p.company_id = $company_id";
$result2 = $conn -> query($sql2);

//Fetchs user details based on the provided company_id
$sql3 = "SELECT * FROM users WHERE company_id = $company_id";
$result3 = $conn -> query($sql3);

//shows all of the user's saved cards
$sql4 = "SELECT * FROM payment_details WHERE company_id = $company_id";
$result4 = $conn -> query($sql4);

if($result3 -> num_rows > 0) {

    $company = $result3 -> fetch_assoc();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') { 

    $msg = null;
    $msg2 = null;

    if(isset($_POST['update_account_info'])) {

        $stmt = $conn -> prepare("UPDATE users SET company_name=?, contact_number=?, contact_person=?, email=?, street_address=?, city=?, postcode=? WHERE company_id = $company_id");
        $stmt -> bind_param("sssssss", $company_name, $contact_number, $contact_person, $email, $street_address, $city, $postcode);
        
        $company_name = $_POST['company_name'];   
        $email = $_POST['email']; 
        $contact_number = $_POST['contact_number'];
        $contact_person = $_POST['contact_person'];
        $street_address = $_POST['street_address'];
        $city = $_POST['city'];
        $postcode = $_POST['postcode'];

        $stmt -> execute();
        header('Location: account.php');
        exit();

    } elseif(isset($_POST['update_card'])) {
  
        $payment_id = $_POST['payment_id'];

        $stmt = $conn -> prepare("UPDATE payment_details SET name_on_card=?, card_number=?, expiration_date=?, cvv=?, billing_address=?, billing_city=?, billing_postcode=? WHERE payment_id = $payment_id");
        $stmt -> bind_param("sssssss", $name_on_card, $card_number, $expiration_date, $cvv, $billing_address, $billing_city, $billing_postcode);

        //payment info
        $name_on_card = $_POST['name_on_card'];
        $card_number = $_POST['card_number'];
        $expiration_date = $_POST['expiration_year']."-".$_POST['expiration_month']."-01";
        $cvv = $_POST['cvv'];

        //billing address
        $billing_address = $_POST['billing_address'];
        $billing_city = $_POST['billing_city'];
        $billing_postcode = $_POST['billing_postcode'];

        $current_date = date('Y-m-d');

        if($current_date > $expiration_date) {

            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Your card is expired.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        } else {

            $stmt -> execute();
            header('Location: account.php');
            exit();
        }       
    } elseif(isset($_POST['add_new_card'])) {
  
        $stmt = $conn -> prepare("INSERT INTO payment_details (company_id, name_on_card, card_number, cvv, expiration_date, billing_address, billing_city, billing_postcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt -> bind_param("isssssss", $company_id, $name_on_card, $card_number, $cvv, $expiration_date, $billing_address, $billing_city, $billing_postcode);

        //payment info
        $name_on_card = $_POST['name_on_card'];
        $card_number = $_POST['card_number'];
        $cvv = $_POST['cvv'];
        $expiration_date = $_POST['expiration_year']."-".$_POST['expiration_month']."-01";

        //billing address
        $billing_address = $_POST['billing_address'];
        $billing_city = $_POST['billing_city'];
        $billing_postcode = $_POST['billing_postcode'];

        $current_date = date('Y-m-d');

        if($current_date > $expiration_date) {

            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Your card is expired.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        } else {

            $stmt -> execute();
            header('Location: account.php');
            exit();
        }   

    } elseif(isset($_POST['delete_card'])) {

        $payment_id = $_POST['payment_id'];
  
        $stmt = $conn -> prepare("DELETE FROM payment_details WHERE payment_id = $payment_id");

        $stmt -> execute();
        header('Location: account.php');
        exit();

    } elseif(isset($_POST['renew_sub'])) {
    
        $stmt = $conn -> prepare("UPDATE users SET subscription=? WHERE company_id = $company_id");
        $stmt -> bind_param("s", $subscription);

        $subscription = 'Active';

        $stmt -> execute();
        header('Location: account.php');
        exit();

    } elseif(isset($_POST['cancel_sub'])) {
    
        $stmt = $conn -> prepare("UPDATE users SET subscription=? WHERE company_id = $company_id");
        $stmt -> bind_param("s", $subscription);

        $subscription = 'Deactivated';

        $stmt -> execute();
        header('Location: account.php');
        exit();

    } elseif(isset($_POST['green_points'])) {
    
        $stmt1 = $conn -> prepare("UPDATE users SET points=? WHERE company_id = $company_id");
        $stmt1 -> bind_param("s", $points);
        
        $points = $_POST['hidden_total'];   
        $year = date('Y');

        if($points < 40) {

            $reward_image_path = 'images/rewards/' . $year . '-bronze-cup.png';

            $stmt2 = $conn -> prepare("INSERT INTO rewards (company_id, type, reward) VALUES (?, 'trophy', ?)");
            $stmt2 -> bind_param("is", $company_id, $reward_image_path);

            $stmt2 -> execute();

        } elseif($points < 70) {

            $reward_image_path = 'images/rewards/' . $year . '-silver-cup.png';

            $stmt2 = $conn -> prepare("INSERT INTO rewards (company_id, type, reward) VALUES (?, 'trophy', ?)");
            $stmt2 -> bind_param("is", $company_id, $reward_image_path);

            $stmt2 -> execute();

        } else {

            $reward_image_path = 'images/rewards/' . $year . '-gold-cup.png';

            $stmt2 = $conn -> prepare("INSERT INTO rewards (company_id, type, reward) VALUES (?, 'trophy', ?)");
            $stmt2 -> bind_param("is", $company_id, $reward_image_path);

            $stmt2 -> execute();

        }

        $stmt1 -> execute();
        header('Location: account.php');
        exit();

    } elseif(isset($_POST['checkout'])) {
        
        //stores a message to let the user know of the purchase was successful

        $payment_id = $_POST['payment_id'];
        $points = $_POST['points'];
        $amount = $_POST['amount'];
        $total_points = $points + $amount;
        $price = $amount * 10;

        if($total_points > 100) {

            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> You cannot exceed a 100-point total.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';

        } else {

            $year = date('Y');
            $reward_desc = $year . ' Certificate';

            $stmt1 = $conn->prepare("UPDATE users SET points=? WHERE company_id=?");
            $stmt1->bind_param("ii", $total_points, $company_id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("INSERT INTO orders (company_id, payment_id, amount, price, order_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt2->bind_param("iiid", $company_id, $payment_id, $amount, $price);
            $stmt2->execute();

            //retrieves the auto-generated order_id
            $order_id = $conn->insert_id;

            $stmt3 = $conn->prepare("INSERT INTO rewards (company_id, order_id, type, reward) VALUES (?, ?, 'certificate', ?)");
            $stmt3->bind_param("iis", $company_id, $order_id, $reward_desc);
            $stmt3->execute();

            $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Purchase successful, '.$amount.' points added to your account. Check out your <a href="company_details.php?company_id='.$company_id.'#rewards">REWARDS</a>!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }

    } elseif(isset($_POST['update_password'])) {

        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_pass = $_POST['confirm_new_pass'];

        //verifies the old password
        if(password_verify($old_password, $company['pass'])) {

            //Checks if passwords match
            if($new_password === $confirm_new_pass) {

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $conn -> prepare("UPDATE users SET pass=? WHERE company_id = $company_id");
                $stmt -> bind_param("s", $hashed_password);

                $stmt -> execute();
                header('Location: account.php');
                exit();

            } else {

                $msg2 = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> Passwords do not match.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        } else {

            $msg2 = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> Wrong password.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
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
    <title>Sustain Energy - Account</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="styles/stylesheet.css"> 
</head>
<body>
    <!-- Navbar start -->
    <nav class="navbar navbar-expand-lg bg-transparent">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sustain Energy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi-list" style="font-size: 1.25rem;" alt="Menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Sustainability
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="sustainability_rubric.php">Rubric</a></li>
                            <li><a class="dropdown-item" href="green_points.php">Green Points</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">Feedback</a>
                    </li>
                </ul>          
                <form class="d-flex ms-auto me-3" role="search">
                    <div id="search-bar-container" style="position: relative;">
                        <input class="form-control form-control-sm focus-ring" id="search-bar" type="search" placeholder="Search" aria-label="Search">
                        <ul id="item-list" style="display:none">
                            <?php if ($result1 -> num_rows > 0) : ?>
                                <?php while ($row = $result1 -> fetch_array()) : ?>
                                    <li>
                                        <a class="search-result" href="company_details.php?company_id=<?= $row['company_id'] ?>" target="_blank"><?= $row['company_name'] ?></a>
                                    </li>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </form>              
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-person-circle" style="font-size: 1.25rem;" alt="My Account"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if(isset($company_id) && ($company_id == 1)): ?>
                                <li><a class="dropdown-item" href="admin_panel.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item active" href="#">Account</a></li>
                            <li><a class="dropdown-item" data-bs-toggle="offcanvas" href="#purchaseOffcanvas" role="button" aria-controls="purchaseOffcanvas">Purchase Points</a></li>
                            <li><a class="dropdown-item" href="order_history.php">Order History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar end -->
    <!-- Main container -->
    <div class="container">
        <!-- Account Information -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                <br>
                <h2>Account Information</h2>
                <br>
                <br>
                <?php if(isset($msg)) { ?>
                    <?php echo $msg; ?>
                <?php } ?>
                <button type="button" class="btn btn-light float-end" data-bs-toggle="modal" data-bs-target="#calculator_modal" <?php if($company['subscription'] == 'Deactivated') : ?>disabled<?php endif; ?>>
                    <i class="bi bi-calculator" style="font-size: 1rem"></i>
                </button>
                <h4>Green Points</h4>
                <br>
                <div class="alert alert-success" role="alert">
                    Your points: <?php echo $company['points'];?>/100
                    <input type="hidden" id="points_check" value="<?php echo $company['points'];?>">
                </div>
                <p>Check out your <a href="company_details.php?company_id=<?php echo $company_id ?>">REWARDS</a>!</p>
                <p style="font-size:12px">Find out more: <a href="green_points.php">Green Points</a>, <a href="sustainability_rubric.php">Rubric</a>.
                <br>
                <button type="button" class="btn btn-light float-end" id="update_account_btn">
                    <i class="bi bi-pen" style="font-size: 1rem"></i>
                </button>
                <h4>Company Details</h4>
                <br>
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <label for="registration_date" class="col-sm-4 col-form-label">Register Date:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control focus-ring" id="registration_date" value="<?php echo date('d M Y', strtotime($company['registration_date'])); ?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="company_name" class="col-sm-4 col-form-label">Company Name:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control updateAccount focus-ring" id="company_name" name="company_name" value="<?php echo $company['company_name'];?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="email" class="col-sm-4 col-form-label">Email:</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control updateAccount focus-ring" id="email" name="email" value="<?php echo $company['email'];?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="contact_number" class="col-sm-4 col-form-label">Contact Number:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control updateAccount focus-ring" id="contact_number" name="contact_number" pattern="[0-9]{11}" value="<?php echo $company['contact_number'];?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="contact_person" class="col-sm-4 col-form-label">Contact Person:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control updateAccount focus-ring" id="contact_person" name="contact_person" value="<?php echo $company['contact_person'];?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="street_address" class="col-sm-4 col-form-label">Address:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control updateAccount focus-ring" id="street_address" name="street_address" value="<?php echo $company['street_address'];?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="city" class="col-sm-4 col-form-label">City/Postcode:</label>
                        <div class="col-sm-5 col-8">
                            <input type="text" class="form-control updateAccount focus-ring" id="city" name="city" value="<?php echo $company['city'];?>" required disabled>
                        </div>
                        <div class="col-sm-3 col-4">
                            <input type="text" class="form-control updateAccount focus-ring" id="postcode" name="postcode" value="<?php echo $company['postcode'];?>" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-10">
                            <div class="form-check" id="account_checkbox" style="display:none">
                                <input class="form-check-input updateAccount focus-ring" type="checkbox" id="confirm_account_update" required disabled>
                                <label class="form-check-label" for="confirm_account_update">
                                    Confirm changes.
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark" id="update_account_info" name="update_account_info" style="display:none">Submit Changes</button>
                </form>     
            </div>
        </div>
        <!-- Account Information End -->
        <br>
        <!-- Subscription status -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                <button type="button" class="btn btn-light float-end" id="update_sub">
                    <i class="bi bi-pen" style="font-size: 1rem"></i>
                </button>
                <h4>Subscription</h4>
                <br>
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <label for="sub_status" class="col-sm-4 col-form-label">Subscription Status:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="sub_status" value="<?php echo $company['subscription'];?>" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-10">
                            <div class="form-check" id="sub_checkbox" style="display:none">
                                <input class="form-check-input focus-ring" type="checkbox" id="confirmSubChange" required>
                                <label class="form-check-label" for="confirmSubChange">
                                    Confirm subscription change.
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark" id="renew_sub" name="renew_sub" style="display:none">Renew Subscription</button>
                    <button type="submit" class="btn btn-dark" id="cancel_sub" name="cancel_sub" style="display:none">Cancel Subscription</button>
                </form>
            </div>
        </div>
        <!-- Subscription end -->
        <br>
        <!-- Card Details -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                <h4>Saved Cards</h4>
                <br>
            </div>
        </div>
        <?php if ($result4->num_rows > 0) : ?>
            <?php $i = 0; ?> 
            <?php while ($row = $result4->fetch_array()) : ?> 
                <?php $i++; ?>                                 
                <div class="row justify-content-center">
                    <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                        <button type="button" class="btn btn-light float-end" id="edit_card<?php echo $i;?>" onclick="editCardDetails(<?php echo $i?>)" style="display:none">
                            <i class="bi bi-pen" style="font-size: 1rem"></i>
                        </button>
                        <button type="button" class="btn btn-light" onclick="showCardDetails(<?php echo $i;?>)">
                            <i class="bi bi-credit-card" style="font-size: 1rem"></i>
                            Card ending: <?php echo "****".substr($row['card_number'], 12); ?>
                        </button>
                        <br><br>
                        <form method="post" class="needs-validation card_dets<?php echo $i;?>" style="display:none" novalidate>
                            <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>" required>
                            <div class="row mb-3">
                                <label for="name_on_card" class="col-sm-4 col-form-label">Name on Card:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring update_card<?php echo $i;?>" id="name_on_card" name="name_on_card" value="<?php echo $row['name_on_card'];?>" required disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="card_number" class="col-sm-4 col-form-label">Card Number:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring update_card<?php echo $i;?>" id="card_number" name="card_number" pattern="[0-9]{16}" value="<?php echo $row['card_number'];?>" required disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="expiration_month" class="col-sm-4 col-form-label">Expire Month/Year:</label>
                                <div class="col-6 col-sm-4">
                                    <select class="form-select focus-ring update_card<?php echo $i;?>" aria-label="expiration_month" name="expiration_month" required disabled>
                                        <option selected><?php echo date('m', strtotime($row['expiration_date'])); ?></option>
                                        <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <select class="form-select focus-ring update_card<?php echo $i;?>" aria-label="expiration_year" name="expiration_year" required disabled>
                                        <option selected><?php echo date('y', strtotime($row['expiration_date'])); ?></option>
                                        <option value="2024">24</option>
                                        <option value="2025">25</option>
                                        <option value="2026">26</option>
                                        <option value="2027">27</option>
                                        <option value="2028">28</option>
                                        <option value="2029">29</option>
                                        <option value="2030">30</option>
                                        <option value="2031">31</option>
                                        <option value="2032">32</option>
                                        <option value="2033">33</option>
                                        <option value="2034">34</option>
                                        <option value="2035">35</option>
                                        <option value="2036">36</option>
                                        <option value="2037">37</option>
                                        <option value="2038">38</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="cvv" class="col-sm-4 col-form-label">CVV:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring update_card<?php echo $i;?>" id="cvv" name="cvv" pattern="[0-9]{3}" value="<?php echo $row['cvv'];?>" required disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="billing_address" class="col-sm-4 col-form-label">Billing Address:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring update_card<?php echo $i;?>" id="billing_address" name="billing_address" value="<?php echo $row['billing_address'];?>" required disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="billing_city" class="col-sm-4 col-form-label">City/Postcode:</label>
                                <div class="col-sm-5 col-8">
                                    <input type="text" class="form-control focus-ring update_card<?php echo $i;?>" id="billing_city" name="billing_city" value="<?php echo $row['billing_city'];?>" required disabled>
                                </div>
                                <div class="col-sm-3 col-4">
                                    <input type="text" class="form-control focus-ring update_card<?php echo $i;?>" id="billing_postcode" name="billing_postcode" value="<?php echo $row['billing_postcode'];?>" required disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-10">
                                    <div class="form-check" id="card_checkbox<?php echo $i;?>" style="display:none">
                                        <input class="form-check-input focus-ring update_card<?php echo $i;?>" type="checkbox" id="confirmCardUpdate<?php echo $i;?>" required disabled>
                                        <label class="form-check-label" for="confirmCardUpdate">
                                            Confirm changes.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark mb-4" id="update_card<?php echo $i;?>" name="update_card" style="display:none">Submit Changes</button>
                            <button type="submit" class="btn btn-outline-danger mb-4" id="delete_card<?php echo $i;?>" name="delete_card" style="display:none">
                                <i class="bi bi-trash" style="font-size: 1rem"></i>
                            </button>
                        </form>     
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
        <!-- card details End -->
        <br>
        <!-- Add new card -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                <button type="button" class="btn btn-light" id="add_new_card">
                    <i class="bi bi-plus-square" style="font-size: 1rem"></i>
                    Add New Card
                </button>
                <br><br>
                <form method="post" class="needs-validation new_card" style="display:none" novalidate>
                    <div class="row mb-3">
                        <label for="name_on_card" class="col-sm-4 col-form-label">Name on Card:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control focus-ring" id="name_on_card" name="name_on_card" required>
                            <div class="invalid-feedback">
                                Please enter the name on the card.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="card_number" class="col-sm-4 col-form-label">Card Number:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control focus-ring" id="card_number" name="card_number" pattern="[0-9]{16}" required>
                            <div class="invalid-feedback">
                                Please enter a valid card number.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="expiration_month" class="col-sm-4 col-form-label">Expire Month/Year:</label>
                        <div class="col-6 col-sm-4">
                            <select class="form-select focus-ring" aria-label="expiration_month" name="expiration_month" required>
                                <option value="01">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                                <option value="08">08</option>
                                <option value="09">09</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-4">
                            <select class="form-select focus-ring" aria-label="expiration_year" name="expiration_year" required>
                                <option value="2024">24</option>
                                <option value="2025">25</option>
                                <option value="2026">26</option>
                                <option value="2027">27</option>
                                <option value="2028">28</option>
                                <option value="2029">29</option>
                                <option value="2030">30</option>
                                <option value="2031">31</option>
                                <option value="2032">32</option>
                                <option value="2033">33</option>
                                <option value="2034">34</option>
                                <option value="2035">35</option>
                                <option value="2036">36</option>
                                <option value="2037">37</option>
                                <option value="2038">38</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="cvv" class="col-sm-4 col-form-label">CVV:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control focus-ring" id="cvv" name="cvv" pattern="[0-9]{3}" required >
                            <div class="invalid-feedback">
                                Please enter a valid CVV.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="billing_address" class="col-sm-4 col-form-label">Billing Address:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control focus-ring" id="billing_address" name="billing_address"  required >
                            <div class="invalid-feedback">
                                Please enter a valid street address.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="billing_city" class="col-sm-4 col-form-label">City/Postcode:</label>
                        <div class="col-sm-5 col-8">
                            <input type="text" class="form-control focus-ring" id="billing_city" name="billing_city" required >
                            <div class="invalid-feedback">
                                Please enter a valid city.
                            </div>
                        </div>
                        <div class="col-sm-3 col-4">
                            <input type="text" class="form-control focus-ring" id="billing_postcode" name="billing_postcode" required >
                            <div class="invalid-feedback">
                                Please enter a valid postcode.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input focus-ring" type="checkbox" id="confirmNewCard" required >
                                <label class="form-check-label" for="confirmNewCard">
                                    The information provided is correct.
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark" id="add_new_card" name="add_new_card">Submit</button>
                </form>     
            </div>
        </div>
        <br>
        <div class="container">
        <!-- Change Password -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                <button type="button" class="btn btn-light float-end" id="changePassword">
                    <i class="bi bi-pen" style="font-size: 1rem"></i>
                </button>
                <h4>Change Password</h4>
                <br>
                <?php if(isset($msg2)) { ?>
                    <?php echo $msg2; ?>
                <?php } ?>
                <form method="post" class="needs-validation mb-4" novalidate>
                    <div class="row mb-3">
                        <label for="old_password" class="col-sm-4 col-form-label">Current Password:</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control changePassword focus-ring" id="old_password" name="old_password" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="new_password" class="col-sm-4 col-form-label">New Password (8-20):</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control changePassword focus-ring" id="new_password" name="new_password" minlength="8" maxlength="20" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="confirm_new_pass" class="col-sm-4 col-form-label">Confirm New Password:</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control changePassword focus-ring" id="confirm_new_pass" name="confirm_new_pass" minlength="8" maxlength="20" required disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-10">
                            <div class="form-check" id="passwordCheckbox" style="display:none">
                                <input class="form-check-input changePassword focus-ring" type="checkbox" id="confirmPasswordChange" required disabled>
                                <label class="form-check-label" for="confirmPasswordChange">
                                    Confirm changes.
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark" id="update_password" name="update_password" style="display:none">Submit Changes</button>
                </form>     
            </div>
        </div>
    </div>
    </div>
    <!-- Main container end -->
    <!-- Footer -->
    <footer class="footer footer-black">
        <br>
        <a class="footer-nav footnav-black" href="register.php">Subscribe</a>&emsp;
        <a class="footer-nav footnav-black" href="about_us.php">About Us</a>&emsp;
        <a class="footer-nav footnav-black" href="terms_of_service.php" target="_blank">Terms of Service</a>&emsp;
        <a class="footer-nav footnav-black" href="privacy_policy.php" target="_blank">Privacy Policy</a>&emsp;
        <!-- modal trigger -->
        <a class="footer-nav footnav-black" href="#" data-bs-toggle="modal" data-bs-target="#exampleModal">Font Size</a>
        <br><br>
        <p>Copyright &copy; 2024 Sustain Energy. All rights reserved.</p>
    </footer>  
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Font Size</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check font_size my-2">
                        <input class="form-check-input my-3" type="radio" name="flexRadioDefault" id="32px">
                        <label class="form-check-label" for="32px" style="font-size:32px;">
                            Example text
                        </label>
                    </div>
                    <div class="form-check font_size my-2">
                        <input class="form-check-input my-2" type="radio" name="flexRadioDefault" id="24px">
                        <label class="form-check-label" for="24px" style="font-size:24px;">
                            Example text
                        </label>
                    </div>
                    <div class="form-check font_size my-2">
                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="16px" checked>
                        <label class="form-check-label" for="16px" style="font-size:16px;">
                            Example text
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-dark" onclick="change_font_size()">Save changes</button>
                </div>
            </div>
        </div>
    </div> 
    <!-- Green Points purchase offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="purchaseOffcanvas" aria-labelledby="purchaseOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="purchaseOffcanvasLabel">Purchase Green Points</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="row">
                <div class="col">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Please use the <a href="account.php">Green Calculator</a> first before completing any purchase.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <h6>1 Point - £10</h6>
                </div>
            </div>
            <form method="post" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-6">
                        <label for="amount" class="form-label">Quantity:</label>
                        <select class="form-select focus-ring"  id="amount" name="amount" required>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                            <option value="35">35</option>
                            <option value="40">40</option>
                            <option value="45">45</option>
                            <option value="50">50</option>
                            <option value="55">55</option>
                            <option value="60">60</option>
                            <option value="65">65</option>
                            <option value="70">70</option>
                            <option value="75">75</option>
                            <option value="80">80</option>
                            <option value="85">85</option>
                            <option value="90">90</option>
                            <option value="95">95</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="total-price" class="form-label">Total Price:</label>
                        <input type="text" class="form-control" id="total-price" value="50" required disabled>
                    </div>
                </div>
                <h6>Choose payment card:</h6>
                <!-- loops through and shows all of user's cards -->
                <?php if ($result2 -> num_rows > 0) {
                    $i = 0;
                    while ($row = $result2 -> fetch_array()) { 
                        $i++; ?>                                 
                        <input type="hidden" name="points" value="<?php echo $row['points']; ?>" required>
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input focus-ring" type="radio" name="payment_id" id="radioBtn<?php echo $i; ?>" value="<?php echo $row['payment_id']; ?>" required>
                                    <label class="form-check-label" for="radioBtn<?php echo $i; ?>">
                                        <i class="bi bi-credit-card" style="font-size: 1rem"></i>
                                        Card ending: <?php echo "****".substr($row['card_number'], 12); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php }
                } ?>
                <!-- loop end -->
                <button type="submit" class="d-grid gap-2 col-12 mx-auto btn btn-dark mb-4" id="checkout" name="checkout" <?php if($company['subscription'] == 'Deactivated') : ?>disabled<?php endif; ?>>Checkout</button>
            </form>
        </div>
    </div>
    <!-- Offcanvas end -->
    <!-- Green points calculator Modal -->
    <div class="modal fade" id="calculator_modal" tabindex="-1" aria-labelledby="calculator_modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="calculator_modal">Green Calculator</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-center mx-auto">
                        <div class="col-12">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col">Improvement</th>
                                        <th scope="col">0%</th>
                                        <th scope="col">5%</th>
                                        <th scope="col">10%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">Carbon Emission Reduction</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Waste Reduction</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Water Conservation</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Sustainable Suply Chain</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Biodiversity Preservation</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Energy-Efficient Infrastructure</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Eco-Friendly Products/Services</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Community Engagement</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Carbon Offsetting</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Employee Sustainability Education</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>   
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="post" class="row needs-validation" novalidate>
                        <input type="hidden" id="hidden_total" name="hidden_total" required>  
                        <label for="total" class="col-sm-3 col-form-label"> Total Points:</label>
                        <div class="col-8 col-sm-6">
                            <input type="text" class="form-control" id="total" name="total" required disabled>                        
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-dark" onclick="calculate()">Calculate</button>
                        </div>
                        <div class="d-grid col-12 mt-3">
                            <button type="submit" class="btn btn-dark" id="green_points" name="green_points" disabled>Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- changes the font size -->
    <script src="font_size.js"></script>
    <!-- Controls the display of the search bar -->
    <script src="searchbar_display.js"></script>
    <!-- counts and outputs total price -->
    <script src="count_price.js"></script>
    <!-- Disables form submition is fields are empty or invalid -->
    <script src="form_validation.js"></script>
    <!-- Toggle account info editing -->
    <script src="company_details_edit.js"></script>
    <!-- Toggle card info editing -->
    <script src="card_details_edit.js"></script>
    <!-- Toggle password change -->
    <script src="password_change.js"></script>
    <!-- Toggle subscription change -->
    <script src="subscription_change.js"></script>
    <!-- Toggle new card addition -->
    <script src="add_new_card.js"></script>
    <!-- Green Calculator Functionality -->
    <script src="green_calc_functionality.js"></script>
    <!-- Green Calculator Score Count -->
    <script src="green_calc_score.js"></script>
    <!-- Modal autofocus fix -->
    <script src="modal_autofocus.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>