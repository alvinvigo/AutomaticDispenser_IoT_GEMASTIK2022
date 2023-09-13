<?php
    function dbInit() {
        $dbHost=getenv("DB_HOST");  
        $dbName=getenv("DB_NAME");  
        $dbUser=getenv("DB_USER");  
        $dbPassword=getenv("DB_PASSWORD");
        try {
            $db = new PDO("mysql:host=$dbHost;dbname=$dbName",$dbUser,$dbPassword);
            return $db;
        } catch(Exception $e) {
            echo "connection failed" . $e->getMessage();
        }
    }
?>