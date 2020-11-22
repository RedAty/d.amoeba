<?php
/**
 * D.Amoeba Game main index.php
 *
 * @file    index.php
 * @author  Attila Reterics
 * @license GPL-3
 * @url     https://github.com/RedAty/d.amoeba
 * @email   reterics.attila@gmail.com
 * @date    2020. 11. 20.
 */

session_start();

require_once "./lib/methods.php";

$size = 6;
$winnerPoint = 4;
$lang = "en";
/**
 * Declare default Session Variables
 */
if(!isset($_SESSION['gameStarted'])) {
    $_SESSION['gameStarted'] = false;
}
if(!isset($_SESSION['current-player'])) {
    $_SESSION['current-player'] = 1;
}
if(!isset($_SESSION['map'])) {
    $_SESSION['map'] = generateMap(6,6);
}
if(isset($_SESSION['size'])){
    $size = $_SESSION['size'];
}
if(isset($_SESSION['winnerPoint'])){
    $winnerPoint = $_SESSION['winnerPoint'];
}
if(!isset($_SESSION['lang'])){
    $_SESSION['lang'] = "en";
}
if(isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
}


/**
 * Start the game with POST arguments
 */
if(isset($_POST['start']) && isset($_POST['mode'])){
    $playerNumber = 2;
    $ai = false;
    if($_POST['mode'] == "ai"){

        echo "ok-AI Mode Enabled-";
        $ai = true;
    } elseif($_POST['mode'] == "player"){
        if(isset($_POST['number'])){
            $playerNumber = $_POST['number'];
        }
        echo "ok-Multiplayer Mode Enabled-";
    }
    $_SESSION['aiMode'] = $ai;
    $_SESSION['gameStarted'] = true;
    $_SESSION['map'] = generateMap(6,6);
    $_SESSION['winnerPoint'] = 4;
    $_SESSION['playerCount'] = $playerNumber;
    $_SESSION['steps'] = 0;
    exit();
}


/**
 * In-Game Logic
 */
$winner = 0;
if(isset($_POST['x']) && isset($_POST['y']) && $_SESSION['gameStarted']) {
    $x = $_POST['x'];
    $y = $_POST['y'];
    if($_SESSION['map'][$x][$y] == 0) {
        $_SESSION['map'][$x][$y] = $_SESSION['current-player'];
        $worthOfThisStep = getMaximumLineLength($_SESSION['map'],$x,$y);
        if(isset($_SESSION['steps'])){
            $_SESSION['steps'] = $_SESSION['steps'] + 1;
        }

        if($worthOfThisStep >= $winnerPoint){
            $winner = $_SESSION['current-player'];
        }
        if($winner != 0){
            $_SESSION['gameStarted'] = false;
            echo "ok-".($_SESSION['current-player']-1)."-\n";
            echo "steps-".$_SESSION['steps']."-\n";
            echo "win-".$winner."-";
            exit();
        } else {

            if($_SESSION['current-player'] == $_SESSION['playerCount']) {
                $_SESSION['current-player'] = 1;
            } else {
                $_SESSION['current-player'] = $_SESSION['current-player'] + 1;
            }

            if(isset($_SESSION['steps']) && $_SESSION['steps'] >= 36) {
                echo "draw---\n";
                exit();
            }

            //If the next player is the computer then he make the step
            if($_SESSION['current-player'] == 2 && isset($_SESSION['aiMode']) && $_SESSION['aiMode'] == true) {
                $aiPlayer = 2;
                $player = 1;
                $aiAnswer = aiTacticV2($_SESSION['map'],array("x"=>$x,"y"=>$y),$aiPlayer);
                $_SESSION['map'] = $aiAnswer["map"];
                $_SESSION['current-player'] = $player;
                if(isset($_SESSION['steps'])){
                    $_SESSION['steps'] = $_SESSION['steps'] + 1;
                }
                $worthOfThisStep = getMaximumLineLength($_SESSION['map'],$aiAnswer["x"],$aiAnswer["y"]);
                if($worthOfThisStep >= $winnerPoint){
                    $winner = 2;
                }
                if($winner != 0){
                    echo "steps-".$_SESSION['steps']."-\n";
                    echo "move-".$aiAnswer["x"]."-".$aiAnswer["y"]."\n";
                    $_SESSION['gameStarted'] = false;
                    echo "win-".$winner;
                    exit();
                } else {
                    echo "steps-".$_SESSION['steps']."-\n";
                    echo "move-".$aiAnswer["x"]."-".$aiAnswer["y"];
                    exit();
                }

            } else {
                echo "steps-".$_SESSION['steps']."-\n";
                if($_SESSION['current-player'] == 1){
                    echo "ok-".($_SESSION['playerCount'])."-\n";
                    //echo "info-LastPlayer:".$_SESSION['playerCount'].",CurrentPlayer:".$_SESSION['current-player']."\n";
                } else {
                    echo "ok-".($_SESSION['current-player']-1)."-\n";
                    //echo "info-LastPlayer:".($_SESSION['current-player']-1).",CurrentPlayer:".$_SESSION['current-player']."\n";
                }
                exit();
            }
        }
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Wrong Move', true, 500);
        die();
    }
}

require_once "./lib/locales.php";

/**
 * Change Language
 */
if(isset($_POST['language']) && isset($LANG[$_POST['language']])) {
    $lang = $_POST['language'];
    $_SESSION['lang'] = $_POST['language'];
    echo "ok";
    exit();
}



?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Amoba Game</title>
    <link href="css/style.css" rel="stylesheet">
    <script src="js/requests.js"></script>
</head>
<body>

<div class="main-wrapper">
    <div class="lang-selector">
        <img src="img/en.svg" onclick="Request.changeLanguage('en')" alt="English Language" title="English Language"/>
        <img src="img/hu.svg" onclick="Request.changeLanguage('hu')" alt="Magyar Nyelv" title="Magyar Nyelv"/>
        <img src="img/de.svg" onclick="Request.changeLanguage('de')" alt="Deutsche Sprache" title="Deutsche Sprache"/>
    </div>
<?php

require_once "./lib/menu.php";


$page = "menu";

if(isset($_GET['menu'])) {
    $page = $_GET['menu'];
}

if($page === "menu"){
    renderMenu();
} elseif($page === "multiplayer-menu"){
    renderMultiplayerMenu();
} elseif($page === "multiplayer-game"){
    renderGameScreen("multiplayer-menu");
} elseif($page === "singleplayer-game"){
    renderGameScreen("menu");
} elseif($page === "close"){
    session_destroy();
    session_start();
    $_SESSION['gameStarted'] = false;
    $_SESSION['current-player'] = 1;
    $_SESSION['map'] = generateMap(6,6);
    $_SESSION['aiMode'] = false;
    if($lang){
        $_SESSION['lang'] = $lang;
    }
    header("Location: ?mode=menu");
    exit;
} else {
    echo "404 Not Found";
}
?>
</div>
</body>
</html>
