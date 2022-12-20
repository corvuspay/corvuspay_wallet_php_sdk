<?php
session_id("corvuspay");
session_start();
require_once('../../vendor/autoload.php');
require_once('../../init.php');
require_once('../../path-data.php');
require_once('../examples-data.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Example for success page</title>
    <?php include (BASEDIR . 'assets/css/cdn.php');?>
</head>
<body>
<?php
include (BASEDIR . 'assets/js/cdn.php');
require_once('../../examples/navbar.php');
?>
<?php
$config = $_SESSION["config"];
$params = $_SESSION["params"];
$client = new CorvusPay\CorvusPayClient($config);
$status = "";


//Preview the response from CorvusPay.
if ($_POST && isset($_POST["approval_code"])) {
    echo('<div class="row"><div class="col-12 m-4"><h5>Response from server:</h5><table class="table">');
    foreach ($_POST as $key => $value) {
        echo('<tr class="d-flex"><td class="col-4">' . $key . '</td><td class="col-8">' . $value . '</td></tr>');
    }
    echo('</table></div></div>');

    //You can check if the data sent to the payment platform is correct by checking if the returned signature is valid.
    $res = $client->validate->signature($_POST);

    echo('<div class="row"><div class="col-12 m-4">');
    if ($res) echo('The signature is <span class="badge badge-success">valid</span>');
    else echo('The signature is <span class="badge badge-danger">invalid</span>');
    echo('</div></div>');
}

//Check transaction status
if ($_POST && isset($_POST["certificate_password"])) {
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
        $params_for_status = ['store_id' => $config["store_id"], 'secret_key' => $config["secret_key"], 'environment' => $config["environment"]];
        try {
            $client            = new CorvusPay\CorvusPayClient($params_for_status);
            $config["certificate_password"] = $_POST["certificate_password"];
            $client->setCertificate($fp, $config["certificate_password"]);
            $params_for_status = [
                'order_number'  => $params["order_number"],
                'currency_code' => $params["currency"]
            ];
            $response_xml = $client->transaction->status($params_for_status);
            $response     = [];
            $response     = new SimpleXMLElement($response_xml);

            echo('<div class="row"><div class="col-12 m-4"><h5>Transaction status:</h5><table class="table">');
            foreach ($response as $key => $value) {
                echo('<tr class="d-flex"><td class="col-4">' . $key . '</td><td class="col-8">' . $value . '</td></tr>');
            }
            echo('</table></div></div>');

            $status = (string)$response->{'status'}[0];

            //Save account_id if exists.
            if ($response->{'account-id'}) {
                $params["account_id"] = (string)$response->{'account-id'}[0];
            }

        } catch (Exception $e) {
            echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $e->getMessage() . '
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
        }
    } else echo("Only certificates in PKCS#12 (*.p12) format are supported.");
}
?>

<!--Check status-->
<?php if ($status === "") { ?>
    <div class="card">
        <div class="p-4">
            Check transaction status
        </div>
        <div class="card-body">
            <form role="form" method="post" enctype="multipart/form-data">

                <div class="form-group row">
                    <label for="certificate_password_id" class="col-sm-2 col-form-label">Certificate password
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
    </div><?php echo PHP_EOL;
} ?>

<!--Next actions-->
<div class="row">
    <div class="col-12 m-4">
        <?php if ($status !== "") { ?>
            <p>Next actions</p><?php echo PHP_EOL;
        } ?>

        <div class="btn-group">
            <?php if ($status === "pre_authorized") { ?>
                <form action="../api/cancel.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $config['store_id'] ?>">
                <input type="hidden" name="secret_key" value="<?php echo $config['secret_key'] ?>">
                <input type="hidden" name="environment" value="<?php echo $config['environment'] ?>">
                <input type="hidden" name="order_number" value="<?php echo $params['order_number'] ?>">
                <input type="hidden" name="certificate_password" value="<?php echo $config['certificate_password'] ?>">
                <input type="hidden" name="predefined" value="">
                <button type="submit" class="btn btn-link">Cancel</button>
                </form><?php echo PHP_EOL;
            } ?>

            <?php if ($status === "pre_authorized") { ?>
                <form action="../api/complete.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $config['store_id'] ?>">
                <input type="hidden" name="secret_key" value="<?php echo $config['secret_key'] ?>">
                <input type="hidden" name="environment" value="<?php echo $config['environment'] ?>">
                <input type="hidden" name="order_number" value="<?php echo $params['order_number'] ?>">
                <input type="hidden" name="certificate_password" value="<?php echo $config['certificate_password'] ?>">
                <input type="hidden" name="predefined" value="">
                <?php
                if (isset($params["account_id"]))
                    echo('<input type="hidden" name="account_id" value="' . $params["account_id"] . '">') ?>
                <button type="submit" class="btn btn-link">Complete</button>
                </form><?php echo PHP_EOL;
            } ?>

            <?php if ($status === "pre_authorized") { ?>
                <form action="../api/partial-complete.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $config['store_id'] ?>">
                <input type="hidden" name="secret_key" value="<?php echo $config['secret_key'] ?>">
                <input type="hidden" name="environment" value="<?php echo $config['environment'] ?>">
                <input type="hidden" name="order_number" value="<?php echo $params['order_number'] ?>">
                <input type="hidden" name="certificate_password" value="<?php echo $config['certificate_password'] ?>">
                <input type="hidden" name="predefined" value="">
                <?php
                if (isset($params["account_id"]))
                    echo('<input type="hidden" name="account_id" value="' . $params["account_id"] . '">') ?>
                <input type="hidden" name="currency" value="<?php echo $params['currency'] ?>">
                <button type="submit" class="btn btn-link">Partial complete</button>
                </form><?php echo PHP_EOL;
            } ?>

            <?php if ($status === "authorized") { ?>
                <form action="../api/refund.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $config['store_id'] ?>">
                <input type="hidden" name="secret_key" value="<?php echo $config['secret_key'] ?>">
                <input type="hidden" name="environment" value="<?php echo $config['environment'] ?>">
                <input type="hidden" name="order_number" value="<?php echo $params['order_number'] ?>">
                <input type="hidden" name="certificate_password" value="<?php echo $config['certificate_password'] ?>">
                <input type="hidden" name="predefined" value="">
                <?php
                if (isset($params["account_id"]))
                    echo('<input type="hidden" name="account_id" value="' . $params["account_id"] . '">') ?>
                <button type="submit" class="btn btn-link">Refund</button>
                </form><?php echo PHP_EOL;
            } ?>

            <?php if ($status === "authorized") { ?>
                <form action="../api/partial-refund.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $config['store_id'] ?>">
                <input type="hidden" name="secret_key" value="<?php echo $config['secret_key'] ?>">
                <input type="hidden" name="environment" value="<?php echo $config['environment'] ?>">
                <input type="hidden" name="order_number" value="<?php echo $params['order_number'] ?>">
                <input type="hidden" name="certificate_password" value="<?php echo $config['certificate_password'] ?>">
                <input type="hidden" name="predefined" value="">
                <?php
                if (isset($params["account_id"]))
                    echo('<input type="hidden" name="account_id" value="' . $params["account_id"] . '">') ?>
                <input type="hidden" name="currency" value="<?php echo $params['currency'] ?>">
                <button type="submit" class="btn btn-link">Partial refund</button>
                </form><?php echo PHP_EOL;
            } ?>

            <?php if ($status === "authorized" && array_key_exists("account_id", $params)) { ?>
                <form action="../api/next-sub-payment.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $config['store_id'] ?>">
                <input type="hidden" name="secret_key" value="<?php echo $config['secret_key'] ?>">
                <input type="hidden" name="environment" value="<?php echo $config['environment'] ?>">
                <input type="hidden" name="order_number" value="<?php echo $params['order_number'] ?>">
                <input type="hidden" name="certificate_password" value="<?php echo $config['certificate_password'] ?>">
                <input type="hidden" name="predefined" value="">
                <?php
                if (isset($params["account_id"]))
                    echo('<input type="hidden" name="account_id" value="' . $params["account_id"] . '">') ?>
                <input type="hidden" name="currency" value="<?php echo $params['currency'] ?>">
                <button type="submit" class="btn btn-link">Next subscription payment</button>
                </form><?php echo PHP_EOL;
            } ?>
        </div>
    </div>
</div>
</body>
</html>
