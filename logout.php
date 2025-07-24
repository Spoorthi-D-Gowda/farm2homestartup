<?php
session_start();
session_destroy();
header("Location: index2.php");
exit();
?>
<?php
session_start();
session_destroy();
header("Location: login.php?message=You have been logged out.");
exit;
?>