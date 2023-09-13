<?php
    require("src/Controller.php");
    require_once("env.php");
    require_once("src/DBConfig.php");

    init();

    error_reporting(E_ERROR | E_PARSE);

    header("Access-Control-Allow-Origin: null");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $reqMethod = $_SERVER["REQUEST_METHOD"];

    $uri = parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH);

    $db = dbInit();

    (new Controller($reqMethod,$db))->process($uri);
?>