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
    <title>Example for charging the next subscription payment</title>
    <?php include(BASEDIR . 'assets/css/cdn.php'); ?>
</head>
<body>

<?php
$status = "";

if ($_POST && isset($_POST["predefined"])) {
    $config = $_POST;
}
if ($_POST && ! isset($_POST["predefined"])) {
    $config = $_POST;
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
        $config_for_client = ['store_id' => $_POST["store_id"], 'secret_key' => $_POST["secret_key"], 'environment' => $_POST["environment"]];
        try {
            $client = new CorvusPay\CorvusPayClient($config_for_client);
            $client->setCertificate($fp, $_POST["certificate_password"]);
            $params = [
                'order_number' => $_POST["order_number"],
                'account_id'   => $_POST["account_id"]
            ];

	        if ( isset( $_POST['have_new_amount'] ) ) {
		        $params["new_amount"] = $_POST["new_amount"];
		        $params["currency"]   = $_POST["currency"];
	        }
            //True if payment is successful or server output in xml format if error occurred.
            $res = $client->subscription->pay($params);
            //alert
            if ($res === true) {
                $status = "paid";
                echo('<div class="alert alert-success alert-dismissible fade show" role="alert">
Payment has been successfully processed!
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
            } else echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">
Something went wrong!
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
        Example for charging the next subscription payment
    </div>
    <div class="card-body">
        <form role="form" method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="store_id" class="col-sm-2 col-form-label">Custom shop id</label>
                <input id="store_id" name="store_id" class="text_input col-sm-2"
                       value="<?php if (isset($config) && isset($config["store_id"])) echo $config['store_id'];
                       else echo STORE_ID; ?>" required>
            </div>
            <div class="form-group row">
                <label for="secret_key" class="col-sm-2 col-form-label">Custom shop secret key</label>
                <input id="secret_key" type="password" name="secret_key" class="text_input col-sm-2"
                       value="<?php if (isset($config) && isset($config["secret_key"])) echo $config['secret_key'];
                       else echo SECRET_KEY; ?>" required>
            </div>
            <div class="form-group row">
                <label for="environment" class="col-sm-2 col-form-label">Environment</label>
                <select class="form-control-inline col-sm-2 inline" id="environment"
                        name="environment" required>
                    <option value="prod" <?php if ((isset($config) && isset($config["environment"]) && $config["environment"] === "prod") ||
                        !(isset($config) && isset($config["environment"]) && $config["environment"] === "prod") && ENVIRONMENT === "prod") echo 'selected="selected" '; ?>>
                        Production
                    </option>
                    <option value="test" <?php if ((isset($config) && isset($config["environment"]) && $config["environment"] === "test") ||
                        !(isset($config) && isset($config["environment"]) && $config["environment"] === "test") && ENVIRONMENT === "test") echo 'selected="selected" '; ?>>
                        Test
                    </option>
                </select>
            </div>

            <div class="form-group row">
                <label for="order_id" class="col-sm-2 col-form-label">Order number (string):</label>
                <input class="text_input col-sm-2" id="order_id" name="order_number" type="text"
                       value="<?php if ($_POST && ! isset($_POST["predefined"]) && isset($config) && isset($config["order_number"])) echo $config['order_number']; ?>"
                       required>
            </div>

            <div class="form-group row">
                <label for="certificate_password_id" class="col-sm-2 col-form-label">Certificate password
                    (string):</label>
                <input class="text_input col-sm-2" id="certificate_password_id" name="certificate_password"
                       type="password"
                       value="<?php if (isset($config) && isset($config["certificate_password"])) echo $config['certificate_password'];
                       else echo CERTIFICATE_PASSWORD; ?>" required>
            </div>

            <div class="form-group row">
                <label for="certificate_id" class="col-sm-2 col-form-label">Certificate:</label>
                <input class="form-control-file-inline" id="certificate_id" name="certificate" type="file" required>
            </div>

            <div class="form-group row">
                <label for="account_id" class="col-sm-2 col-form-label">Account ID (string):</label>
                <input class="text_input col-sm-2" id="account_id" name="account_id"
                       value="<?php if (isset($config) && isset($config["account_id"])) echo $config['account_id'];
                       else echo ACCOUNT_ID; ?>" required>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="new_amount_id"
                           name="have_new_amount" <?php if (isset($config) && isset($config["have_new_amount"])) echo "checked"; ?>>
                    <label class="form-check-label" for="new_amount_id"> Different amount (string):</label>
                </div>
            </div>

            <div class="form-group row">
                <label for="new_amount_id" class="col-sm-2 col-form-label">New amount (string):</label>
                <input id="new_amount_id" name="new_amount" class="text_input col-sm-2"
                       value="<?php if (isset($config) && isset($config["new_amount"])) echo $config['new_amount'];
                       else echo NEW_AMOUNT_FOR_NEXT_SUB; ?>">
            </div>

            <div class="form-group row">
                <label for="currency_code_id" class="col-sm-2 col-form-label">Currency:</label>
                <select class="form-control-inline col-sm-2" id="currency_code_id" name="currency">
			        <?php foreach ( \CorvusPay\Service\CheckoutService::CURRENCY_CODES as $currency => $code ) { ?>
				        <?php if ( isset( $config ) && isset( $config["currency"] ) && $config["currency"] === $currency ) { ?>
                            <option value="<?php echo $currency ?>"
                                    selected="selected"><?php echo $currency ?></option>
				        <?php } elseif ( ! $config && $currency === CURRENCY ) { ?>
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

        <!--Next actions-->
        <div class="row">
            <div class="col-12 m-4">
                <?php if ($status === "paid") { ?>
                    <p>Next actions</p><?php echo PHP_EOL;
                } ?>

                <div class="btn-group">
                    <?php if ($status === "paid") { ?>
                        <form action="refund.php" method="POST">
                        <input type="hidden" name="store_id"
                               value="<?php if (isset($config) && isset($config['store_id'])) echo $config['store_id'] ?>">
                        <input type="hidden" name="secret_key"
                               value="<?php if (isset($config) && isset($config['secret_key'])) echo $config['secret_key'] ?>">
                        <input type="hidden" name="environment"
                               value="<?php if (isset($config) && isset($config['environment'])) echo $config['environment'] ?>">
                        <input type="hidden" name="order_number"
                               value="<?php if (isset($params) && isset($params['order_number'])) echo $params['order_number'] ?>">
                        <input type="hidden" name="certificate_password"
                               value="<?php if (isset($config) && isset($config['certificate_password'])) echo $config['certificate_password'] ?>">
                        <input type="hidden" name="predefined" value="">
                        <input type="hidden" name="account_id"
                               value="<?php if (isset($params) && isset($params['account_id'])) echo $params['account_id'] ?>">
                        <button type="submit" class="btn btn-link">Refund</button>
                        </form><?php echo PHP_EOL;
                    } ?>

                    <?php if ($status === "paid") { ?>
                        <form action="partial-refund.php" method="POST">
                        <input type="hidden" name="store_id"
                               value="<?php if (isset($config) && isset($config['store_id'])) echo $config['store_id'] ?>">
                        <input type="hidden" name="secret_key"
                               value="<?php if (isset($config) && isset($config['secret_key'])) echo $config['secret_key'] ?>">
                        <input type="hidden" name="environment"
                               value="<?php if (isset($config) && isset($config['environment'])) echo $config['environment'] ?>">
                        <input type="hidden" name="order_number"
                               value="<?php if (isset($params) && isset($params['order_number'])) echo $params['order_number'] ?>">
                        <input type="hidden" name="certificate_password"
                               value="<?php if (isset($config) && isset($config['certificate_password'])) echo $config['certificate_password'] ?>">
                        <input type="hidden" name="predefined" value="">
                        <input type="hidden" name="account_id"
                               value="<?php if (isset($params) && isset($params['account_id'])) echo $params['account_id'] ?>">
                        <input type="hidden" name="currency"
                               value="<?php if (isset($params) && isset($params['currency'])) echo $params['currency'] ?>">
                        <button type="submit" class="btn btn-link">Partially refund</button>
                        </form><?php echo PHP_EOL;
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
