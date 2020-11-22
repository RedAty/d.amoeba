<?php
/**
 * In-Game Logic
 *
 * @file    methods.php
 * @author  Attila Reterics
 * @license GPL-3
 * @url     https://github.com/RedAty/d.amoeba
 * @date    2020. 11. 20.
 * @email   reterics.attila@gmail.com
 */


/**
 * @param string[] $array
 */
function printArray($array){
    foreach ($array as $item) {
        echo $item.", ";
    }
}


/**
 * Generate a 2D Multidimensional Array
 * with the given row and column count
 * @param int $row
 * @param int $column
 * @return array
 */
function generateMap($row, $column){
    if(!isset($row) || $row == 0){
        $row = 3;
    }
    if(!isset($column) || $column == 0){
        $column = 3;
    }
    $map = array();

    for ($i = 0; $i < $row; $i++) {
        array_push($map, array());
        for ($j = 0; $j < $column; $j++) {
            $map[$i][$j] = 0;
        }
    }
    return $map;
}

/**
 * Generate a HTML table from PHP 2D Multidimensional Array
 * @param array[] $multiArray
 * @return string
 */
function generateTableFromMap($multiArray){
    $size = count($multiArray);
    $table = "<table><tbody>";
    for ($i = 0; $i < $size; $i++) {
        if(isset($multiArray[$i])){
            $table .= "<tr>";
            $columnSize = count($multiArray[$i]);
            for ($j = 0; $j < $columnSize; $j++) {
                if (isset($multiArray[$i][$j])){
                    if($multiArray[$i][$j]){
                        $table .= "<td class='player-".$multiArray[$i][$j]."' data-x='".$i."' data-y='".$j."'></td>";
                    } else{
                        $table .= "<td class='empty' data-x='".$i."' data-y='".$j."'></td>";
                    }

                }
                $multiArray[$i][$j] = 0;
            }
            $table .= "</tr>";
        }
    }
    $table .= "</tbody></table>";
    return $table;
}




/**
 * Recursive function to search how many field we have in a specific direction for a given type
 * @param array[] $multiArray
 * @param int $row
 * @param int $column
 * @param int $rowDirection
 * @param int $columnDirection
 * @return int
 */
function searchTheLine($multiArray, $row, $column, $rowDirection, $columnDirection) {
    $type = $multiArray[$row][$column];
    $currentRow = $row + $rowDirection;
    $currentColumn = $column + $columnDirection;

    if(isset($multiArray[$currentRow][$currentColumn])) {
        if($type === $multiArray[$currentRow][$currentColumn]) {
            return 1 + searchTheLine($multiArray, $currentRow, $currentColumn, $rowDirection, $columnDirection);
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

/**
 * @param array[] $multiArray
 * @param int $row
 * @param int $column
 * @return array
 */
function getLineLengths($multiArray, $row, $column){
    $verticalUpCount = searchTheLine($multiArray,$row,$column, -1,0);
    $verticalDownCount = searchTheLine($multiArray,$row,$column, 1,0);

    $horizontalUpCount = searchTheLine($multiArray,$row,$column, 0,-1);
    $horizontalDownCount = searchTheLine($multiArray,$row,$column, 0,1);

    $diagonalTopRight = searchTheLine($multiArray,$row,$column, -1,1);
    $diagonalBottomLeft = searchTheLine($multiArray,$row,$column, 1,-1);

    $diagonalTopLeft = searchTheLine($multiArray,$row,$column, -1,-1);
    $diagonalBottomRight = searchTheLine($multiArray,$row,$column, 1,1);

    $verticalCount = 1 + $verticalDownCount + $verticalUpCount;
    $horizontalCount = 1 + $horizontalDownCount + $horizontalUpCount;
    $diagonalRightCount = 1 + $diagonalTopRight + $diagonalBottomLeft;
    $diagonalLeftCount = 1 + $diagonalTopLeft + $diagonalBottomRight;

    return array(
        "vertical"=>$verticalCount,
        "horizontal"=>$horizontalCount,
        "diagRight"=>$diagonalRightCount,
        "diagLeft"=>$diagonalLeftCount,
        "max"=>max($verticalCount,$horizontalCount,$diagonalRightCount,$diagonalLeftCount)
    );
}

/**
 * @param array[] $multiArray
 * @param int $row
 * @param int $column
 * @return int
 */
function getMaximumLineLength($multiArray, $row, $column) {
    $lineLengths = getLineLengths($multiArray, $row, $column);
    return $lineLengths["max"];
}


function isEmptyField($multiArray,$x,$y){
    return isset($multiArray[$x][$y]) && $multiArray[$x][$y] == 0;
}

/**
 * @param array $multiArray
 * @param array $coordinates
 * @param int $field
 * @return array
 */
function attackCoordinate($multiArray,$coordinates, $field){
    $attackSuccessful = false;
    if(isEmptyField($multiArray,$coordinates["x"],$coordinates["y"])) {
        $multiArray[$coordinates["x"]][$coordinates["y"]] = $field;
        $attackSuccessful = true;
    }
    return array(
        "status"=>$attackSuccessful,
        "map"=>$multiArray
    );
}

/**
 * @param array[] $multiArray
 * @param $coordinates
 * @param $directions
 * @param $field
 * @return array
 */
function attackCoordinates($multiArray, $coordinates, $directions, $field){
    $attackSuccessful = false;
    $attackedField = null;
    foreach($directions as $directionPair){
        if(!$attackSuccessful){
            $lastField = findLastInDirection($multiArray, $coordinates["x"], $coordinates["y"], $directionPair[0], $directionPair[1]);
            $result = attackCoordinate($multiArray, $lastField, $field);
            if($result["status"] === true){
                $attackSuccessful = true;
                $multiArray = $result["map"];
                $attackedField = $lastField;
            }
        } else {
            break;
        }
    }
    return array(
        "status"=>$attackSuccessful,
        "map"=>$multiArray,
        "field"=>$attackedField
    );
}

/**
 * @param array[] $multiArray
 * @param int $x
 * @param int $y
 * @param $xDirection
 * @param $yDirection
 * @return array
 */
function findLastInDirection($multiArray,$x,$y,$xDirection,$yDirection) {
    $type = $multiArray[$x][$y];

    while(isset($multiArray[$x][$y]) && $type === $multiArray[$x][$y]) {
        $x = $x + $xDirection;
        $y = $y + $yDirection;
    }

    return array(
        "x" => $x,
        "y" => $y
    );

}

/**
 * Find connections to make the user sad, when tries to tick the AI
 * @param array[] $multiArray
 * @param int $x
 * @param int $y
 * @return array
 */
function findAround($multiArray, $x, $y) {
    $pairs = [
        [-2,0],
        [-2,2],
        [0,2],
        [2,2],
        [2,0],
        [2,-2],
        [0,-2],
        [-2,-2],
    ];

    $type = $multiArray[$x][$y];

    $result = array(
        "status" => false,
        "x"=>0,
        "y"=>0
    );
    foreach ($pairs as $pair) {
        $neededX = $x + $pair[0];
        $neededY = $y + $pair[1];
        if(isset($multiArray[$neededX][$neededY]) && $multiArray[$neededX][$neededY] === $type) {

            $result["x"] = ($neededX - $x != 0) ? (($neededX - $x) / 2) + $x : $x;
            $result["y"] = ($neededY - $y != 0) ? (($neededY - $y) / 2) + $y : $y;

            if($multiArray[$result["x"]][$result["y"]] === 0){
                $result["status"] = true;
                break;
            }
        }
    }
    return $result;
}

/**
 * @param array[] $multiArray
 * @param $coordinates
 * @param $aiPlayer
 * @return mixed
 */
function aiTacticV2($multiArray, $coordinates, $aiPlayer) {

    $lineLengths = getLineLengths($multiArray, $coordinates["x"], $coordinates["y"]);

    $attackSuccessful = false;
    $attackedX = 0;
    $attackedY = 0;

    if($lineLengths["max"] < 3) {

        $result = findAround($multiArray, $coordinates["x"], $coordinates["y"]);
        if($result["status"] === true){
            $multiArray[$result["x"]][$result["y"]] = $aiPlayer;
            $attackedX = $result["x"];
            $attackedY = $result["y"];
            $attackSuccessful = true;
        }
    }

    $ones = [1, -1];
    shuffle($ones);
    // We check where we have the longest line, because we ATTACK THERE!
    if(!$attackSuccessful && $lineLengths["max"] === $lineLengths["vertical"]) {
        $result  = attackCoordinates($multiArray, $coordinates, [ [$ones[0], 0] , [$ones[1], 0] ], $aiPlayer);
        if($result["status"] === true){
            $attackSuccessful = true;
            $multiArray = $result["map"];
            $attackedX = $result["field"]["x"];
            $attackedY = $result["field"]["y"];
        }
    }
    if(!$attackSuccessful && $lineLengths["max"] === $lineLengths["horizontal"]){
        $result  = attackCoordinates($multiArray, $coordinates, [ [0, $ones[0]] , [0, $ones[1]] ], $aiPlayer);
        if($result["status"] === true){
            $attackSuccessful = true;
            $multiArray = $result["map"];
            $attackedX = $result["field"]["x"];
            $attackedY = $result["field"]["y"];
        }
    }
    if(!$attackSuccessful && $lineLengths["max"] === $lineLengths["diagRight"]){
        $result  = attackCoordinates($multiArray, $coordinates, [ [-1,1] , [1, -1] ], $aiPlayer);
        if($result["status"] === true){
            $attackSuccessful = true;
            $multiArray = $result["map"];
            $attackedX = $result["field"]["x"];
            $attackedY = $result["field"]["y"];
        }
    }
    if(!$attackSuccessful && $lineLengths["max"] === $lineLengths["diagLeft"]){
        $result  = attackCoordinates($multiArray, $coordinates, [ [1,1] , [-1, -1] ], $aiPlayer);
        if($result["status"] === true){
            $attackSuccessful = true;
            $multiArray = $result["map"];
            $attackedX = $result["field"]["x"];
            $attackedY = $result["field"]["y"];
        }
    }

    if(!$attackSuccessful){
        $pairs = [
            [-2,0],
            [-2,2],
            [0,2],
            [2,2],
            [2,0],
            [2,-2],
            [0,-2],
            [-2,-2],
        ];
        //Go around then
        foreach ($pairs as $pair) {
            $neededX = $coordinates["x"] + $pair[0];
            $neededY = $coordinates["y"] + $pair[1];
            if(!$attackSuccessful && isEmptyField($multiArray, $neededX, $neededY)) {
                $multiArray[$neededX][$neededY] = $aiPlayer;
                $attackSuccessful = true;
                $attackedX = $neededX;
                $attackedY = $neededY;
            }
        }
    }

    if(!$attackSuccessful){
        $rows = count($multiArray);
        $columns = count($multiArray[0]);

        for($i = 0; $i < $rows; $i++){
            for($j = 0; $j < $columns; $j++){
                if($multiArray[$i][$j] == 0){
                    $multiArray[$i][$j] = $aiPlayer;
                    $attackedX = $i;
                    $attackedY = $j;
                    $i = $rows;
                    //$j = $columns;
                    break;
                }
            }
        }
    }

    return array(
        "map" => $multiArray,
        "x" => $attackedX,
        "y" => $attackedY
    );
}