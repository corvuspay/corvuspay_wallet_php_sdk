<?php
require_once('../../vendor/autoload.php');
require_once('../../init.php');
require_once('../../path-data.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Example for cancel page</title>
    <?php include (BASEDIR . 'assets/css/cdn.php');?>
</head>
<body>
<?php
include (BASEDIR . 'assets/js/cdn.php');
require_once('../../examples/navbar.php');
?>
<?php
if ($_POST) {
    echo("Order " . $_POST["order_number"] . " has been cancelled.");
}
?>
</body>
</html>
