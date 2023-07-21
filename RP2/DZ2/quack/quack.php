<?php 

require_once 'crtaj_html.php';
require_once 'analiziraj_POST.php';
require_once 'db.class.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if( isset( $_SESSION['username'] ) )
{
	exit();
}

if( isset( $_POST['username'] ) )
{
	analiziraj_POST_login();
	exit();
}

else
{
	crtaj_formaZaLogin();
	exit();
}

?> 
