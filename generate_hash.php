<?php
$new_password = 'ashishgupta';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
echo "Your hashed password is: " . $hashed_password;
?>

<!-- UPDATE admins 
SET password = '$2y$10$newHashedPasswordHere' 
WHERE username = 'admin'; -->
