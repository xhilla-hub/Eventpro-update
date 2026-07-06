<?php
include 'backend/config/database.php';
$hash = '$2y$10$A0AS6h6ZaDuKWb2HY/hu3ujkxdKVXfLJmrJXl3twtcOFKwvC8Ic4e';
$pdo->exec("UPDATE users SET password = '$hash' WHERE email = 'admin@gmail.com'");
echo "Fixed admin password.\n";
