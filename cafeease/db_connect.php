<?php

	$host = 'localhost';
	$db   = 'cafe';  
	$user = 'root';
	$pass = 'Ananam@03';
	$charset = 'utf8mb4';

	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	$options = [
		PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES  => false,
	];

	$conn = null;
	$db_error = null;

	try {
		$conn = new PDO($dsn, $user, $pass, $options);} 
	catch (\PDOException $e) {
		$db_error = '<p style="color: red; background-color: #ffe0e0; padding: 10px; border: 1px solid red;">Database Connection Error: ' . htmlspecialchars($e->getMessage()) . '</p>';}
?>