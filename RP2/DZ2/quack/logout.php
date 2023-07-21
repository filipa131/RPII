<?php 

// Uništi session i preusmjeri korisnika na početnu stranicu.
//session_start();
if (session_status() == PHP_SESSION_NONE) {
    // Ako sesija još nije pokrenuta, pokreni je
    session_start();
}

session_unset();
session_destroy();

header( 'Location: quack.php' );
exit();

?> 
