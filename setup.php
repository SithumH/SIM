<?php
// Database Setup Checker
echo "<h2>Database Setup Checker</h2>";

// Check MySQL connection
$conn = @new mysqli('localhost', 'root', '', '', 3307);

if ($conn->connect_error) {
    die("<p style='color:red'>❌ MySQL is not running! Start MySQL from XAMPP Control Panel.</p>");
}

echo "<p style='color:green'>✅ MySQL is running on port 3307!</p>";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE 'sis_db'");
if ($result->num_rows > 0) {
    echo "<p style='color:green'>✅ Database 'sis_db' exists!</p>";
} else {
    echo "<p style='color:orange'>⚠️ Database 'sis_db' not found. Creating...</p>";
    
    // Create database
    if ($conn->query("CREATE DATABASE sis_db")) {
        echo "<p style='color:green'>✅ Database created!</p>";
    } else {
        die("<p style='color:red'>❌ Failed to create database: " . $conn->error . "</p>");
    }
}

$conn->select_db('sis_db');

// Check if tables exist
$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "<p style='color:green'>✅ Tables exist (" . $result->num_rows . " tables)</p>";
    echo "<p><a href='login.php' style='background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Login</a></p>";
} else {
    echo "<p style='color:orange'>⚠️ No tables found. Please import 'database_schema.sql' from phpMyAdmin</p>";
    echo "<p><a href='http://localhost/phpmyadmin' target='_blank' style='background:#2196F3;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Open phpMyAdmin</a></p>";
}

$conn->close();
?>
