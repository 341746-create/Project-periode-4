<?php
$host = "localhost";
$user = "root";       // Standard username for XAMPP
$pass = "";           // Standard password for XAMPP is empty
$dbname = "theater_db"; // Replace this with your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
