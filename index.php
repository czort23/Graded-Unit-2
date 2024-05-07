<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'

//resets the points back to 0 on 1st jan every year
$current_date = date('Y-m-d');
$reset_date = date('Y-01-01');

if ($current_date == $reset_date) {
    
    // SQL query to reset the points column to 0
    $sql = "UPDATE users SET points = 0";

    $conn -> query($sql);
}

$sql0 = "SELECT company_id, registration_date, subscription FROM users";
$result0 = $conn -> query($sql0);

//sets inactive status to accounts without any organised events after 2 months from registering
if ($result0 -> num_rows > 0) {
    while ($row0 = $result0 -> fetch_array()) {

        $company_ID = $row0['company_id'];
        $registration_date = new DateTime($row0['registration_date']);
        $sub_status = $row0['subscription'];

        $sql = "SELECT event_id FROM events WHERE company_id = $company_ID";
        $result = $conn -> query($sql);

        if ($result -> num_rows > 0) {

            if($sub_status == 'Inactive') {

                // SQL query to set the subscription status to active if a company created an event and was previously inactive
                $sql = "UPDATE users SET subscription = 'Active' WHERE company_id = $company_ID";

                $conn -> query($sql);
            }
        } else {

            //adds 2 months to the date
            $registration_date->modify('+2 months');

            // Get the new date
            $inactive_deadline = $registration_date->format('Y-m-d');

            if(($current_date >= $inactive_deadline) && ($sub_status == 'Active')) {

                // SQL query to set the subscription status to inactive
                $sql = "UPDATE users SET subscription = 'Inactive' WHERE company_id = $company_ID";

                $conn -> query($sql);
            }
        }
    }
}

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
    <title>Sustain Energy</title>
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
            <a class="navbar-brand" href="#">Sustain Energy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi-list" style="font-size: 1.25rem;" alt="Menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
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
        <?php if(isset($msg)) { ?>
            <?php echo $msg; ?>
        <?php } ?>
    </div>
    <section class="main-poster">
        <img src="images/index_bg.jpg" class="img-fluid w-100" alt="picutre of a forest">
        <header class="centered-title">
            <h1>Transforming Companies, One Green Step at a Time!</h1>
            <a class="btn btn-lg btn-outline-light" href="about_us.php" role="button">Learn More</a>
        </header>        
    </section>
    <section class="mission-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-10 col-md-5 my-5">
                    <h4>The menace of climate change imperils every corner of our planet, demanding collective global efforts for effective solutions. 
                        Addressing this challenge necessitates international collaboration and cooperation on a scale as vast as the issue itself.</h4>
                </div> 
                <div class="col-md-1"></div>
                <div class="col-10 col-md-5 my-5">
                    <p>At <b>Sustain Energy</b>, our mission is to empower companies to lead the way in environmental sustainability. We strive to create a 
                        digital ecosystem that inspires, guides, and rewards organizations for adopting eco-friendly practices. Through a supportive 
                        community, we aim to revolutionize the corporate landscape, fostering a collective commitment to a greener and more sustainable 
                        future. Our mission is clear: Transforming companies, one green step at a time, for a world that thrives on conscious choices 
                        and responsible actions.</p>
                </div>
            </div>
        </div>
    </section>
    <br><br>
    <section class="features">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-10 col-lg-5 my-5">
                    <img src="images/index-sustain.jpg" class="img-fluid w-100 rounded" alt="image of a plant in a hand">
                </div>
                <div class="col-md-1"></div>
                <div class="col-10 col-lg-5 my-5">
                    <h5>Key Features:</h5>               
                    <p><b class="upper">Green Points System:</b> Earn points for every eco-friendly initiative, from recycling efforts to promoting sustainable events. 
                        Watch your points grow and contribute to a greener future.</p>
                    <p><b class="upper">Shopping Basket of Change:</b> Address any point gaps by donating in exchange for green points through our user-friendly 
                        shopping system. It's your safety net for achieving sustainability goals. All donations will go towards the Plant the Tree&#8482; 
                        program.</p>
                    <p><b class="upper">Certificate of Achievement:</b> Celebrate your commitment to sustainability with a prestigious Certificate of Achievement. 
                        Upon successfully reaching your yearly sustainability goals on <b>Sustain Energy</b>, proudly showcase this recognition as a testament 
                        to your organization's dedication to creating a greener future.</p> 
                    <a class="btn btn-lg btn-outline-dark" href="sustainability_rubric.php" role="button">Learn More</a>
                </div>
                <div class="col-10 col-lg-11 my-5">
                    <h5>Start Your Green Journey Today:</h5>
                    <p>Ready to take the first step towards a greener future? Join <b>Sustain Energy</b> now, and let's transform your organization into a 
                        sustainability champion. Together, we can create a world where every action counts, and every company contributes to a 
                        brighter, cleaner, and more sustainable tomorrow. <b>Sustain Energy</b> – Where Green Dreams Become Reality!</p>
                    <a class="btn btn-lg btn-outline-dark" href="register.php" role="button">Join Today - Only £99.99/Year</a>
                </div>
            </div>      
        </div>   
    </section> 
    <section class="carousel-section">
        <h1>We Are Endorsed By:</h1>
        <br>
        <div class="row mx-auto my-auto justify-content-center">
            <div id="endorseCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner" role="listbox">
                    <div class="carousel-item active">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/ecoatlas_innovations.png" class="d-block carousel-image" alt="ecoatlas innovations company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/edinburgh_college.png" class="d-block carousel-image" alt="edinburgh college company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/ecoimpact_dynamics.png" class="d-block carousel-image" alt="ecoimpact dynamics company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/ecosphere_strategies.png" class="d-block carousel-image" alt="ecosphere strategies company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/green_harmony.png" class="d-block carousel-image" alt="green harmony company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/evergreen.png" class="d-block carousel-image" alt="evergreen company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/green_alliance.png" class="d-block carousel-image" alt="green alliance company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/resilient_earth.png" class="d-block carousel-image" alt="resilient earth company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/ecowise.png" class="d-block carousel-image" alt="ecowise company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/greenhorizon_solutions.png" class="d-block carousel-image" alt="greenhorizon solutions company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/sustainablefuture.png" class="d-block carousel-image" alt="sustainablefuture company logo">
                            </a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="col-md-3">
                            <a href="https://www.edinburghcollege.ac.uk/" target="_blank">
                                <img src="images/terravanguard.png" class="d-block carousel-image" alt="terravanguard company logo">
                            </a>
                        </div>
                    </div>
                </div>
                <a class="carousel-control-prev bg-transparent w-aut" href="#endorseCarousel" role="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </a>
                <a class="carousel-control-next bg-transparent w-aut" href="#endorseCarousel" role="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </a>
            </div>
        </div>
    </section>
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
    <!-- Adds a wrap functionality to the carousel -->
    <script src="carousel_wrap.js"></script>
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