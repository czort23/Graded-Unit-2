<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'

//sql query for the navbar search bar
$sql1 = "SELECT company_id, company_name FROM users";
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

if($_SERVER['REQUEST_METHOD'] == 'POST') { 
    
    if(isset($_POST['checkout'])) {
        
        //stores a message to let the user know of the purchase was successful
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

            $stmt1 = $conn -> prepare("UPDATE users SET points=? WHERE company_id = $company_id");
            $stmt1 -> bind_param("s", $total_points);

            $stmt2 = $conn -> prepare("INSERT INTO orders (company_id, payment_id, amount, price, order_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt2 -> bind_param("iiid", $company_id, $payment_id, $amount, $price);
            
            $stmt1 -> execute();
            $stmt2 -> execute();

            $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Purchase successful, '.$amount.' points added to your account.
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
    <title>Sustain Energy - Sustainability</title>
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
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Sustainability
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="sustainability_rubric.php">Rubric</a></li>
                            <li><a class="dropdown-item active" href="#">Green Points</a></li>
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
    <!-- Main container -->
    <div class="container">
        <?php if(isset($msg)) { ?>
            <?php echo $msg; ?>
        <?php } ?>
    </div>
    <section class="main-poster">
        <img src="images/green_points.jpg" class="img-fluid w-100" alt="...">
        <header class="centered-title">
            <h1>Green Points</h1>
        </header>        
    </section>
    <section class="mission-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-10 col-md-11 my-5">
                    <h5><b>Green Points</b> serve as more than just a metric of environmental performance; they are a symbol of commitment and progress towards 
                        sustainability. By earning and utilizing <b>Green Points</b>, companies not only demonstrate their dedication to environmental 
                        stewardship but also contribute to real-world impact through green activities and initiatives.</h5>
                </div> 
            </div>
        </div>
    </section>
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-md-11 my-5">
                <h5>How Green Points Work:</h5>
                <p><b>Green Points</b> serve as a quantifiable representation of a company's sustainable practices within our platform. 
                Here's how it works:</p> 
                <p><b class="upper">Scoring System:</b> Each selected measure is assigned a score based on the company's performance:</p>
                <ul>
                    <li><b style="color:red">RED</b>: 0 points - Not following the practice yet (0% improvement).</li>
                    <li><b style="color:orange">AMBER</b>: 5 points - Intermediate adherence (5% improvement).</li>
                    <li><b style="color:green">GREEN</b>: 10 points - Excellent adherence (10% improvement).</li>
                </ul>
                <p><b class="upper">Calculation of Total Points:</b> You can use our Green Point Calculator on your Account page to quickly calculate your score. The cumulative score from all ten measures determines the company's total Green Points, with 
                    a maximum score of 100 points. <b>Important!</b> You can calculate your score only once a year.</p>
                <p><b class="upper">Purchasing Green Vouchers:</b> If a company falls short of its target, it can purchase Green Vouchers through our platform. The 
                    cost is calculated at £10 per point below the target.</p>
                <p><b class="upper">Utilization of Green Points Revenue:</b> Our company will utilize the revenue earned from purchased Green Points to engage in 
                    green activities, such as tree planting programs or donations to environmental organizations.</p>
                <p><b class="upper">Certificate of Achievement:</b> Upon reaching a certain threshold of Green Points, companies receive a Certificate of Achievement, 
                    showcasing their commitment to sustainability. The certificate also reflects their achieved grading - GOLD, SILVER, or BRONZE - 
                    based on their total Green Points.</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-10 col-md-11 my-5">
                <h5>Purchase Green Points Now:</h5>
                <p>Ready to take your sustainability efforts to the next level? Purchase Green Points today and make a tangible impact on the environment. 
                    Simply follow these steps to get started:
                <p><b class="upper">1. Log in to Your Account:</b> If you haven't already, log in to your account using your registration email and password.</p>
                <p><b class="upper">2. Navigate to the Green Points Purchase Section:</b> Once logged in, click on the account icon and then Purchase Points on the right hand side of the navigation bar.</p>
                <p><b class="upper">3. Select the Quantity of Green Points:</b> Choose the quantity of Green Points you wish to purchase. Whether you need a small boost or a 
                    substantial investment, our flexible options cater to your specific needs.</p>
                <p><b class="upper">4. Review and Confirm Your Purchase:</b> Review your selection and confirm your purchase. You'll have the opportunity to review the 
                    cost and ensure it aligns with your budget and sustainability goals.</p>
                <p><b class="upper">5. Complete the Transaction:</b> Follow the prompts to complete your transaction securely. Our platform ensures a seamless and 
                    hassle-free purchasing experience, with multiple payment options available for your convenience.</p>
                <p><b class="upper">6. Track Your Green Points:</b> Once your purchase is complete, you can track your Green Points balance in real-time. Monitor your  
                    progress towards your sustainability targets and celebrate the positive impact of your investment.</p>
                <p>Ready to make a difference? Purchase Green Points now and join us in building a greener, more sustainable future for generations to 
                    come. Together, we can create positive change and leave a lasting impact on the planet.</p>
            </div>
        </div>
    </section>
    <!-- Main container end -->
    <!-- Footer -->
    <footer class="footer footer-black">
        <br>
        <a class="footer-nav footnav-black" href="register.php">Subscribe</a>&emsp;
        <a class="footer-nav footnav-black" href="about_us.php">About Us</a>&emsp;
        <a class="footer-nav footnav-black" href="terms_of_service.php" target="_blank">Terms of Service</a>&emsp;
        <a class="footer-nav footnav-black" href="privacy_policy.php" target="_blank">Privacy Policy</a>
        <br><br>
        <p>Copyright &copy; 2024 Sustain Energy. All rights reserved.</p>
    </footer>
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