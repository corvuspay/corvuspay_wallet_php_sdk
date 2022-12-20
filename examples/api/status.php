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
    <title>Example for checking transaction status</title>
    <?php include(BASEDIR . 'assets/css/cdn.php'); ?>
</head>
<body>
<?php
include(BASEDIR . 'assets/js/cdn.php');
require_once('../../examples/navbar.php');
?>
<?php
$tempClient          = new CorvusPay\CorvusPayClient();
$supportedCurrencies = array_keys(\CorvusPay\Service\CheckoutService::CURRENCY_CODES);

if ($_POST) {
// Check if the uploaded file has (*.p12) extension.
    $fileExtensionAllowed = 'p12';
    $fileName             = $_FILES['certificate']['name'];
    $fileTmpName          = $_FILES['certificate']['tmp_name'];
    $exploded             = explode('.', $fileName);
    $fileExtension        = strtolower(end($exploded));

    if ($fileExtension === $fileExtensionAllowed) {
        // Configuration.
        $fp = fopen($fileTmpName, 'r');
        // Environment parameter is optional, default value is 'test'.
        $params = ['store_id' => $_POST["store_id"], 'secret_key' => $_POST["secret_key"], 'environment' => $_POST["environment"]];
        try {
            $client = new CorvusPay\CorvusPayClient($params);
            $client->setCertificate($fp, $_POST["certificate_password"]);
            $params = [
                'order_number'  => $_POST["order_number"],
                'currency_code' => $_POST["currency_code"]
            ];
            $res    = $client->transaction->status($params);
            require_once('../modal.php');
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

<?php require_once('../../examples/navbar.php'); ?>
<div class="card">
    <div class="p-4">
        Example for checking transaction status
    </div>
    <div class="card-body">
        <form role="form" method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="store_id" class="col-sm-2 col-form-label">Custom shop id</label>
                <input id="store_id" name="store_id" class="text_input col-sm-2"
                       value="<?php if ($_POST && isset($_POST["store_id"])) echo $_POST['store_id'];
                       else echo STORE_ID; ?>" required>
            </div>
            <div class="form-group row">
                <label for="secret_key" class="col-sm-2 col-form-label">Custom shop secret key</label>
                <input id="secret_key" type="password" name="secret_key" class="text_input col-sm-2"
                       value="<?php if ($_POST && isset($_POST["secret_key"])) echo $_POST['secret_key'];
                       else echo SECRET_KEY; ?>"
                       required>
            </div>
            <div class="form-group row">
                <label for="environment" class="col-sm-2 col-form-label">Environment</label>
                <select class="form-control-inline col-sm-2 inline" id="environment"
                        name="environment" required>
                    <option value="prod" <?php if (($_POST && isset($_POST["environment"]) && $_POST["environment"] === "prod") ||
                        ! ($_POST && isset($_POST["environment"]) && $_POST["environment"] === "prod") && ENVIRONMENT === "prod") echo 'selected="selected" '; ?>>
                        Production
                    </option>
                    <option <?php if (( ! $_POST || isset($_POST["environment"]) && $_POST["environment"] === "test") ||
                        ! ( ! $_POST || isset($_POST["environment"]) && $_POST["environment"] === "test") && ENVIRONMENT === "test") echo 'selected="selected" '; ?>
                            value="test">Test
                    </option>
                </select>
            </div>

            <div class="form-group row">
                <label for="order_id" class="col-sm-2 col-form-label">Order number (string):</label>
                <input class="text_input col-sm-2" id="order_id" name="order_number" type="text"
                       value="<?php if ($_POST && isset($_POST["order_number"])) echo $_POST['order_number'];
                       else echo(uniqid()); ?>" required>
            </div>

            <div class="form-group row">
                <label for="certificate_password_id" class="col-sm-2 col-form-label">Certificate password
                    (string):</label>
                <input class="text_input col-sm-2" id="certificate_password_id" name="certificate_password"
                       type="password"
                       value="<?php if ($_POST && isset($_POST["certificate_password"])) echo $_POST['certificate_password'];
                       else echo CERTIFICATE_PASSWORD; ?>" required>
            </div>

            <div class="form-group row">
                <label for="certificate_id" class="col-sm-2 col-form-label">Certificate:</label>
                <input class="form-control-file-inline" id="certificate_id" name="certificate" type="file" required>
            </div>

            <div class="form-group row">
                <label for="currency_code_id" class="col-sm-2 col-form-label">Currency:</label>
                <select class="form-control-inline col-sm-2" id="currency_code_id" name="currency_code">
                    <?php foreach (\CorvusPay\Service\CheckoutService::CURRENCY_CODES as $currency => $code) { ?>
                        <?php if ($_POST && isset($_POST["currency_code"]) && $_POST["currency_code"] === $currency) { ?>
                            <option value="<?php echo $currency ?>"
                                    selected="selected"><?php echo $currency ?></option>
                        <?php } elseif ( ! $_POST && $currency === CURRENCY) { ?>
                            <option value="<?php echo $currency ?>"
                                    selected="selected"><?php echo $currency ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $currency ?>"><?php echo $currency ?></option>>
                        <?php } ?><?php echo PHP_EOL;
                    } ?>
                </select>
            </div>

            <input type="submit" value="Submit" class="btn btn-primary"/>
        </form>
    </div>
</div>
</body>
</html>
