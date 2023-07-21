<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Quack</title>
        <style>
            body {
            font-family: Arial, sans-serif;
            }

            .center {
                text-align: center;
                font-size: 20px; 
                font-family: 'Pacifico', cursive;
            }
            
            .header {
                background-color: #4682B4;
                padding: 10px;
                display: flex;
                justify-content: space-between;
                color: #FFFFFF;
            }
            
            .logout {
                background-color: #4682B4;
                text-align: right;
                padding-right: 10px;
                color: #FFFFFF;
            }

            .gumbic {
                
            }

            .gumbic button {
                padding: 5px 10px;
                background-color: #4682B4;
                color: #FFFFFF;
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }
            
            .gumbici {
                display: flex;
                background-color: #4682B4;
                justify-content: space-between;
                align-items: center;
                margin-left: 120px;
                margin-top: -40px;
                padding: 10px;
            }

            .gumbici input[type="submit"] {
                margin-right: 47px;
                font-family: 'Pacifico', cursive;
                color: #4682B4;
                background-color: #FFFFFF;
                border: 1px solid #FFFFFF;
                padding: 5px 10px;
                transition: background-color 0.3s, color 0.3s;
                border-radius: 7px;
            }
            
            .quack-form {
                padding: 10px;
            }
            
            .quack-form input[type="text"] {
                width: 300px;
                margin-right: 10px;
                padding: 5px;
                border-radius: 5px;
                border: 1px solid #4682B4;
            }
            
            .quack-form input[type="submit"] {
                padding: 5px 10px;
                background-color: #4682B4;
                color: #FFFFFF;
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }

            .quack-form input[type="password"] {
                padding: 5px 10px;
                background-color: #4682B4;
                color: #FFFFFF;
                border: 1px solid #4682B4;
                cursor: pointer;
                border-radius: 5px;
            }

        </style>
    </head>
</html>
<?php 

require_once 'db.class.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
function dohvati_id_korisnika($username)
{
    $db = DB::getConnection();
    try {
        $st = $db->prepare('SELECT id FROM dz2_users WHERE username=:username');
        $st->execute(array(':username' => $username));
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['id'];
        } else {
            return null;
        }
    } catch (PDOException $e) {
        crtaj_formaZaLogin('Greška: ' . $e->getMessage());
        return;
    }
}

function dohvati_username_korisnika($id)
{
    $db = DB::getConnection();
    try {
        $st = $db->prepare('SELECT username FROM dz2_users WHERE id=:id');
        $st->execute(array(':id' => $id));
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['username'];
        } else {
            return null;
        }
    } catch (PDOException $e) {
        crtaj_formaZaLogin('Greška: ' . $e->getMessage());
        return;
    }
}

function zamijeni_hashtagove($quack) {
    $hashtags = [];
    preg_match_all('/#(\w+)/', $quack, $hashtags);

    foreach ($hashtags[0] as $hashtag) {
        $tag = substr($hashtag, 1); 
        $quack_link = 'prikazi_quackove_po_tagu.php?tag=' . urlencode($tag);
        $quack = str_replace($hashtag, '<a href="' . $quack_link . '">' . $hashtag . '</a>', $quack);
    }

    return $quack;
}

function prikazi_quackove_ulogiranog_korisnika($username){
	$id_user = dohvati_id_korisnika($username);

	if ($id_user !== null) {
		date_default_timezone_set('Europe/Zagreb');
        $db = DB::getConnection();

        try {
			$st = $db->prepare('SELECT dz2_quacks.quack, dz2_users.username, dz2_quacks.date FROM dz2_quacks JOIN dz2_users ON dz2_quacks.id_user = dz2_users.id WHERE dz2_quacks.id_user = :id_user ORDER BY dz2_quacks.date DESC');
			$st->execute(array(':id_user' => $id_user));

			while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $date = strtotime($row['date']);
                $formatted_date = strftime('%d.%m.%Y. %H:%M:%S', $date);
                
                echo '@' . $row['username'] . ' - ' . $formatted_date . '<br>';
                echo zamijeni_hashtagove($row['quack']) . '<br><br>';
            }
        } catch (PDOException $e) {
            crtaj_formaZaLogin('Greška: ' . $e->getMessage());
            return;
        }
    } else {
        echo 'Korisnik nije pronađen.';
    }
}

function objava_novog_quacka(){
	$username = $_SESSION['username'];
	$id_user = dohvati_id_korisnika($username);

	$db = DB::getConnection();

	$quack_text = $_POST['quack_text'];
	$id = $db->query("SELECT MAX(id) FROM dz2_quacks")->fetchColumn();
	$id++;
	$date = date('Y-m-d H:i:s');
	$st = $db->prepare('INSERT INTO dz2_quacks (id, id_user, quack, date) VALUES (:id, :id_user, :quack, :date)');
	$st->execute(array(':id' => $id, ':id_user' => $id_user, ':quack' => $quack_text, ':date' => $date));

	prikazi_quackove_ulogiranog_korisnika($username);
}

function dohvati_id_koje_username_prati($username) {
    $id_user = dohvati_id_korisnika($username);
    $db = DB::getConnection();
    
    try {
        $st = $db->prepare('SELECT id_followed_user FROM dz2_follows WHERE id_user = :id_user');
        $st->execute(array(':id_user' => $id_user));
        
        $id_followers = array();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $id_followed_user = $row['id_followed_user'];
            $id_followers[] = $id_followed_user;
        }        
        return $id_followers;
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
        return array(); 
    }
}

function dohvati_id_followers($username) {
    $id_followed_user = dohvati_id_korisnika($username);
    $db = DB::getConnection();
    
    try {
        $st = $db->prepare('SELECT id_user FROM dz2_follows WHERE id_followed_user = :id_followed_user');
        $st->execute(array(':id_followed_user' => $id_followed_user));
        
        $id_followers = array();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $id_user = $row['id_user'];
            $id_followers[] = $id_user;
        }        
        return $id_followers;
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
        return array(); 
    }
}


function ispis_id_followers($id_followers){
	foreach ($id_followers as $id_followed_user) {
		echo dohvati_username_korisnika($id_followed_user) . '<br><br>';
	}
}

function ispis_following_quacks($id_followers) {
    $db = DB::getConnection();
    
    try {
        $quacks = array(); 
        foreach ($id_followers as $id_user) {
            $st = $db->prepare('SELECT quack, id_user, date 
                                FROM dz2_quacks 
                                WHERE id_user = :id_user');
            $st->execute(array(':id_user' => $id_user));

            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $quacks[] = $row;
            }
        }

        // Sortiraj quackove po datumu silazno
        usort($quacks, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Ispis sortiranih quackova
        foreach ($quacks as $quack) {
            $timestamp = strtotime($quack['date']);
            $formattedDate = date('d.m.Y H:i:s', $timestamp);

            echo '@' . dohvati_username_korisnika($quack['id_user']) . ' - ' . $formattedDate . '<br>';
            echo zamijeni_hashtagove($quack['quack']) . '<br><br>';
        }
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
    }
}

function dohvati_i_ispisi_quacks_username() {
    $db = DB::getConnection();
    $pojam = $_SESSION['username'];

    
    try {
        if (strpos($pojam, '@') === 0) {
            $pojam = substr($pojam, 1); 
        }
        
        $st = $db->prepare("SELECT quack, id_user, date
                            FROM dz2_quacks
                            WHERE quack LIKE BINARY :tag
                            AND NOT quack REGEXP BINARY :regex
                            ORDER BY date DESC");
        
        $regex = '@'.$pojam.'[[:alnum:]]';
        
        $st->execute(array(
            ':tag' => '%@' . $pojam . '%',
            ':regex' => $regex
        ));

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $quack = zamijeni_hashtagove($row['quack']);
            $id_user = $row['id_user'];
            $date = $row['date'];
            $username = dohvati_username_korisnika($id_user);
            
            echo "<div>@$username - " . date('d.m.Y. H:i:s', strtotime($date)) . "</div>";
            echo "<div>$quack</div>";
            echo "<br>";
        }
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
    }
}

function dohvati_i_ispisi_quacks_search($pojam) {
    $db = DB::getConnection();
    
    try {
        if (strpos($pojam, '#') === 0) {
            $pojam = substr($pojam, 1); 
        }
        
        $st = $db->prepare("SELECT quack, id_user, date
                            FROM dz2_quacks
                            WHERE quack LIKE BINARY :tag
                            AND NOT quack REGEXP BINARY :regex
                            ORDER BY date DESC");
        
        $regex = '#'.$pojam.'[[:alnum:]]';
        
        $st->execute(array(
            ':tag' => '%#' . $pojam . '%',
            ':regex' => $regex
        ));

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $quack = zamijeni_hashtagove($row['quack']);
            $id_user = $row['id_user'];
            $date = $row['date'];
            $username = dohvati_username_korisnika($id_user);
            
            echo "<div>@$username - " . date('d.m.Y. H:i:s', strtotime($date)) . "</div>";
            echo "<div>$quack</div>";
            echo "<br>";
        }
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
    }
}


function provjeriTag($tag) {
    if ($tag[0] === '#') {
        if (strlen($tag) === 1) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function provjeriUsername($username) {
    if ($username[0] === '@') {
        if (strlen($tag) === 1) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function unfollow($user) {
    $db = DB::getConnection();
    $username = $_SESSION["username"];
    $id_user = dohvati_id_korisnika($username);
    $id_followed_user = dohvati_id_korisnika($user);

    try {
        $st = $db->prepare('DELETE FROM dz2_follows WHERE id_user = :id_user AND id_followed_user = :id_followed_user');
        $st->execute(array(':id_user' => $id_user, ':id_followed_user' => $id_followed_user));
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
    }
}

function follow($user) {
    $db = DB::getConnection();
    $username = $_SESSION["username"];
    $id_user = dohvati_id_korisnika($username);
    $id_followed_user = dohvati_id_korisnika($user);
    if($id_followed_user === NULL) return;
    
    try {
        $st = $db->prepare('SELECT COUNT(*) FROM dz2_follows WHERE id_user = :id_user AND id_followed_user = :id_followed_user');
        $st->execute(array(':id_user' => $id_user, ':id_followed_user' => $id_followed_user));
        $count = $st->fetchColumn();
        if($count == 0){
            $st = $db->prepare('INSERT INTO dz2_follows (id_user, id_followed_user) VALUES (:id_user, :id_followed_user)');
            $st->execute(array(':id_user' => $id_user, ':id_followed_user' => $id_followed_user));
        }
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
    }
}

if(isset($_POST["login"])){	
	$username = $_POST["username"];
}
else if(isset($_POST["my_quacks"])){
	$username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik();
	prikazi_quackove_ulogiranog_korisnika($username);	
}
else if(isset($_POST['objavi']) && !empty($_POST['quack_text']) && isset($_SESSION["username"])){
    date_default_timezone_set('Europe/Zagreb');
	$username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik();
	objava_novog_quacka();
}
else if(isset($_POST['objavi']) && empty($_POST['quack_text'])){
	$username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik();
    prikazi_quackove_ulogiranog_korisnika($username);
}
else if(isset($_POST["following"])){
	$username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik('', false);
    crtaj_follow();
    $id_followers = dohvati_id_koje_username_prati($username);
	ispis_following_quacks($id_followers);	
}
else if(isset($_POST["follow"]) && !empty($_POST['follow_text'])){
    $user = $_POST['follow_text'];
    $username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik('', false);
	crtaj_follow();
    if (substr($user, 0, 1) !== '@') {
        echo "Neispravan format, potrebno  je unijeti @username.<br><br>";     
    }
    else{
        $user = substr($user, 1);
        follow($user);
    }
    $id_followers = dohvati_id_koje_username_prati($username);
    ispis_following_quacks($id_followers);
}
else if(isset($_POST["follow"]) && empty($_POST['follow_text'])){
    $username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik('', false);
	crtaj_follow();
    echo "Unesite @username!<br><br>";
    $id_followers = dohvati_id_koje_username_prati($username);
    ispis_following_quacks($id_followers);
}
else if(isset($_POST["unfollow"]) && !empty($_POST['follow_text'])){
    $user = $_POST['follow_text'];
    $username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik('', false);
	crtaj_follow();
    if (substr($user, 0, 1) !== '@') {
        echo "Neispravan format, potrebno  je unijeti @username.<br><br>";    
    }
    else{
        $user = substr($user, 1);
        unfollow($user);
    }
    $id_followers = dohvati_id_koje_username_prati($username);
    ispis_following_quacks($id_followers);
}
else if(isset($_POST["unfollow"]) && empty($_POST['follow_text'])){
    $username = $_SESSION["username"];
    $id_followers = dohvati_id_koje_username_prati($username);
	crtaj_ulogiraniKorisnik('', false);
	crtaj_follow();
    echo "Unesite @username!<br><br>";
    ispis_following_quacks($id_followers);
}
else if(isset($_POST["followers"])){
	$username = $_SESSION["username"];
	$id_followers = dohvati_id_followers($username);
	crtaj_ulogiraniKorisnik('', false);
	ispis_id_followers($id_followers);
}
else if(isset($_POST["quacks_username"])){
	$username = $_SESSION["username"];
	crtaj_ulogiraniKorisnik('', false);
	dohvati_i_ispisi_quacks_username();
}
else if(isset($_POST["search"])){
	crtaj_ulogiraniKorisnik('', false);
	crtaj_search();
}
else if(isset($_POST["pretrazi"]) && !empty($_POST['pretrazi_text'])){
	crtaj_ulogiraniKorisnik('', false);
	crtaj_search();
	$pojam = $_POST['pretrazi_text'];
	if(provjeriTag($pojam)){
		dohvati_i_ispisi_quacks_search($pojam);
	}
    else{
        echo "Neispravan format, potrebno  je unijeti #search.";
        return;
    }
}
else if(isset($_POST["pretrazi"]) && empty($_POST['pretrazi_text'])){
	crtaj_ulogiraniKorisnik('', false);
	crtaj_search();
    echo "Unesite #search!";
}

function crtaj_formaZaLogin( $errorMsg = '' )
{
	?>
    <h1 style="color: #4682B4;">Quack!</h1>
    <div class="quack-form">
        <form method="post" action="<?php echo htmlentities( $_SERVER["PHP_SELF"] ); ?>">
            Korisničko ime:
            <input type="text" name="username" />
            <br><br>
            Lozinka:
            <input type="password" name="password_hash" />
            <br><br>
            <div class="gumbic">
            <button type="submit" name="login">Ulogiraj se!</button>
            </div>
        </form>
    </div>
	<p class="button" style="padding: 10px">
		Ako nemate korisnički račun, otvorite ga <a href="novi.php">ovdje</a>.
	</p>

	<?php
	if( $errorMsg !== '' )
		echo '<p>Greška: ' . $errorMsg . '</p>';

}


function crtaj_formaZaNovogKorisnika( $errorMsg = '' )
{
	?>
    <h1 style="color: #4682B4;">Quack!</h1>
    <div class="quack-form">
        <form method="post" action="<?php echo htmlentities( $_SERVER["PHP_SELF"] ); ?>">
            Odaberite korisničko ime:
            <input type="text" name="username" />
            <br><br>
            Odaberite lozinku:
            <input type="password" name="password_hash" />
            <br><br>
            Vaša mail-adresa:
            <input type="text" name="email" />
            <br><br>
            <div class="gumbic">
            <button type="submit">Stvori korisnički račun!</button>
            </div>
        </form>
    </div>
	<p style="padding: 10px">
		Povratak na <a href="quack.php">početnu stranicu</a>.
	</p>

	<?php
	if( $errorMsg !== '' )
		echo '<p>Greška: ' . $errorMsg . '</p>';
}


function crtaj_ulogiraniKorisnik( $errorMsg = '', $showQuackForm = true)
{
	?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf8" />
        <title>Quack</title>
    </head>
    <body>
        <div class="header">
            <div class="left-corner">
            <img src="transparentt_duck.png" alt="Logo" style="width: 100px; height: auto; margin-top: 15px"/>
            </div>
            <div class="center">QUACK!</div>
            <?php $username = $_SESSION['username']; ?>
            <div class="right-corner">@<?php echo $username; ?></div>
        </div>
        <div class="logout">
            <div class="gumbici">
                <form action="" method="post">
                    <input type="submit" name="my_quacks" value="My quacks">
                </form>
                <form action="" method="post">
                    <input type="submit" name="following" value="Following">
                </form>
                <form action="" method="post">
                    <input type="submit" name="followers" value="Followers">
                </form>
                <form action="" method="post">
                    <input type="submit" name="quacks_username" value="quacks @<?php echo $_SESSION['username']; ?>">
                </form>
                <form action="" method="post">
                    <input type="submit" name="search" value="#search">
                </form>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <?php if ($showQuackForm) : ?>
            <div class="quack-form">
                <form action="" method="post">
                    <input type="text" name="quack_text" placeholder="Unesite svoj quack">
                    <input type="submit" name="objavi" value="Objavi!">                
                </form>
            </div>
        <?php endif; ?>
        <br>
    </body>
    </html>
    <?php    
}


function crtaj_zahvalaNaPrijavi( $errorMsg = '' )
{
	?>

	<p>
		Zahvaljujemo na prijavi. Da biste dovršili registraciju, kliknite na link u mailu kojeg smo Vam poslali.
	</p>

	<p>
		Povratak na <a href="quack.php">početnu stranicu</a>.
	</p>


	<?php
}


function crtaj_zahvalaNaRegistraciji( $errorMsg = '' )
{
	?>

	<p>
		Registracija je uspješno provedena.<br />
		Sada se možete ulogirati na <a href="quack.php">početnoj stranici</a>.
	</p>

	<?php
}

function crtaj_search()
{
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf8" />
		<title>Quack</title>
	</head>
	<body>
        <div class="quack-form">
            <form action="" method="post">
                <input type="text" name="pretrazi_text" placeholder="#search">
                <input type="submit" name="pretrazi" value="Search">
            </form>
        </div>
        <br>
	</body>
	</html>
	<?php
}

function crtaj_follow()
{
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf8" />
		<title>Quack</title>
	</head>
	<body>
        <div class="quack-form">
            <form action="" method="post">
                <input type="text" name="follow_text" placeholder="@username">
                <input type="submit" name="follow" value="Follow">
                <input type="submit" name="unfollow" value="Unfollow">
            </form>
        </div>
        <br>
	</body>
	</html>
	<?php
}

?> 