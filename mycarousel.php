<?php

require 'connect_db.php'; //connects to the database
require 'session_check.php'; //passes loggedIn() function, returns 'true' if a user is logged in, otherwise returns 'false'
include 'login.php'; //handles user login
include 'register.php'; //handles user registration 

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_POST['login'])) {

        login($conn);

    } elseif(isset($_POST['register'])) {

        register($conn);

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
    <title>FilmHarbour</title>
    <!-- CSS -->
    <link rel="stylesheet" href="styles/stylesheet.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
         .carousel-sectionn {
      overflow: hidden;
      width: 100%; /* Set the width of your carousel container */
      margin: 0 auto;
      position: relative;
    }
    #carousel {
      overflow: hidden;
      width: 60%; /* Set the width of your carousel container */
      margin: 0 auto;
      position: relative;
    }

    #image-container {
      display: flex;
      transition: transform 0.5s ease-in-out;
    }

    .carousel-image {
      width: 150px;
      height: auto;
      box-sizing: border-box;
      margin: 15px 50px;
    }

    #prevBtn, #nextBtn {
      cursor: pointer;
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 24px;
      background: none;
      border: none;
    }

    #prevBtn { left: 100px; }
    #nextBtn { right: 100px; }

  </style>
</head>
<body>
    <!-- Navbar start -->
    <nav class="navbar navbar-expand-lg bg-transparent">
        <div class="container">
            <a class="navbar-brand" href="#">FilmHarbour</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi-list" style="font-size: 1.25rem;" alt="Menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sustainability.php">Sustainability</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="green_points.php">Green Points</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">Feedback</a>
                    </li>
                </ul>            
                <ul class="navbar-nav ms-auto">
                <!-- Checks if session is active and displays navbar options accordingly -->
                <?php if(!loggedIn()): ?>
                    <li class="nav-item">
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Sign In/Up
                        </button>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi-person-circle" style="font-size: 1.25rem;" alt="My Account"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="account.php">Account</a></li>
                            <li><a class="dropdown-item" href="order_history.php">Order History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="basket.php">
                            <i class="bi-basket2" style="font-size: 1.25rem;" alt="Basket"></i>
                        </a>
                    </li>
                <?php endif; ?>
                <!-- End if -->
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar end -->
    
<section class="carousel-sectionn">
        <div id="carousel">
            <div id="image-container">
                <img class="carousel-image" src="images/ecoatlas_innovations.png" alt="Image 1">
                <img class="carousel-image" src="images/ecoimpact_dynamics.png" alt="Image 2">
                <img class="carousel-image" src="images/ecosphere_strategies.png" alt="Image 3">
                <img class="carousel-image" src="images/green_alliance.png" alt="Image 4">
                <img class="carousel-image" src="images/greenhorizon_governance.png" alt="Image 1">
                <img class="carousel-image" src="images/resilient_earth.png" alt="Image 2">
                <img class="carousel-image" src="images/greenhorizon_solutions.png" alt="Image 3">
                <img class="carousel-image" src="images/sustainablefuture.png" alt="Image 4">
            </div>
            
            </div>

            <button id="prevBtn" onclick="prevSlide()">❮</button>
            <button id="nextBtn" onclick="nextSlide()">❯</button>
                </section>
    <script>
  let currentIndex = 0;
  const imageContainer = document.getElementById('image-container');
  const totalImages = document.querySelectorAll('.carousel-image').length;
  let carouselImage = document.querySelector('.carousel-image');
    let widthValue = window.getComputedStyle(carouselImage).getPropertyValue('width');
    let rightMarginValue = window.getComputedStyle(carouselImage).getPropertyValue('margin-right');
    let leftMarginValue = window.getComputedStyle(carouselImage).getPropertyValue('margin-left');

    let widthAsInteger = parseInt(widthValue, 10);
    let rightMarginAsInteger = parseInt(rightMarginValue, 10);
    let leftMarginAsInteger = parseInt(leftMarginValue, 10);

    function nextSlide() {
    currentIndex = (currentIndex + 1) % totalImages;
    updateCarousel();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
    updateCarousel();
  }

  function updateCarousel() {
    const newTransformValue = -currentIndex * (widthAsInteger + rightMarginAsInteger + leftMarginAsInteger) + 'px';
    imageContainer.style.transform = 'translateX(' + newTransformValue + ')';
  }
  setInterval(nextSlide, 3000);
</script>
    <!-- Main end -->
    <!-- Footer -->
    <footer class="footer">
        <br>
        <p>&copy; 2023 FilmHarbour. All rights reserved.</p>
    </footer>
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="loginModalLabel">Login</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" class="row g-3 needs-validation" novalidate>
                        <div class="col-12">                           
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email.
                            </div>
                        </div>
                        <div class="col-12">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <div class="invalid-feedback">
                                Please enter a password.
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-success" name="login">Login</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <p>Don't have an account?</p>
                    <button class="btn" data-bs-target="#registerModal" data-bs-toggle="modal">Register</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="registerModalLabel">Register</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">           
                    <form method="post" class="row g-3 needs-validation" novalidate>
                        <div class="col-12">
                            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Company Name" required>
                            <div class="invalid-feedback">
                                Please enter the name of thr company.
                            </div>
                        </div>
                        <div class="col-12">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email.
                            </div>
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Contact Person" required>
                            <div class="invalid-feedback">
                                Please enter the name of the contact person.
                            </div>
                        </div>
                        <div class="col-7">
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="Contact Number" required>
                            <div class="invalid-feedback">
                                Please enter the company's contact number.
                            </div>
                        </div>
                        <div class="col-5">
                            <select class="form-select" id="employees" name="employees" placeholder="dsfsdfsdfds" aria-label="Floating label select example">
                                <option selected>No. of people</option>
                                <option value="1k">Less than 10,000</option>
                                <option value="10k">10,000 - 24,999</option>
                                <option value="25k">25,000 - 49,999</option>
                                <option value="50k">50,000 - 99,999</option>
                                <option value="100k">100,000 - 199,999</option>
                                <option value="200k">200,000 or more</option>
                            </select>
                            <div class="form-text">Number of people in the organistaion.</div>
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" id="address" name="address" placeholder="Address" required>
                            <div class="invalid-feedback">
                                Please enter the company's address.
                            </div>
                        </div>
                        <div class="col-7">
                            <input type="text" class="form-control" id="city" name="city" placeholder="City" required>
                            <div class="invalid-feedback">
                                Please enter a valid city.
                            </div>
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Postcode" required>
                            <div class="invalid-feedback">
                                Please enter the company's post code.
                            </div>
                        </div>
                        <div class="col-12">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <div class="invalid-feedback">
                                Please enter a password.
                            </div>
                        </div>
                        <div class="col-12">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                            <div class="invalid-feedback">
                                Please confirm the password.
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-success" name="register">Register</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <p>Already have an account?</p>
                    <button class="btn" data-bs-target="#loginModal" data-bs-toggle="modal">Login</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal autofocus fix -->
    <script src="modal_autofocus.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>