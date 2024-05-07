<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'

//sql query for the navbar search bar
$sql1 = "SELECT company_id, company_name FROM users WHERE company_id != 1";
$result1 = $conn -> query($sql1);

if(loggedIn()) {
    $company_id = $_SESSION['user_id'];

    //sql query to show user's payment cards in the purchase section
    $sql2 = "SELECT u.points, p.payment_id, p.card_number 
            FROM users u LEFT JOIN payment_details p 
            ON u.company_id = p.company_id
            WHERE p.company_id = $company_id";
    $result2 = $conn -> query($sql2);
}

$sql3 = "SELECT f.*, u.company_name
        FROM feedbacks f LEFT JOIN users u
        ON f.company_id = u.company_id";
$result3 = $conn -> query($sql3);

if($_SERVER['REQUEST_METHOD'] == 'POST') { 

    if(isset($_POST['checkout'])) {
        
        //stores a message to let the user know if the purchase was successful or not
        $msg = null;

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
    } elseif(isset($_POST['feedback-submit'])) {

        $feedback = $_POST['feedback'];

        $stmt = $conn -> prepare("INSERT INTO feedbacks (company_id, feedback, date_posted) VALUES (?, ?, NOW())");
        $stmt -> bind_param("is", $company_id, $feedback);
            
        $stmt -> execute();
        header('Location: feedback.php');
        exit();

    } elseif(isset($_POST['delete'])) {

        $feedback_id = $_POST['feedback_id'];

        $stmt = $conn -> prepare("DELETE FROM feedbacks WHERE feedback_id = $feedback_id");
            
        $stmt -> execute();
        header('Location: feedback.php');
        exit();
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
    <title>Sustain Energy - Feedback</title>
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
                        <a class="nav-link active" href="#">Feedback</a>
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
                <!-- Checks if session is active and displays navbar options accordingly -->
                <?php if(!loggedIn()): ?>
                    <li class="nav-item">
                        <div class="btn-group">
                            <a href="register.php" class="btn btn-sm btn-dark">Sign Up</a>
                            <a href="login.php" class="btn btn-sm btn-outline-dark">Log In</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-person-circle" style="font-size: 1.25rem;" alt="My Account"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if(isset($company_id) && ($company_id == 1)): ?>
                                <li><a class="dropdown-item" href="admin_panel.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="account.php">Account</a></li>
                            <li><a class="dropdown-item" data-bs-toggle="offcanvas" href="#purchaseOffcanvas" role="button" aria-controls="purchaseOffcanvas">Purchase Points</a></li>
                            <li><a class="dropdown-item" href="order_history.php">Order History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <!-- End if -->
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar end -->
    <!-- Main -->
    <div class="container">
        <br><br>
        <?php if(isset($msg)) { ?>
            <?php echo $msg; ?>
        <?php } ?>
        <?php if(isset($company_id)) : ?>
        <form method="post" class="row g-3 needs-validation mb-5" novalidate>
            <label for="feedback" class="form-label mb-0"><h4>Leave feedback:</h4></label>
            <textarea class="form-control focus-ring mt-0" id="feedback" name="feedback" placeholder="Type your feedback here..." minlength="15" required></textarea>
            <div class="invalid-feedback">
                Please type in your feedback min. 15 characters.
            </div>
            <button type="submit" class="btn btn-dark d-grid gap-2 col-sm-2 ms-auto" id="feedback-submit" name="feedback-submit">Post</button>
        </form>
        <?php endif; ?>
        <h2>Feedbacks</h2>
        <div class="row justify-content-center">
            <div class="col-12">
                <?php if ($result3->num_rows > 0) {
                    $rows = [];
                    while ($row = $result3->fetch_array()) {
                        $rows[] = $row;
                    }

                    $rows = array_reverse($rows);

                    foreach ($rows as $row) {
                        $date = date('Y-m-d', strtotime($row['date_posted']));
                        $time = date('H:i:s', strtotime($row['date_posted'])); ?>    
                            <div class="card mb-3">
                                <div class="row g-0">
                                    <div class="col-4 col-lg-2">
                                        <div class="card-body">
                                            <?php if(loggedIn()) : ?>
                                                <?php if(($company_id == $row['company_id']) || ($company_id == 1)) : ?>
                                                <form method="post" class="needs-validation" novalidate>
                                                    <input type="hidden" name="feedback_id" value="<?php echo $row['feedback_id']; ?>" required>
                                                    <button type="submit" class="btn btn-outline-danger float-end" id="delete" name="delete">
                                                        <i class="bi bi-trash" style="font-size: 1rem"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <h5 class="card-title"><?php echo $row['company_name']; ?></h5>
                                            <h6 class="card-subtitle mb-2 text-body-secondary">Date Posted: <br> <?php echo $date.'<br>'.$time; ?></h6>
                                        </div>
                                    </div>
                                    <div class="vr p-0"></div>
                                    <div class="col-7 col-lg-9">
                                        <div class="card-body">
                                            <p class="card-text"><?php echo $row['feedback']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>                   
                    <?php }
                } ?>
            </div>  
        </div>   
    </div>
    <!-- Main end -->
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
                <button type="submit" class="d-grid gap-2 col-12 mx-auto btn btn-dark mb-4" id="checkout" name="checkout">Checkout</button>
            </form>
        </div>
    </div>
    <!-- Offcanvas end -->
    <!-- changes the font size -->
    <script src="font_size.js"></script>
    <!-- Controls the display of the search bar -->
    <script src="searchbar_display.js"></script>
    <!-- counts and outputs total price -->
    <script src="count_price.js"></script>
    <!-- Disables form submition is fields are empty or invalid -->
    <script src="form_validation.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>