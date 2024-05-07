<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'
//Will redirect to index.php if the user is already logged in
if(loggedIn()) {
    header("Location:index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_POST['sign_up'])) {

        $msg = register($conn);
    } 
}

function register($conn) {

    $msg = null;
    
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT email FROM users");
    $stmt -> execute();
    $result = $stmt -> get_result();

    if($result -> num_rows > 0) {
        while($row = $result -> fetch_array()) {
            if($email === $row['email']) {

                $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> Email already exists.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                return $msg;

            }
        }
    }

    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $confirm_password = mysqli_real_escape_string($conn, trim($_POST['confirm_password']));
    $otp = mysqli_real_escape_string($conn, trim($_POST['otp']));

    //Checks if passwords match
    if($password !== $confirm_password) {

        $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> Passwords don\'t match.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';

    } else {

        $stmt1 = $conn -> prepare("INSERT INTO users (company_name, contact_person, contact_number, registration_date, email, pass, otp, street_address, city, postcode) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
        $stmt1 -> bind_param("sssssssss", $company_name, $contact_person, $contact_number, $email, $hashed_password, $hashed_otp, $street_address, $city, $postcode);
    
        $stmt2 = $conn -> prepare("INSERT INTO payment_details (company_id, name_on_card, card_number, cvv, expiration_date, billing_address, billing_city, billing_postcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2 -> bind_param("isssssss", $company_id, $name_on_card, $card_number, $cvv, $expiration_date, $billing_address, $billing_city, $billing_postcode);

        //company details
        $company_name = $_POST['company_name'];
        $contact_person = $_POST['contact_person'];
        $contact_number = $_POST['contact_number'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
        $street_address = $_POST['street_address'];
        $city = $_POST['city'];
        $postcode = $_POST['postcode'];

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

            $stmt1 -> execute();

            $company_id = $stmt1->insert_id;

            $stmt2 -> execute();

            if ($stmt1->affected_rows > 0 && $stmt2->affected_rows > 0) {

                header('Location: processing_payment.php');
                exit();

            } else {
                $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> Error.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
            }
        }   
    }
    return $msg;
}

$conn -> close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="authour" content="Adrian Zalubski">
    <title>Sustain Energy - Sign Up</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="styles/stylesheet.css"> 
    <style>
        /*body {
            background-image: url("images/pexels-pixabay-45222.jpg");
            background-repeat: no-repeat;
            background-size: cover;
        }*/
    </style>
</head>
<body>
    <!-- Navbar start -->
    <nav class="navbar navbar-expand-lg bg-transparent">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sustain Energy</a>
        </div>
    </nav>
    <!-- Navbar end -->
    <!-- Main -->
    <section id="login">
        <div class="container">
            <br><br>
            <div class="row">
                <h1 class="centered">Create Account</h1>
                <br>
                <?php if(isset($msg)) { ?>
                    <?php echo $msg; ?>
                <?php } ?>
                <form method="post" class="row justify-content-center needs-validation" novalidate>
                    <div class="col-lg-4 col-md-7 col-10 my-5">
                        <!-- Company details form -->
                        <h5>Company details</h5>
                        <div class="row mb-3">
                            <input type="text" class="form-control focus-ring" id="company_name" name="company_name" placeholder="Company name" required>
                            <div class="invalid-feedback">
                                Please enter the name of the company.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="email" class="form-control focus-ring" id="email" name="email" placeholder="Email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="text" class="form-control focus-ring" id="contact_person" name="contact_person" placeholder="Contact person" required>
                            <div class="invalid-feedback">
                                Please enter the contact person.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="text" class="form-control focus-ring" id="contact_number" name="contact_number" placeholder="Contact number" pattern="[0-9]{11}" required>
                            <div class="invalid-feedback">
                                Please enter the contact number.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="text" class="form-control focus-ring" id="street_address" name="street_address" placeholder="Street address" required>
                            <div class="invalid-feedback">
                                Please enter a valid address.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-8 ps-0">
                                <input type="text" class="form-control focus-ring" id="city" name="city" placeholder="City" required>
                                <div class="invalid-feedback">
                                    Please enter a valid city.
                                </div>
                            </div>
                            <div class="col-4 pe-0">
                                <input type="text" class="form-control focus-ring" id="postcode" name="postcode" placeholder="Postcode" required>
                                <div class="invalid-feedback">
                                    Please enter a valid postcode.
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="password" class="form-control focus-ring" id="password" name="password" placeholder="Password (8-20 characters)" minlength="8" maxlength="20" required>
                            <div class="invalid-feedback">
                                Please enter a password (8-20 characters).
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="password" class="form-control focus-ring" id="confirm_password" name="confirm_password" placeholder="Confirm password (8-20 characters)" minlength="8" maxlength="20" required>
                            <div class="invalid-feedback">
                                Please confirm the password (8-20 characters).
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="password" class="form-control focus-ring" id="otp" name="otp" placeholder="Recover Password (8-number code)" pattern="[0-9]{8}" required>
                            <div class="invalid-feedback">
                                Please confirm the recovery password (8-number code).
                            </div>
                        </div>
                    </div>
                    <div class="vr m-5 p-0 vr-signup"></div>
                    <div class="col-lg-4 col-md-7 col-10 my-5">
                        <!-- Payment information form -->
                        <h5>Payment info</h5>
                        <div class="row mb-3">
                            <input type="text" class="form-control focus-ring" id="name_on_card" name="name_on_card" placeholder="Name on the card" required>
                            <div class="invalid-feedback">
                                Please enter the name on the card.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="text" class="form-control focus-ring" id="card_number" name="card_number" placeholder="Card number" pattern="[0-9]{16}" required>
                            <div class="invalid-feedback">
                                Please enter a valid card number.
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-4 ps-0">
                                <input type="text" class="form-control focus-ring" id="cvv" name="cvv" placeholder="CVV" pattern="[0-9]{3}" required>
                                <div class="invalid-feedback">
                                    Please enter a valid CVV.
                                </div>
                            </div>
                            <div class="col-8 pe-0">
                                <div class="input-group ">
                                    <select class="form-select focus-ring" id="expiration_month" name="expiration_month" required>
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
                                    <select class="form-select focus-ring"  id="expiration_year" name="expiration_year" required>
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
                        </div>
                        <!-- Billing address form -->
                        <h5>Billing address</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input focus-ring" type="checkbox" value="" id="toggle_billing_form">
                            <label class="form-check-label" for="toggle_billing_form">
                                Same as company address
                            </label>
                        </div>
                        <div id="billing_address_form">
                            <div class="row mb-3">
                                <input type="text" class="form-control focus-ring" id="billing_address" name="billing_address" placeholder="Street address" required>
                                <div class="invalid-feedback">
                                    Please enter a valid address.
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-8 ps-0">
                                    <input type="text" class="form-control focus-ring" id="billing_city" name="billing_city" placeholder="City" required>
                                    <div class="invalid-feedback">
                                        Please enter a valid city.
                                    </div>
                                </div>
                                <div class="col-4 pe-0">
                                    <input type="text" class="form-control focus-ring" id="billing_postcode" name="billing_postcode" placeholder="Postcode" required>
                                    <div class="invalid-feedback">
                                        Please enter a valid postcode.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid col-lg-4 col-md-7 col-10 px-0 mb-2 mx-auto">
                        <button type="submit" class="btn btn-dark" name="sign_up">Sign up</button>
                    </div>
                </form>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-7 col-10 mt-2 pe-0">
                    <p>By clicking Sing Up, you agree to our <a href="terms_of_service.php" target="_blank">Terms</a>, and the £99.99 subscription charge.</p>
                    <hr>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-7 col-10 mt-2 mb-5 pe-0">
                    <a href="login.php">Already have an account?</a>
                </div>
            </div>
        </div>
    </section>
    <!-- Main end -->
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
    <!-- Disables form submition is fields are empty or invalid -->
    <script src="form_validation.js"></script>
    <script>
        

        document.addEventListener('DOMContentLoaded', function() {
            let toggleBillingFormCheckbox = document.getElementById('toggle_billing_form');
            let billingAddressForm = document.getElementById('billing_address_form');

            toggleBillingFormCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    billingAddressForm.style.display = 'none';
                } else {
                    billingAddressForm.style.display = 'inline';
                }
            });

            // Initialize form display based on checkbox state
            if (toggleBillingFormCheckbox.checked) {
                billingAddressForm.style.display = 'none';
            } else {
                billingAddressForm.style.display = 'inline';
            }
        });

    </script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let checkbox = document.getElementById("toggle_billing_form");
    let companyAddress = document.getElementById("street_address");
    let companyCity = document.getElementById("city");
    let companyPostcode = document.getElementById("postcode");
    let billingAddress = document.getElementById("billing_address");
    let billingCity = document.getElementById("billing_city");
    let billingPostcode = document.getElementById("billing_postcode");

    checkbox.addEventListener("change", function() {
        if (checkbox.checked) {
            // Copy values from company address fields to billing address fields
            billingAddress.value = companyAddress.value;
            billingCity.value = companyCity.value;
            billingPostcode.value = companyPostcode.value;
        } else {
            // Clear billing address fields
            billingAddress.value = "";
            billingCity.value = "";
            billingPostcode.value = "";
        }
    });
});
</script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>