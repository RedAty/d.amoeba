<?php
/**
 * Manage Languages
 *
 * @file    locales.php
 * @author  Attila Reterics
 * @license GPL-3
 * @url     https://github.com/RedAty/d.amoeba
 * @date    2020. 11. 20.
 * @email   reterics.attila@gmail.com
 */

$LANG = array();

/**
 * @param {array} $langArray
 * @param {string} $langCode
 */
function addLanguage($langArray, $langCode){
    global $LANG;
    if(isset($langArray) && isset($langCode)){
        $LANG[$langCode] = $langArray;
    }
}

/**
 * @param {string} $text
 * @return string
 */
function getTranslate($text){
    global $LANG;
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
    if(!isset($text) || !isset($LANG[$lang]) || !isset($LANG[$lang][$text])) {
        return "";
    }
    return $LANG[$lang][$text];
}

foreach (glob("./lib/locales/*.php") as $filename) {
    require_once $filename;
}