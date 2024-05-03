<?php

//If the user is logged in, returns true, otherwise returns false
session_start();

function loggedIn() {

    if(isset($_SESSION['user_id'])) {
        
        return true;       
    } else {

        return false;
    }
}

?>