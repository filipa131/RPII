<?php

// Manualno inicijaliziramo bazu ako veÄ‡ nije.
require_once 'db.class.php';

$db = DB::getConnection();

$has_tables = false;

try
{
	$st = $db->prepare( 
		'SHOW TABLES LIKE :tblname'
	);

	$st->execute( array( 'tblname' => 'dz2_users' ) );
	if( $st->rowCount() > 0 )
		$has_tables = true;

	$st->execute( array( 'tblname' => 'dz2_follows' ) );
	if( $st->rowCount() > 0 )
		$has_tables = true;

	$st->execute( array( 'tblname' => 'dz2_quacks' ) );
	if( $st->rowCount() > 0 )
		$has_tables = true;

}
catch( PDOException $e ) { exit( "PDO error [show tables]: " . $e->getMessage() ); }


if( $has_tables )
{
	exit( 'Tablice dz2_users / dz2_follows / dz2_quacks veÄ‡ postoje. ObriĹˇite ih pa probajte ponovno.' );
}


try
{
	$st = $db->prepare( 
		'CREATE TABLE IF NOT EXISTS dz2_users (' .
		'id int NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		'username varchar(50) NOT NULL,' .
		'password_hash varchar(255) NOT NULL,'.
		'email varchar(50) NOT NULL,' .
		'registration_sequence varchar(20) NOT NULL,' .
		'has_registered int)'
	);

	$st->execute();
}
catch( PDOException $e ) { exit( "PDO error [create dz2_users]: " . $e->getMessage() ); }

echo "Napravio tablicu dz2_users.<br />";

try
{
	$st = $db->prepare( 
		'CREATE TABLE IF NOT EXISTS dz2_follows (' .
		'id_user int NOT NULL,' .
		'id_followed_user int NOT NULL)'
	);

	$st->execute();
}
catch( PDOException $e ) { exit( "PDO error [create dz2_follows]: " . $e->getMessage() ); }

echo "Napravio tablicu dz2_follows.<br />";


try
{
	$st = $db->prepare( 
		'CREATE TABLE IF NOT EXISTS dz2_quacks (' .
		'id int NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		'id_user INT NOT NULL,' .
		'quack varchar(140) NOT NULL,' .
		'date DATETIME NOT NULL)'
	);

	$st->execute();
}
catch( PDOException $e ) { exit( "PDO error [create dz2_quacs]: " . $e->getMessage() ); }

echo "Napravio tablicu dz2_quacks.<br />";


// Ubaci neke korisnike unutra
try
{
	$st = $db->prepare( 'INSERT INTO dz2_users(username, password_hash, email, registration_sequence, has_registered) VALUES (:username, :password, \'a@b.com\', \'abc\', \'1\')' );

	$st->execute( array( 'username' => 'elon', 'password' => password_hash( 'tesla', PASSWORD_DEFAULT ) ) );
	$st->execute( array( 'username' => 'KingJames', 'password' => password_hash( 'lebron', PASSWORD_DEFAULT ) ) );
	$st->execute( array( 'username' => 'StephenCurry30', 'password' => password_hash( '402', PASSWORD_DEFAULT ) ) );
	$st->execute( array( 'username' => 'billgates', 'password' => password_hash( 'mirkosoft', PASSWORD_DEFAULT ) ) );
}
catch( PDOException $e ) { exit( "PDO error [insert dz2_users]: " . $e->getMessage() ); }

echo "Ubacio u tablicu dz2_users.<br />";


// Ubaci neke followere unutra (ovo nije baĹˇ pametno ovako raditi, preko hardcodiranih id-eva usera)
try
{
	$st = $db->prepare( 'INSERT INTO dz2_follows(id_user, id_followed_user) VALUES (:id1, :id2)' );

	$st->execute( array( 'id1' => '1', 'id2' => '2' ) ); // elon -> KingJames
	$st->execute( array( 'id1' => '2', 'id2' => '1' ) ); // KingJames -> elon
	$st->execute( array( 'id1' => '3', 'id2' => '1' ) ); // StephenCurry30 -> elon
 	$st->execute( array( 'id1' => '1', 'id2' => '4' ) ); // elon -> billgates
	$st->execute( array( 'id1' => '4', 'id2' => '1' ) ); // billgates -> elon
}
catch( PDOException $e ) { exit( "PDO error [dz2_follows]: " . $e->getMessage() ); }

echo "Ubacio u tablicu dz2_follows.<br />";


// Ubaci neke quackove unutra (ovo nije baĹˇ pametno ovako raditi, preko hardcodiranih id-eva usera)
try
{
	$st = $db->prepare( 'INSERT INTO dz2_quacks(id_user, quack, date) VALUES (:id_user, :quack, :date)' );

	$st->execute( array( 'id_user' => 1, 'quack' => 'Well done @KingJames and @StephenCurry30 to reach #NBA conference semis. Big fan of both #Lakers and #Warriors!', 'date' => '2023-04-26 12:45:00') );
	$st->execute( array( 'id_user' => 1, 'quack' => 'Thank you for the kind words, @BillGates. #quack is so much better indeed...', 'date' => '2023-04-28 19:23:45') );
	$st->execute( array( 'id_user' => 2, 'quack' => 'First game of #NBA conference semis in #LA on Wednesday!', 'date' => '2023-05-01 17:45:00') );
	$st->execute( array( 'id_user' => 2, 'quack' => 'Big win over #Memphis! Next up: @StephenCurry30 and #Warriors...', 'date' => '2023-04-19 12:23:00') );
	$st->execute( array( 'id_user' => 3, 'quack' => 'Congrats to @KingJames and #Lakers for reaching #NBA semis! #seeyousoon', 'date' => '2023-04-19 12:22:45') );
	$st->execute( array( 'id_user' => 3, 'quack' => 'Practicing some 3-pointers for #NBA conference semis...see you in soon in #LA!', 'date' => '2023-04-19 12:32:45') );
	$st->execute( array( 'id_user' => 4, 'quack' => 'I am a big fan of @elon! Driving my #tesla every day! But I prefer #quack :)', 'date' => '2023-04-20 22:10:00') );
	$st->execute( array( 'id_user' => 4, 'quack' => 'Well done @StephenCurry30! Good luck in #LA #NBA', 'date' => '2023-04-20 23:55:00') );
	$st->execute( array( 'id_user' => 3, 'quack' => 'I\'m very happy to use #quack! Sorry, @elon, but #quack is perfect!', 'date' => '2023-04-23 12:00:05') );
	$st->execute( array( 'id_user' => 3, 'quack' => 'The best day ever! #50points', 'date' => '2023-04-30 12:00:00') );
}
catch( PDOException $e ) { exit( "PDO error [dz2_quacks]: " . $e->getMessage() ); }

echo "Ubacio u tablicu dz2_quacks.<br />";

?> 