<?php
require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'
//Will redirect to index.php if the user is already logged in
if(loggedIn()) {
    header("Location:index.php");
    exit;
}

if(isset($_GET['company_id'])) {

    $company_id = $_GET['company_id'];
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_POST['sign_up'])) {

        $msg = null;

        $new_password = mysqli_real_escape_string($conn, trim($_POST['new_password']));
        $confirm_new_password = mysqli_real_escape_string($conn, trim($_POST['confirm_new_password']));
        $otp = mysqli_real_escape_string($conn, trim($_POST['otp']));

        //Checks if passwords match
        if($new_password !== $confirm_new_password) {

            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Passwords don\'t match.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';

        } else {

            $stmt1 = $conn->prepare("UPDATE users SET pass=?, otp=? WHERE company_id=?");
            $stmt1->bind_param("ssi", $hashed_password, $hashed_otp, $company_id);

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);

            $stmt1 -> execute();

            if($stmt1->affected_rows > 0) {

                header('Location: login.php');
                exit();

            } else {
                $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> Error.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
            }
        }
        return $msg;
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
    <title>Sustain Energy - Reset Password</title>
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
        </div>
    </nav>
    <!-- Navbar end -->
    <!-- Main -->
    <section id="login">
        <div class="container">
            <br><br><br><br><br><br>
            <div class="row justify-content-center my-5">
                <div class="col-lg-4 col-md-6 col-10 my-5">
                    <?php if(isset($msg)) { ?>
                        <?php echo $msg; ?>
                    <?php } ?>
                    <h4>Enter your email</h4><br>
                    <form method="post" class="row g-3 needs-validation" novalidate>
                        <div class="row mb-3">
                            <input type="password" class="form-control focus-ring" id="new_password" name="new_password" placeholder="New password (8-20 characters)" minlength="8" maxlength="20" required>
                            <div class="invalid-feedback">
                                Please enter a new password (8-20 characters).
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="password" class="form-control focus-ring" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password (8-20 characters)" minlength="8" maxlength="20" required>
                            <div class="invalid-feedback">
                                Please confirm the new password (8-20 characters).
                            </div>
                        </div>
                        <div class="row mb-3">
                            <input type="password" class="form-control focus-ring" id="otp" name="otp" placeholder="New recover password (8-number code)" pattern="[0-9]{8}" required>
                            <div class="invalid-feedback">
                                Please confirm the recovery password (8-number code).
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark" id="sign_up" name="sign_up">Reset password</button>
                    </form>    
                </div>
            </div>
    </section>
    <!-- Main end -->
    <hr>
    <!-- Footer -->
    <footer class="footer footer-white">
        <br>
        <a class="footer-nav footnav-white" href="register.php">Subscribe</a>&emsp;
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
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>