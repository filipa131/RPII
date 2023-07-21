<!DOCTYPE html>
<html>
<head>
    <title>Sokoban</title>
    <style>
        .content-container {    
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // . = prazno polje, # = zid, T = cilj, P = osoba, D = dijamant

    $POCETNAmap = array(
        array('.', '.', '#', '#', '#', '#', '#', '.'), 
        array('#', '#', '.', '.', '.', '.', '#', '.'), 
        array('#', 'T', '.', '.', '.', '.', '#', '.'),
        array('#', '#', '#', '.', '.', 'T', '#', '.'),
        array('#', 'T', '#', '#', '.', '.', '#', '.'),
        array('#', '.', '#', '.', 'T', '.', '#', '#'),
        array('#', '.', '.', 'T', '.', '.', 'T', '#'),
        array('#', '.', '.', '.', 'T', '.', '.', '#'),
        array('#', '#', '#', '#', '#', '#', '#', '#')
    );

    $POCETNAmap2 = array(
        array('.', '.', '.', '.', '.', '.', '.', '.'), 
        array('.', '.', '.', '.', '.', '.', '.', '.'), 
        array('.', '.', 'P', 'D', '.', '.', '.', '.'),
        array('.', '.', '.', '.', 'D', '.', '.', '.'),
        array('.', '.', '.', '.', 'D', '.', '.', '.'),
        array('.', '.', '.', '.', '.', '.', '.', '.'),
        array('.', 'D', '.', 'D', 'D', 'D', '.', '.'),
        array('.', '.', '.', '.', '.', '.', '.', '.'),
        array('.', '.', '.', '.', '.', '.', '.', '.')
    );

    $POCETNAmap3 = array(
        array('#', '#', '#', '#', '#', '.', '.'), 
        array('#', '.', 'T', '.', '#', '.', '.'), 
        array('#', '.', 'T', '.', '#', '#', '#'),
        array('#', '.', '.', '.', '.', '.', '#'),
        array('#', 'T', 'T', '.', '.', '.', '#'),
        array('#', '.', '.', '.', 'T', '.', '#'),
        array('#', '#', '#', '#', '#', '#', '#')
    );

    $POCETNAmap4 = array(
        array('.', '.', '.', '.', '.', '.', '.'), 
        array('.', '.', '.', '.', '.', '.', '.'), 
        array('.', 'D', 'D', 'P', '.', '.', '.'),
        array('.', '.', 'D', 'D', '.', '.', '.'),
        array('.', '.', '.', 'D', '.', '.', '.'),
        array('.', '.', '.', '.', '.', '.', '.'),
        array('.', '.', '.', '.', '.', '.', '.')
    );

    // Funkcija za generiranje padaju�eg izbornika s pozicijama
    function generirajPadajuciIzbornik($map) {
            $izbornik = '<select name="dijamant">';
            for ($red = 0; $red < count($map); $red++) {
            for ($stupac = 0; $stupac < count($map[$red]); $stupac++) {
                    if ($map[$red][$stupac] == 'D') {
                    $izbornik .= '<option value="' . $red . ',' . $stupac . '">(' . ($red + 1) . ', ' . ($stupac + 1) . ')</option>';
                    }
                }
            }
            $izbornik .= '</select>';
            return $izbornik;
    }

    // Funkcija za crtanje tablice
    function nacrtajTablicu($map, $map2){ 
        echo '<table style="border-collapse: collapse;">'; 
            for ($row = 0; $row < count($map); $row++) {
                echo '<tr>'; 
                for ($col = 0; $col < count($map[$row]); $col++) {
                    echo '<td style="border: 1px solid black; width: 50px; height: 50px; text-align: center; font-weight: bold; background-color:';

                    if($map[$row][$col] == '#'){
                        echo '#4F6D7E;"></td>'; 
                    }
                    else if($map2[$row][$col] == 'P' && $map[$row][$col] == 'T'){
                        echo 'silver;"><img src="P.png" width="30" height="30"></td>'; 
                    }
                    else if($map2[$row][$col] == 'P'){
                        echo ';"><img src="P.png" width="30" height="30"></td>'; 
                    }
                    else if($map2[$row][$col] == 'D' && $map[$row][$col] == 'T'){
                        echo 'silver;"><img src="D.png" width="30" height="30"></td>'; 
                    }
                    else if($map2[$row][$col] == 'D' && $map[$row][$col] == '.'){
                        echo ';"><img src="D.png" width="30" height="30"></td>'; 
                    }
                    else if($map[$row][$col] == 'T'){
                        echo 'silver;"></td>'; 
                    }
                    else{
                        echo ';"></td>'; 
                    }
                    
                    echo '</td>'; 
                }
                echo '</tr>'; 
            }
        echo '</table>'; 
    }

    // Funkcija za početnoo generiranje naslova i tablice
    function generiraj($playerName, $numMoves, $map, $map2){
        echo "<h1>Sokoban</h1>";
        echo "Igrač $playerName je dosad napravio $numMoves pomaka.";
        echo '<br></br>';
        nacrtajTablicu($map, $map2); 
    }

    // Funkcija za provjeru dopuštenog kretanja
    function dopustenoKretanje($map, $map2, $playerX, $playerY, $moveX, $moveY){
        $newX = $playerX + $moveX;
        $newY = $playerY + $moveY;
        if ($newX < 0 || $newX >= count($map[0]) || $newY < 0 || $newY >= count($map)) {
            return false;
        }

        $nextCell = $map[$newY][$newX];
        $nextCell2 = $map2[$newY][$newX];
        if($nextCell === '#'){
            return false;
        }
        elseif($nextCell2 === 'D'){
            //Provjera polja iza dijamanta u smjeru kretanja
            $nextDiamondX = $newX + $moveX;
            $nextDiamondY = $newY + $moveY;
            if ($nextDiamondX < 0 || $nextDiamondX >= count($map[0]) || $nextDiamondY < 0 || $nextDiamondY >= count($map)) {
                return false;
            }
            $nextDiamondCellMap = $map[$nextDiamondY][$nextDiamondX];
            $nextDiamondCellMap2 = $map2[$nextDiamondY][$nextDiamondX];
            if ($nextDiamondCellMap === '#' || $nextDiamondCellMap2 === 'D') {
                return false; // Nedozvoljeno kretanje ako iza dijamanta nije prazno polje
            }
        }
        return true;
    } 

    // Dohvati trenutnu poziciju igrača
    function pozicijaIgraca($map2){
        $playerPosition = array();
        $rows = count($map2);
        $cols = count($map2[0]);

        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                if ($map2[$i][$j] == 'P') {
                    $playerPosition = array($i, $j);
                    break;
                }
            }     
        }
        return $playerPosition;
    }

    // Provjeri je li igra gotova
    function igraGotova($map, $map2){
        for ($row = 0; $row < count($map); $row++){
            for ($col = 0; $col < count($map[$row]); $col++){
                if($map2[$row][$col] == 'D' && !($map[$row][$col] == 'T')) return false;
            }
        }
        return true;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["playerName"]) && !empty($_POST["playerName"]) && isset($_POST["pocetak"])) {
            $playerName = $_POST["playerName"];
            $numMoves = 0; 
            $odabranaIgra = $_POST["pocetak"];

            $_SESSION["numMoves"] = $numMoves; 
            $_SESSION["playerName"] = $playerName; 
    
            switch ($odabranaIgra) {
                case 'Započni igru 1':
                    $_SESSION["map"] = $POCETNAmap;
                    $_SESSION["map2"] = $POCETNAmap2;
                    $_SESSION["POCETNAmap"] = $POCETNAmap;
                    $_SESSION["POCETNAmap2"] = $POCETNAmap2;
                    break;
                case 'Započni igru 2':
                    $_SESSION["map"] = $POCETNAmap3;
                    $_SESSION["map2"] = $POCETNAmap4;
                    $_SESSION["POCETNAmap"] = $POCETNAmap3;
                    $_SESSION["POCETNAmap2"] = $POCETNAmap4;
                    break;
                default:
                    break;
            }

            $map = $_SESSION["map"];
            $map2 = $_SESSION["map2"];

            generiraj($playerName, $numMoves, $map, $map2);
        }
        
        if (isset($_POST["button"])) {
            if (isset($_SESSION["numMoves"]) && isset($_SESSION["playerName"]) && isset($_SESSION["map"]) && isset($_SESSION["map2"])) {
                $numMoves = $_SESSION["numMoves"];
                $playerName = $_SESSION["playerName"];
                $map = $_SESSION['map'];
                $map2 = $_SESSION['map2'];
            
                switch ($_POST["button"]) {
                    case "Gore":
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, 0, -1)){
                            generiraj($playerName, $numMoves, $map, $map2);
                            break;
                        }
                        $numMoves++; 
                        $_SESSION["numMoves"] = $numMoves;
                        $novix = $x;
                        $noviy = $y - 1;
                        if ($map2[$noviy][$novix] === 'D') {
                            $map2[$noviy][$novix] = 'P';
                            $map2[$y][$x] = '.';
                            $map2[$noviy - 1][$novix] = 'D';
                        }
                        else{
                            $map2[$y][$x] = '.';
                            $map2[$noviy][$novix] = 'P';
                        }
                        $_SESSION['map'] = $map;
                        $_SESSION['map2'] = $map2;
                        generiraj($playerName, $numMoves, $map, $map2);
                        break;
                    case "Dolje":
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, 0, 1)){
                            generiraj($playerName, $numMoves, $map, $map2);
                            break;
                        }
                        $numMoves++; 
                        $_SESSION["numMoves"] = $numMoves;
                        $novix = $x;
                        $noviy = $y + 1;
                        if ($map2[$noviy][$novix] === 'D') {
                            $map2[$noviy][$novix] = 'P';
                            $map2[$y][$x] = '.';
                            $map2[$noviy + 1][$novix] = 'D';
                        }
                        else{
                            $map2[$y][$x] = '.';
                            $map2[$noviy][$novix] = 'P';
                        }
                        generiraj($playerName, $numMoves, $map, $map2);                        
                        $_SESSION['map'] = $map;
                        $_SESSION['map2'] = $map2;
                        break;
                    case "Lijevo":
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, -1, 0)){
                            generiraj($playerName, $numMoves, $map, $map2);
                            break;
                        }
                        $numMoves++; 
                        $_SESSION["numMoves"] = $numMoves;
                        $novix = $x - 1;
                        $noviy = $y;
                        if ($map2[$noviy][$novix] === 'D') {
                            $map2[$noviy][$novix] = 'P';
                            $map2[$y][$x] = '.';
                            $map2[$noviy][$novix - 1] = 'D';
                        }
                        else{
                            $map2[$y][$x] = '.';
                            $map2[$noviy][$novix] = 'P';
                        }
                        generiraj($playerName, $numMoves, $map, $map2);                        
                        $_SESSION['map'] = $map;
                        $_SESSION['map2'] = $map2;
                        break;
                    case "Desno":
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, 1, 0)){
                            generiraj($playerName, $numMoves, $map, $map2);
                            break;
                        }
                        $numMoves++; 
                        $_SESSION["numMoves"] = $numMoves;
                        $novix = $x + 1;
                        $noviy = $y;
                        if ($map2[$noviy][$novix] === 'D') {
                            $map2[$noviy][$novix] = 'P';
                            $map2[$y][$x] = '.';
                            $map2[$noviy][$novix + 1] = 'D';
                        }
                        else{
                            $map2[$y][$x] = '.';
                            $map2[$noviy][$novix] = 'P';
                        }
                        generiraj($playerName, $numMoves, $map, $map2);                        
                        $_SESSION['map'] = $map;
                        $_SESSION['map2'] = $map2;
                        break;
                    default:
                        //Nijedan gumb nije pritisnut
                        break;
                }
            }
        }

        if (isset($_POST["submit"]) && isset($_SESSION["numMoves"]) && isset($_SESSION["playerName"]) && isset($_SESSION["map"]) && isset($_SESSION["map2"])) {
            // Provjeri koji radio button je odabran
            if (isset($_POST["odabir"])) {
                $selectedOption = $_POST["odabir"];               
                if ($selectedOption === "radio1") {
                    $map = $_SESSION['POCETNAmap'];
                    $map2 = $_SESSION['POCETNAmap2'];
                    $_SESSION['map'] = $map;
                    $_SESSION['map2'] = $map2;
                    $numMoves = 0;
                    $playerName = $_SESSION['playerName'];
                    $_SESSION['numMoves'] = $numMoves;
                    $_SESSION['playerName'] = $playerName;
                    generiraj($playerName, $numMoves, $map, $map2);
                } 
                elseif ($selectedOption === "radio2") {
                    $map = $_SESSION['map'];
                    $map2 = $_SESSION['map2'];
                    if (isset($_POST["dijamant"]) && !empty($_POST["dijamant"]) && !igraGotova($map, $map2)) {
                        $selectOption = $_POST["dijamant"];  
                        list($y, $x) = explode(",", $selectOption);
                        $map = $_SESSION['map'];
                        $map2 = $_SESSION['map2'];
                        $map2[$y][$x] = '.';
                        $_SESSION['map2'] = $map2;
                        $numMoves = $_SESSION["numMoves"];
                        $playerName = $_SESSION["playerName"];
                        generiraj($playerName, $numMoves, $map, $map2); 
                    }
                    else{
                        $numMoves = $_SESSION["numMoves"];
                        $playerName = $_SESSION["playerName"];
                        generiraj($playerName, $numMoves, $map, $map2); 
                    }
                } 
            }
            else {
                $map = $_SESSION['map'];
                $map2 = $_SESSION['map2'];
                $numMoves = $_SESSION['numMoves'];
                $playerName = $_SESSION['playerName'];
                generiraj($playerName, $numMoves, $map, $map2);  
            }
            
            
        }

        if(isset($_SESSION["map"]) && isset($_SESSION["map2"])){
            $map = $_SESSION['map'];
            $map2 = $_SESSION['map2'];
            if(igraGotova($map, $map2)){
                ?>
                <h3 style="margin-left: 10px; color: #5781A3">Čestitam, završili ste igru!</h3>
                <?php
            }
        }

        ?>

        <div class="content-container" style="">
            <p style="margin-left: 10px; display: inline-block; white-space: nowrap;">Pomakni igrača za jedno mjesto u smjeru:</p>
            
            <form method="post" action="">
                <input type="submit" name="button" value="Gore" style=" margin-left: 75px; display: inline-block;" 
                    <?php 
                        if((isset($_POST["button"]) || isset($_POST["pocetak"]) || isset($_POST["submit"]) || isset($_POST["dijamant"])) && isset($_SESSION["map"]) && isset($_SESSION["map2"])){ 
                        $map = $_SESSION['map'];
                        $map2 = $_SESSION['map2'];
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, 0, -1) || igraGotova($map, $map2)){
                        echo "disabled";} 
                        } 
                    ?> >
                <br></br>
                <input type="submit" name="button" value="Lijevo" style=" margin-left: 10px; display: inline-block;"
                    <?php 
                        if((isset($_POST["button"]) || isset($_POST["pocetak"]) || isset($_POST["submit"]) || isset($_POST["dijamant"])) && isset($_SESSION["map"]) && isset($_SESSION["map2"])){ 
                        $map = $_SESSION['map'];
                        $map2 = $_SESSION['map2'];
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, -1, 0) || igraGotova($map, $map2)){
                        echo "disabled";} 
                        } 
                    ?> >
                <input type="submit" name="button" value="Desno" style="  margin-left: 75px; display: inline-block;"
                    <?php 
                        if((isset($_POST["button"]) || isset($_POST["pocetak"]) || isset($_POST["submit"]) || isset($_POST["dijamant"])) && isset($_SESSION["map"]) && isset($_SESSION["map2"])){ 
                        $map = $_SESSION['map'];
                        $map2 = $_SESSION['map2'];
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, 1, 0) || igraGotova($map, $map2)){
                        echo "disabled";} 
                        } 
                    ?> >
                <br></br>
                <input type="submit" name="button" value="Dolje" style="  margin-left: 75px; display: inline-block;"
                    <?php 
                        if((isset($_POST["button"]) || isset($_POST["pocetak"]) || isset($_POST["submit"]) || isset($_POST["dijamant"])) && isset($_SESSION["map"]) && isset($_SESSION["map2"])){ 
                        $map = $_SESSION['map'];
                        $map2 = $_SESSION['map2'];
                        list($y, $x) = pozicijaIgraca($map2);
                        if(!dopustenoKretanje($map, $map2, $x, $y, 0, 1) || igraGotova($map, $map2)){
                        echo "disabled";} 
                        } 
                    ?> >
            </form> 

            <p style="margin-left: 10px; display: inline-block; white-space: nowrap;">Ili odaberi željenu akciju:</p>
            
            <form method="post" action="">
                <input type="radio" id="radio1" value="radio1" name="odabir" style="margin-left: 20px; display: inline; ">
                <label for="radio1" style="display: inline-block; white-space: nowrap">Pokreni sve ispočetka</label>
                <br></br>
                <input type="radio" id="radio2" value="radio2" name="odabir" style="margin-left: 20px; display: inline; ">
                <label for="radio2" style="display: inline-block; white-space: nowrap">Obriši dijamant s pozicije (red, stupac) =</label>

                <?php $map2 = $_SESSION['map2']; ?>
                <div style="display: inline-block;">
                    <?php echo generirajPadajuciIzbornik($map2); ?>
                </div>

                <br></br>
                <input type="submit" name="submit" value="Izvrši akciju!" id="izvrsi" style="margin-left: 20px; display: inline-block; white-space: nowrap;">            
            
            </form> 
   
        </div>
        
        <?php

    } else {
        session_unset();
        session_destroy();
        echo "<h1>Sokoban</h1>";
        echo '<form method="post" action="">';
            echo '<label for="playerName">Unesi ime igrača: </label>';
            echo '<input type="text" name="playerName" id="playerName" required>';
            echo '&nbsp;';
            echo '<input type="submit" name="pocetak" value="Započni igru 1">';
            echo '&nbsp;';
            echo '<input type="submit" name="pocetak" value="Započni igru 2">';
        echo '</form>';
    }

    ?>
</body>
</html>    