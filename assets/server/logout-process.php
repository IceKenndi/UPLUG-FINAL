<?php

session_start();

unset($_SESSION['seen_global_toasts']);

session_destroy();

header("Location: /../index.php");
exit();
?>