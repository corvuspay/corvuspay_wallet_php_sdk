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
    <title>Example for basic checkout</title>
    <?php include(BASEDIR . 'assets/css/cdn.php'); ?>
</head>
<body>

<?php

if ($_POST) {
    // Configuration.
    // Environment parameter is optional, default value is 'test'.
    $params = ['store_id' => $_POST["store_id"], 'secret_key' => $_POST["secret_key"], 'environment' => $_POST["environment"]];
    $client = new CorvusPay\CorvusPayClient($params);

    $data_to_save = array();
    //Save config parameters in order to use them in success page.
    $data_to_save["config"] = $params;
    $_SESSION["config"]     = $params;

    //Checkout required fields.
    $params = [
        'order_number'     => $_POST["order_number"],
        'language'         => $_POST["language"],
        'currency'         => $_POST["currency"],
        'amount'           => $_POST["amount"],
        'cart'             => $_POST["cart"],
        'require_complete' => $_POST["require_complete"]
    ];

    $data_to_save["params"] = $params;
    $_SESSION["params"]     = $params;

    //Second parameter is the name of the redirect button, if the value is 'auto' then redirects automatically.
    try {
        $client->checkout->create($params, 'auto');
    } catch (Exception $e) {
        echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $e->getMessage() . '
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>');
    }
}
?>

<?php
include(BASEDIR . 'assets/js/cdn.php');
require_once('../../examples/navbar.php');
?>
<div class="card">
    <div class="p-4">
        CorvusPay basic checkout
    </div>
    <div class="card-body">
        <form role="form" method="post">
            <div class="form-group row">
                <label for="store_id" class="col-sm-2 col-form-label">Custom shop id:</label>
                <input class="text_input col-sm-2" id="store_id" name="store_id"
                       value="<?php if (isset($data_to_save) && isset($data_to_save["config"]["store_id"])) echo $data_to_save["config"]['store_id'];
                       else echo STORE_ID; ?>" required>
            </div>
            <div class="form-group row">
                <label for="secret_key" class="col-sm-2 col-form-label">Custom shop secret key:</label>
                <input class="text_input col-sm-2" id="secret_key" type="password" name="secret_key"
                       value="<?php if (isset($data_to_save) && isset($data_to_save["config"]["secret_key"])) echo $data_to_save["config"]['secret_key'];
                       else echo SECRET_KEY; ?>"
                       required>
            </div>
            <div class="form-group row">
                <label for="environment" class="col-sm-2 col-form-label">Environment:</label>
                <select class="form-control-inline col-sm-2 inline" id="environment"
                        name="environment" required>
                    <option value="prod" <?php if ((isset($data_to_save) && isset($data_to_save["config"]["environment"]) && $data_to_save["config"]["environment"] === "prod") ||
                        !(isset($data_to_save) && isset($data_to_save["config"]["environment"]) && $data_to_save["config"]["environment"] === "prod") && ENVIRONMENT === "prod") echo 'selected="selected" '; ?>>
                        Production
                    </option>
                    <option value="test" <?php if ((isset($data_to_save) && isset($data_to_save["config"]["environment"]) && $data_to_save["config"]["environment"] === "test") ||
                        !(isset($data_to_save) && isset($data_to_save["config"]["environment"]) && $data_to_save["config"]["environment"] === "test") && ENVIRONMENT === "test") echo 'selected="selected" '; ?>>
                        Test
                    </option>
                </select>
            </div>

            <div class="form-group row">
                <label for="order_id" class="col-sm-2 col-form-label">Order number (string):</label>
                <input class="text_input col-sm-2" id="order_id" name="order_number" type="text"
                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["order_number"])) echo $data_to_save["params"]['order_number'];
                       else echo(uniqid()); ?>" required>
            </div>

            <div class="form-group row">
                <label for="amount_id" class="col-sm-2 col-form-label">Cart amount (float):</label>
                <input class="text_input col-sm-2" id="amount_id" name="amount" type="text"
                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["amount"])) echo $data_to_save["params"]['amount'];
                       else echo AMOUNT_FOR_CHECKOUT; ?>" required>
            </div>

            <div class="form-group row">
                <label for="language_id" class="col-sm-2 col-form-label">Language for payform:</label>
                <select class="form-control-inline col-sm-2 inline" id="language_id"
                        name="language" required>
                    <?php foreach (\CorvusPay\Service\CheckoutService::SUPPORTED_LANGUAGES as $code => $name) { ?>
                        <?php if (isset($data_to_save) && isset($data_to_save["params"]["language"]) && $data_to_save["params"]["language"] === $code) { ?>
                            <option value="<?php echo $code ?>"
                                    selected="selected"><?php echo $name ?></option>
                        <?php } elseif ($code === LANGUAGE) { ?>
                            <option value="<?php echo $code ?>"
                                    selected="selected"><?php echo $name ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $code ?>"><?php echo $name ?></option>
                        <?php } ?><?php echo PHP_EOL;
                    } ?>
                </select>
            </div>

            <div class="form-group row">
                <label for="currency_id" class="col-sm-2 col-form-label">Currency:</label>
                <select class="form-control-inline col-sm-2 inline" id="currency_id"
                        name="currency" required>
                    <?php foreach (\CorvusPay\Service\CheckoutService::CURRENCY_CODES as $currency => $code) { ?>
                        <?php if (isset($data_to_save) && isset($data_to_save["params"]["currency"]) && $data_to_save["params"]["currency"] === $currency) { ?>
                            <option value="<?php echo $currency ?>"
                                    selected="selected"><?php echo $currency ?></option>
                        <?php } elseif ( ! isset($data_to_save) && $currency === CURRENCY) { ?>
                            <option value="<?php echo $currency ?>"
                                    selected="selected"><?php echo $currency ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $currency ?>"><?php echo $currency ?></option>>
                        <?php } ?><?php echo PHP_EOL;
                    } ?>
                </select>
            </div>

            <div class="form-group row">
                <label for="cart_id" class="col-sm-2 col-form-label">Cart content (string):</label>
                <textarea class="text_input col-sm-2" id="cart_id" name="cart"
                          required><?php if (isset($data_to_save) && isset($data_to_save["params"]["cart"])) echo $data_to_save["params"]['cart']; else echo CART; ?></textarea>
            </div>

            <div class="form-group row">
                <label for="preauthorization_id" class="col-sm-2 col-form-label">Preauthorization:</label>
                <select class="form-control-inline col-sm-2" id="preauthorization_id" name="require_complete">
                    <option value="true" <?php if ((isset($data_to_save) && isset($data_to_save["params"]["require_complete"]) && $data_to_save["params"]["require_complete"] === "true") ||
                        !(isset($data_to_save) && isset($data_to_save["params"]["require_complete"]) && $data_to_save["params"]["require_complete"] === "true") && REQUIRE_COMPLETE === "true") echo 'selected="selected" '; ?>>
                        True
                    </option>
                    <option value="false" <?php if ((isset($data_to_save) && isset($data_to_save["params"]["require_complete"]) && $data_to_save["params"]["require_complete"] === "false") ||
                        !(isset($data_to_save) && isset($data_to_save["params"]["require_complete"]) && $data_to_save["params"]["require_complete"] === "false") && REQUIRE_COMPLETE === "false") echo 'selected="selected" '; ?>>
                        False
                    </option>
                </select>
            </div>

            <input type="submit" value="Submit" class="btn btn-primary"/>
        </form>
    </div>
</div>
</body>
</html>
