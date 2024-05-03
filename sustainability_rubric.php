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
                            <li><a class="dropdown-item active" href="#">Rubric</a></li>
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
        <img src="images/sustainability_bg.jpg" class="img-fluid w-100" alt="...">
        <header class="centered-title">
            <h1>Sustainability Rubric</h1>
        </header>        
    </section>
    <section class="mission-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-10 col-md-11 my-5">
                    <h5>Here, we delve into the intricate facets of sustainability and how it forms 
                        the backbone of our mission. Below, we outline our <b>Sustainability Rubric</b>, a comprehensive framework designed to measure and 
                        evaluate a company's commitment to environmental stewardship.</h5>
                </div> 
            </div>
        </div>
    </section>
    <!-- Sustainability Rubric -->
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-md-11 my-5">
                <h5>Understanding Sustainability Rubric:</h5>
                <p>Our <code>Sustainability Rubric</code> provides a structured framework to evaluate and measure a company's commitment to 
                sustainability across various aspects of its operations. It comprises ten key measures, each assessing different facets of 
                environmental responsibility, from carbon emissions reduction to employee sustainability education. By utilizing this rubric, 
                companies can gain insights into their environmental performance and identify areas for improvement.</p>
            </div> 
            <div class="col-10 col-md-11 mb-5">
                <div class="accordion" id="accordionPanelsStayOpenExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                <b class="upper">1. Carbon Emissions Reduction</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <p>This measure evaluates the company's efforts to reduce its carbon footprint by implementing <code>energy-efficient practices</code>, 
                                    transitioning to <code>renewable energy sources</code>, and initiating <code>emission reduction initiatives</code>. It reflects the company's commitment 
                                    to combatting climate change and minimizing its environmental impact.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                                <b class="upper">2. Waste Reduction</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Assessing the company's dedication to <code>minimizing waste generation</code> and <code>enhancing recycling rates</code>. This includes strategies such 
                                    as waste <code>reduction at the source</code>, <code>reuse of materials</code>, <code>recycling programs</code>, and <code>responsible disposal practices</code>. A focus on waste 
                                    reduction demonstrates a commitment to resource conservation and environmental sustainability.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                                <b class="upper">3. Water Conservation</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Evaluates the company's initiatives to <code>reduce water consumption</code> and promote <code>responsible water management</code> practices. This may 
                                    involve implementing water-efficient technologies, optimizing water usage in production processes, and implementing water 
                                    recycling and reclamation systems. Water conservation efforts are crucial for <code>mitigating water scarcity</code> and <code>preserving 
                                    freshwater ecosystems</code>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="false" aria-controls="panelsStayOpen-collapseFour">
                                <b class="upper">4. Sustainable Supply Chain</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Examining the sustainability of the company's supply chain, considering the environmental impact of raw material sourcing, 
                                    production processes, transportation, and distribution. This involves <code>assessing suppliers' environmental practices</code>, 
                                    <code>promoting sustainable sourcing</code>, <code>reducing transportation emissions</code>, and fostering partnerships with <code>environmentally 
                                    responsible vendors</code>. A sustainable supply chain contributes to overall environmental stewardship and resilience.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFive" aria-expanded="false" aria-controls="panelsStayOpen-collapseFive">
                                <b class="upper">5. Biodiversity Preservation</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseFive" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Assessing the company's efforts to <code>protect and promote biodiversity</code> within its operations and surrounding areas. 
                                    This may include <code>habitat conservation</code>, <code>restoration initiatives</code>, <code>biodiversity monitoring</code>, and integration of <code>biodiversity 
                                    considerations</code> into land use planning and development projects. Biodiversity preservation is essential for ecosystem 
                                    health, resilience, and the provision of ecosystem services.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseSix" aria-expanded="false" aria-controls="panelsStayOpen-collapseSix">
                                <b class="upper">6. Energy-Efficient Infrastructure</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseSix" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Examining the <code>energy efficiency</code> of the company's buildings, facilities, and manufacturing processes. This involves implementing 
                                    <code>energy-efficient technologies</code>, optimizing building design for energy conservation, conducting <code>energy audits</code>, and promoting <code>energy-saving 
                                    behaviors</code> among employees. Energy-efficient infrastructure reduces greenhouse gas emissions, lowers energy costs, and <code>enhances overall 
                                    sustainability</code> performance.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseSeven" aria-expanded="false" aria-controls="panelsStayOpen-collapseSeven">
                                <b class="upper">7. Eco-friendly Products/Services</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseSeven" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Measures the proportion of the company's product or service offerings that have <code>environmentally friendly attributes</code>. 
                                    This includes products/services with reduced environmental impact throughout their lifecycle, such as those made from 
                                    <code>sustainable materials</code>, <code>energy-efficient appliances</code>, <code>organic food products</code>, or <code>eco-friendly packaging options</code>. 
                                    Offering eco-friendly products/services demonstrates a commitment to meeting consumer demand for <code>sustainable options</code> 
                                    and reducing environmental harm.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseEight" aria-expanded="false" aria-controls="panelsStayOpen-collapseEight">
                                <b class="upper">8. Community Engagement</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseEight" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Measures the company's involvement in <code>local communities</code>, focusing on <code>environmental education</code>, support for <code>green initiatives</code>, 
                                    and <code>community development</code>. This involves initiatives such as <code>volunteering</code>, <code>sponsoring environmental events</code>, providing 
                                    <code>environmental education programs</code>, and collaborating with local organizations on sustainability projects. Community engagement 
                                    fosters partnerships, <code>builds trust</code>, and promotes positive social and environmental outcomes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseNine" aria-expanded="false" aria-controls="panelsStayOpen-collapseNine">
                                <b class="upper">9. Carbon Offsetting</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseNine" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Evaluates the company's participation in <code>carbon offset programs</code> or initiatives to compensate for its unavoidable emissions. 
                                    Carbon offsetting involves investing in projects that <code>reduce or capture greenhouse gas emissions</code>, such as <code>reforestation</code>, 
                                    <code>renewable energy projects</code>, or <code>methane capture from landfills</code>. Carbon offsetting enables companies to take responsibility 
                                    for their emissions and contribute to global climate action efforts.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTen" aria-expanded="false" aria-controls="panelsStayOpen-collapseTen">
                                <b class="upper">10. Employee Sustainability Education</b>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapseTen" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p>Evaluates the company's efforts to educate and engage employees in sustainability practices within and outside the workplace. 
                                    This includes providing <code>training on environmental issues</code>, <code>promoting sustainable behaviors</code> in daily operations, encouraging 
                                    employee involvement in <code>sustainability initiatives</code>, and fostering a culture of <code>environmental responsibility</code>. Employee 
                                    sustainability education empowers individuals to make informed choices, drives innovation, and strengthens organizational 
                                    commitment to sustainability goals.</p>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </section> 
    <section class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-md-11 my-5">
                <h5>Using the Rubric:</h5>
                <p>Each measure within the rubric can be assigned a <b>Green Score</b>, providing a comprehensive 
                    assessment of a company's sustainability performance. This enables businesses to identify strengths, areas for improvement, and chart 
                    a course towards a greener future.</p> 
            </div>            
        </div>
        <div class="row justify-content-center">
            <div class="col-11 my-5">
                <h5>Join Us in Making a Difference:</h5>
                <p>We invite companies of all sizes and industries to join us in our mission to create a more sustainable future. Together, we can harness 
                    the power of technology, innovation, and collaboration to drive positive change for the planet and future generations.</p>
                <a href="register.php">
                    <button type="button" class="btn btn-lg btn-outline-dark">Join Today - Only £99.99/Year</button>
                </a>
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