<?php
session_start();
unset($_SESSION['user_id_task_app']);
unset($_SESSION['username_task_app']);
unset($_SESSION['email_for_otp_verification_task_app']);
unset($_SESSION['success_message_task_app']);
unset($_SESSION['error_message_task_app']);
session_destroy(); 
header("Location: welcome.php"); 
exit;
?>