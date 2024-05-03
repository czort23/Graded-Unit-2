<?php

require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'
//Will redirect to index.php if the user is already logged in
if(loggedIn()) {
    header("Location:index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_POST['login'])) {

        $msg = login($conn);
    } 
}

//login function, checks if email and password match, returns error if input is incorrect
function login($conn) {
    
    $msg = null;

    $email = $_POST['email'];
    $password = $_POST['password'];

    //select details from database where email (database) equals email (user input)
    $stmt = $conn->prepare("SELECT company_id, email, pass FROM users WHERE email = ?");
    $stmt -> bind_param("s", $email);
    $stmt -> execute();
    $result = $stmt -> get_result();

    //if the number of results return is > 0, then user exist
    if($result -> num_rows > 0) {

        $user = $result -> fetch_array();

        if(password_verify($password, $user['pass'])) {

            $_SESSION['user_id'] = $user['company_id'];
            header('Location: index.php');
            exit();

        } else {
            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> Incorrect password.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }
    } else {
        $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> User not found.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
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
    <title>Sustain Energy - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="styles/stylesheet.css"> 
    <style>
       /* body {
            background-image: url("images/pexels-pixabay-45222.jpg");
            background-repeat: no-repeat;
            background-position: center;
            height: 100vh;
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
            <br><br><br><br><br><br>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 col-10 mt-5">
                    <h1 class="centered">Welcome Back</h1>
                    <br>
                    <?php if(isset($msg)) { ?>
                        <?php echo $msg; ?>
                    <?php } ?>
                    <form method="post" class="row g-3 needs-validation" novalidate>
                        <input type="email" class="form-control focus-ring" id="email" name="email" placeholder="Email" required>
                        <div class="invalid-feedback">
                            Please enter a valid email.
                        </div>
                        <input type="password" class="form-control focus-ring" id="password" name="password" placeholder="Password" required>
                        <div class="invalid-feedback">
                            Please enter a password.
                        </div>
                        <button type="submit" class="btn btn-dark" id="login" name="login">Log in</button>
                    </form>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 col-10 mt-2">
                    <a href="forgot_password.php">Forgot password?</a>
                    <hr>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 col-10 mb-5">
                    <a href="register.php">Don't have an account yet?</a>
                </div>
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
        <a class="footer-nav footnav-white" href="privacy_policy.php" target="_blank">Privacy Policy</a>
        <br><br>
        <p>Copyright &copy; 2024 Sustain Energy. All rights reserved.</p>
    </footer>   
    <!-- Disables form submition is fields are empty or invalid -->
    <script src="form_validation.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>