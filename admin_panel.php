<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'

//Will redirect to index.php if the user is not logged in
if(!loggedIn()) {

    header("Location:index.php");
    exit();
}

$company_id = $_SESSION['user_id'];

$sql = "SELECT company_id FROM users WHERE company_id != 1";
$result = $conn -> query($sql);

//sql query for the navbar search bar
$sql1 = "SELECT company_id, company_name FROM users WHERE company_id != 1";
$result1 = $conn -> query($sql1);

if($_SERVER['REQUEST_METHOD'] == 'POST') { 

    if(isset($_POST['check_details'])) {
        
        $company_id_check = $_POST['company_id'];

        $sql2 = "SELECT * FROM users WHERE company_id = $company_id_check";
        $result2 = $conn -> query($sql2); 

    } elseif(isset($_POST['update_account_info'])) {

        $company_id_update = $_POST['company_id']; 

        $stmt = $conn -> prepare("UPDATE users SET company_name=?, contact_number=?, contact_person=?, email=?, street_address=?, city=?, postcode=?, subscription=?, points=? WHERE company_id = $company_id_update");
        $stmt -> bind_param("ssssssssi", $company_name, $contact_number, $contact_person, $email, $street_address, $city, $postcode, $subscription, $points);
        
        $company_name = $_POST['company_name'];   
        $email = $_POST['email']; 
        $contact_number = $_POST['contact_number'];
        $contact_person = $_POST['contact_person'];
        $street_address = $_POST['street_address'];
        $city = $_POST['city'];
        $postcode = $_POST['postcode'];
        $subscription = $_POST['subscription'];
        $points = $_POST['points'];

        $stmt -> execute();
        header('Location: account.php');
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
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-person-circle" style="font-size: 1.25rem;" alt="My Account"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if(isset($company_id) && ($company_id == 1)): ?>
                                <li><a class="dropdown-item active" href="admin_panel.php">Admin Panel</a></li>
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
    <!-- Main container -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                <?php if ($result -> num_rows > 0) : ?>
                    <form method="post" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-auto">
                                <label for="company_id" class="form-label">Company ID</label>
                                <select class="form-select" id="company_id" name="company_id" aria-label="Default select example">
                                <?php while ($row = $result -> fetch_array()) : ?>                                
                                    <option value="<?php echo $row['company_id']; ?>"><?php echo $row['company_id']; ?></option>
                                <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark" id="check_details" name="check_details">Check Details</button>
                    </form>
                <?php endif; ?>
                <br>
                <?php if (isset($result2)) : ?>
                    <?php if ($result2 -> num_rows > 0) : ?>
                        <?php $row2 = $result2 -> fetch_array() ?>
                        <form method="post" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <label for="company_id" class="col-sm-4 col-form-label">Company ID:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring" id="company_id" value="<?php echo $row2['company_id']; ?>" required disabled>
                                    <input type="hidden" name="company_id" value="<?php echo $row2['company_id']; ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="registration_date" class="col-sm-4 col-form-label">Register Date:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring" id="registration_date" value="<?php echo date('d M Y', strtotime($row2['registration_date'])); ?>" required disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="points" class="col-sm-4 col-form-label">Green Points:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control focus-ring" id="points" name="points" value="<?php echo $row2['points']; ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="subscription" class="col-sm-4 col-form-label">Subscription:</label>
                                <div class="col-sm-8">
                                    <select class="form-select" id="subscription" name="subscription" aria-label="Default select example" required>
                                        <option selected value="<?php echo $row2['subscription']; ?>"><?php echo $row2['subscription']; ?></option>
                                        <option value='Active'>Active</option>
                                        <option value='Inactive'>Inactive</option>
                                        <option value='Deactivated'>Deactivated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="company_name" class="col-sm-4 col-form-label">Company Name:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control updateAccount focus-ring" id="company_name" name="company_name" value="<?php echo $row2['company_name'];?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="email" class="col-sm-4 col-form-label">Email:</label>
                                <div class="col-sm-8">
                                    <input type="email" class="form-control updateAccount focus-ring" id="email" name="email" value="<?php echo $row2['email'];?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="contact_number" class="col-sm-4 col-form-label">Contact Number:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control updateAccount focus-ring" id="contact_number" name="contact_number" value="<?php echo $row2['contact_number'];?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="contact_person" class="col-sm-4 col-form-label">Contact Person:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control updateAccount focus-ring" id="contact_person" name="contact_person" value="<?php echo $row2['contact_person'];?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="street_address" class="col-sm-4 col-form-label">Address:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control updateAccount focus-ring" id="street_address" name="street_address" value="<?php echo $row2['street_address'];?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="city" class="col-sm-4 col-form-label">City/Postcode:</label>
                                <div class="col-sm-5 col-8">
                                    <input type="text" class="form-control updateAccount focus-ring" id="city" name="city" value="<?php echo $row2['city'];?>" required>
                                </div>
                                <div class="col-sm-3 col-4">
                                    <input type="text" class="form-control updateAccount focus-ring" id="postcode" name="postcode" value="<?php echo $row2['postcode'];?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-10">
                                    <div class="form-check" id="account_checkbox">
                                        <input class="form-check-input updateAccount focus-ring" type="checkbox" id="confirm_account_update" required>
                                        <label class="form-check-label" for="confirm_account_update">
                                            Confirm changes.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark" id="update_account_info" name="update_account_info">Submit Changes</button>
                        </form>     
                        <br>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <hr>
    <!-- Footer -->
    <footer class="footer footer-white">
        <br>
        <a class="footer-nav footnav-white" href="#">Subscribe</a>&emsp;
        <a class="footer-nav footnav-white" href="about_us.php">About Us</a>&emsp;
        <a class="footer-nav footnav-white" href="terms_of_service.php" target="_blank">Terms of Service</a>&emsp;
        <a class="footer-nav footnav-white" href="privacy_policy.php" target="_blank">Privacy Policy</a>&emsp;
        <!-- modal trigger -->
        <a class="footer-nav footnav-white" href="#" data-bs-toggle="modal" data-bs-target="#exampleModal">Font Size</a>
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
    <!-- changes the font size -->
    <script src="font_size.js"></script>
    <!-- Modal autofocus fix -->
    <script src="modal_autofocus.js"></script>
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