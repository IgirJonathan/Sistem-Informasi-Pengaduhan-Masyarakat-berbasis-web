<?php
// Set default timezone for consistent date/time operations
date_default_timezone_set('Asia/Jakarta'); // UTC+7 timezone for Indonesia

$conn = mysqli_connect("localhost", "root", "", "db_apem"); //koneksi ke database db_apem

// Set MySQL session timezone to match PHP timezone
// Asia/Jakarta = UTC+7
mysqli_query($conn, "SET time_zone = '+07:00';");
