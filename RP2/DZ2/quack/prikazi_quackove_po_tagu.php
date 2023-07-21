<?php
require_once 'crtaj_html.php';
require_once 'analiziraj_POST.php';
require_once 'db.class.php';

function ispis_quackova_po_tagu($tag) {
    if (isset($_POST['my_quacks']) || 
        isset($_POST['following']) || 
        isset($_POST['followers']) || 
        isset($_POST['quacks_username']) || 
        isset($_POST["follow"]) ||
        isset($_POST["unfollow"]) ||
        isset($_POST["pretrazi"]) || 
        isset($_POST["search"]) ||
        isset($_POST["objavi"]) ||
        (isset($_POST["follow"]) && !empty($_POST['follow_text'])) || 
        (isset($_POST["unfollow"]) && !empty($_POST['follow_text'])) || 
        (isset($_POST["pretrazi"]) && empty($_POST['pretrazi_text']))) {
        return; 
    }

    $db = DB::getConnection();

    try {
        
        $st = $db->prepare('SELECT dz2_quacks.quack, dz2_users.username, dz2_quacks.date 
                            FROM dz2_quacks 
                            JOIN dz2_users ON dz2_quacks.id_user = dz2_users.id 
                            WHERE dz2_quacks.quack REGEXP :regex
                            ORDER BY date DESC');
        $st->execute(array(':regex' => '[[:<:]]' . preg_quote($tag, '/') . '[[:>:]]'));
        
        crtaj_ulogiraniKorisnik('', false);
	    crtaj_search();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $date = strtotime($row['date']);
            $formatted_date = strftime('%d.%m.%Y. %H:%M:%S', $date);

            echo '@' . $row['username'] . ' - ' . $formatted_date . '<br>';
            echo zamijeni_hashtagove($row['quack']) . '<br><br>';
        }
        $st->closeCursor();
    } catch (PDOException $e) {
        echo 'Greška: ' . $e->getMessage();
    }
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['tag'])) {
    $tag = $_GET['tag'];
    ispis_quackova_po_tagu($tag);
}
else {
    echo 'Tag nije odabran.';
}
?>
