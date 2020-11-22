<?php
/**
 * Created by PhpStorm.
 * @file    menu.php
 * @author  Attila Reterics
 * @license GPL-3
 * @url     https://github.com/RedAty/d.amoeba
 * @date    2020. 11. 20.
 * @email   reterics.attila@gmail.com
 */


function renderMenu(){
    ?>
    <div class="container">
        <h3 class="title">
            <?php echo getTranslate("D.Amoeba Game") ?>
        </h3>
        <ul class="menu-options">
            <li class="menu-option" onclick="Request.startGame('?menu=singleplayer-game', 'mode=ai')"><?php echo getTranslate("singleplayer") ?></li>
            <li class="menu-option" onclick="Request.navigate('?menu=multiplayer-menu')"><?php echo getTranslate("multiplayer") ?></li>
        </ul>
    </div>
    <?php
}

function renderMultiplayerMenu(){
    ?>
    <div class="container">
        <h3 class="title">
            <?php echo getTranslate("multiplayer") ?>
        </h3>
        <form class="menu-options">
            <div class="line">
                <label for="player-number"><?php echo getTranslate("player_count") ?></label><input id="player-number" type="number" name="player-number" value="2" max="5" min="2">
            </div>
            <input type="button" value="<?php echo getTranslate("back") ?>" onclick="Request.navigate('?menu=menu')">
            <input type="button" value="<?php echo getTranslate("start_game") ?>" onclick="Request.startGame('?menu=multiplayer-game', 'mode=player&number='+document.getElementById('player-number').value)">

        </form>
    </div>
    <?php
}

/**
 * @param $playerID
 */
function inGameHeader($playerID) {
    ?>

    <nav class="header-navigation">
        <div class="title-container">
            <h2 class="title"> <?php echo getTranslate("D.Amoeba Game") ?></h2>
        </div>

        <div class="right-container">
            <div class="centered-container">

            </div>
             <ul class="stats">
                 <li class="user player-<?php echo $playerID; ?>">
                     <?php echo "Player-".$playerID; ?>
                 </li>
                 <li class="close" onclick="Request.navigate('?menu=close')">
                     <?php echo getTranslate("forfeit") ?>
                 </li>
             </ul>
        </div>
        <div class="clear"></div>

    </nav>
    <?php
}

function tableLogic(){
    ?>

    <script>
        /**
         * Make a move on the table
         * @param {{x:number, y:number}} coordinates
         * @param {number} userID
         */
        const moveOnTheTable = (coordinates, userID)=>{
            if(!coordinates || typeof coordinates !== "object"){
                console.error("coordinates must be an object");
                return;
            }
            const rows = document.querySelectorAll("table tr");
            if(rows[coordinates.x]){
                const columns = rows[coordinates.x].querySelectorAll("td");
                if(columns[coordinates.y]) {
                    const node = columns[coordinates.y];
                    const currentUser = node.className.trim() === "empty" ? 0 : Number.parseInt(node.className.trim().replace("player-",""));
                    if(currentUser !== 0){
                        console.warn("The Field is already occupied!");
                        console.warn(coordinates);
                    } else {
                        node.classList.remove("empty");
                        node.classList.add("player-"+userID);
                    }
                } else {
                    console.error("Invalid Column");
                }
            } else {
                console.error("Invalid Row");
            }
        };
        /**
         ** Converts the Give Up button into Back button
         */
        const convertButton = ()=>{
            const closeButton = document.querySelector(".stats li.close");
            if(closeButton){
                closeButton.innerHTML = "<?php echo getTranslate("forfeit") ?>";
            }
        };
        /**
         * Hides the Game Table when the game is finished
         * @param {string} text
         */
        const hideTable = (text)=>{
            const tableHider = document.querySelector(".table-hider");
            if(tableHider){
                tableHider.style.display = "block";
                if(text){
                    tableHider.innerHTML = text;
                }
            }
        };
        /**
         * Handle Lines of the Server Answers
         * @param {string} line
         * @param {{x:number, y:number}} coordinates
         */
        const handleAPILine =(line,coordinates)=>{
            const optionArray = line.split("-");
            switch(optionArray[0]){
                case "move":
                    moveOnTheTable(coordinates, 1);
                    moveOnTheTable({x:Number.parseInt(optionArray[1]),y:Number.parseInt(optionArray[2])}, 2);
                    break;
                case "ok":
                    console.warn("OK",optionArray[1]);
                    moveOnTheTable(coordinates, Number.parseInt(optionArray[1]));
                    break;
                case "win":
                    message("<?php echo getTranslate("winner_payer") ?>  "+optionArray[1]);
                    convertButton();
                    hideTable("<?php echo getTranslate("winner_payer") ?>  "+optionArray[1]);
                    break;
                case "draw":
                    message("<?php echo getTranslate("game_is_a_draw") ?>");
                    convertButton();
                    hideTable("<?php echo getTranslate("game_is_a_draw") ?>");
                    break;
                case "steps":
                    message("<?php echo getTranslate("fill_state") ?> " + Math.floor(Number.parseInt(optionArray[1]) / 36 *100) + "<?php echo getTranslate("fill_state_end") ?>");
                    break;
                case "info":
                    console.log(optionArray[1]);
                    break;
                default:
                    console.error("Unknown server answer:");
                    console.log(optionArray);
                    break;
            }
        };

        /**
         * Handle the Server answers by lines
         * @param {string} result
         * @param {{x:number, y:number}} coordinates
         */
        const handleAPILines =(result,coordinates)=>{
            if(result && typeof result === "string"){
                const lines = result.split("\n");
                lines.forEach(line=>{
                    if(line.trim()){
                        handleAPILine(line,coordinates);
                    }
                });
            } else {
                console.error("Input data is not a string");
            }
        };

        /**
         * Write message on the navigation bar
         * @param msg
         */
        const message = (msg)=>{
            const node = document.querySelector("nav .centered-container");
            if(node){
                node.innerHTML = msg;
            } else {
                alert(msg);
            }
        };

        const gameTable = document.querySelector("table");
        if(gameTable){
            gameTable.onclick = function (e){
                if(e.target && e.target instanceof HTMLElement && e.target.classList.contains("empty")){
                    const coordinates = {
                        x:Number.parseInt(e.target.getAttribute("data-x")),
                        y:Number.parseInt(e.target.getAttribute("data-y"))
                    };
                    Request.makeMove(coordinates, (error, result) => {
                        if(!error){
                            if(result && result.startsWith("<!DOCTYPE html>")){
                                console.warn("Invalid HTML content in the result");
                                return;
                            }
                            handleAPILines(result,coordinates);
                        } else {
                            console.error(error);
                        }
                    });
                }else {
                    message("<?php echo getTranslate("occupied") ?>");
                }
            }
        } else {
            console.error("Table is not found in the DOM");
        }

    </script>

    <?php
}

/**
 * @param {string} $callbackMenu
 */
function renderGameScreen($callbackMenu){
    if(!isset($_SESSION['gameStarted']) || !$_SESSION['gameStarted']) {
        if(isset($callbackMenu) && $callbackMenu){
            header("Location: ?menu=".$callbackMenu);
            exit;
        } else {
            header("Location: ?menu=menu");
            exit;
        }
    }

    echo "<div class=\"container\">";
        inGameHeader($_SESSION['current-player']);
        echo generateTableFromMap($_SESSION['map'])."<div class=\"table-hider\" style=\"display: none\"></div>";
    echo "</div>";
    /**
     * Add Client-Side Javascript logic for the game
     */
    tableLogic();
}

