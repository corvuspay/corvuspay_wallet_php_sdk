<?php
require_once('../../vendor/autoload.php');
require_once('../../init.php');
require_once('../../path-data.php');
require_once('../examples-data.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Example for refunding a transaction with logger</title>
    <?php include(BASEDIR . 'assets/css/cdn.php'); ?>
</head>
<body>

<?php

if ($_POST) {
    // To enable logging, create logger that support PSR-3 interface. For example Monolog logger.
    $logger = new Monolog\Logger('Test');
    $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/app.log', Monolog\Logger::DEBUG));

    // Check if the uploaded file has (*.p12) extension.
    $fileExtensionAllowed = 'p12';
    $fileName             = $_FILES['certificate']['name'];
    $fileTmpName          = $_FILES['certificate']['tmp_name'];
    $exploded             = explode('.', $fileName);
    $fileExtension        = strtolower(end($exploded));

    if ($fileExtension === $fileExtensionAllowed) {
        // Configuration.
        $fp = fopen($fileTmpName, 'r');

        $params = [
            'store_id'    => $_POST["store_id"],
            'secret_key'  => $_POST["secret_key"],
            'environment' => $_POST["environment"],
            'logger'      => $logger];
        try {
            $client = new CorvusPay\CorvusPayClient($params);
            $client->setCertificate($fp, $_POST["certificate_password"]);
            $params = [
                'order_number' => $_POST["order_number"]
            ];

            $res = $client->transaction->refund($params);
            //alert
            if ($res)
                echo('<div class="alert alert-success alert-dismissible fade show" role="alert">
Successfully cancelled the transaction!
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
            else echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">
Something went wrong! Check app.log
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
        } catch (Exception $e) {
            echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $e->getMessage() . '
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
        }
    } else echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">
Only certificates in PKCS#12 (*.p12) format are supported.
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
}
?>
<?php
include(BASEDIR . 'assets/js/cdn.php');
require_once('../../examples/navbar.php');
?>
<div class="card">
    <div class="p-4">
        Example for refunding a transaction with logger
    </div>
    <div class="card-body">
        <form role="form" method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="store_id" class="col-sm-2 col-form-label">Custom shop id</label>
                <input id="store_id" name="store_id" class="text_input col-sm-2" value="<?php echo STORE_ID ?>" required>
            </div>
            <div class="form-group row">
                <label for="secret_key" class="col-sm-2 col-form-label">Custom shop secret key</label>
                <input id="secret_key" type="password" name="secret_key" class="text_input col-sm-2"
                       value="<?php echo SECRET_KEY ?>"
                       required>
            </div>
            <div class="form-group row">
                <label for="environment" class="col-sm-2 col-form-label">Environment</label>
                <select class="form-control-inline col-sm-2 inline" id="environment"
                        name="environment" required>
                    <option value="prod">Production</option>
                    <option selected="selected" value="test">Test</option>
                </select>
            </div>

            <div class="form-group row">
                <label for="order_id" class="col-sm-2 col-form-label">Order number (string):</label>
                <input class="text_input col-sm-2" id="order_id" name="order_number" type="text"
                       value="<?php echo(uniqid()) ?>" required>
            </div>

            <div class="form-group row">
                <label for="certificate_password_id" class="col-sm-2 col-form-label">Enter incorrect password
                    (string):</label>
                <input class="text_input col-sm-2" id="certificate_password_id" name="certificate_password"
                       type="password"
                       value="<?php echo CERTIFICATE_PASSWORD ?>" required>
            </div>

            <div class="form-group row">
                <label for="certificate_id" class="col-sm-2 col-form-label">Certificate:</label>
                <input class="form-control-file-inline" id="certificate_id" name="certificate" type="file" required>
            </div>

            <input type="submit" value="Submit" class="btn btn-primary"/>
        </form>
    </div>
</div>
</body>
</html>
