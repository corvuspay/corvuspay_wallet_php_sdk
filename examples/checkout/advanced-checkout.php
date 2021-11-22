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
    <title>Example for advanced checkout</title>
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
    $_SESSION["config"]     = $params;
    $data_to_save["config"] = $params;

    //Checkout required and optional fields.
    $params = [
        'order_number'        => $_POST["order_number"],
        'language'            => $_POST["language"],
        'currency'            => $_POST["currency"],
        'amount'              => $_POST["amount"],
        'cart'                => $_POST["cart"],
        'cardholder_name'     => $_POST["cardholder_name"],
        'cardholder_surname'  => $_POST["cardholder_surname"],
        'cardholder_address'  => $_POST["cardholder_address"],
        'cardholder_city'     => $_POST["cardholder_city"],
        'cardholder_zip_code' => $_POST["cardholder_zip_code"],
        'cardholder_country'  => $_POST["cardholder_country"],
        'cardholder_phone'    => $_POST["cardholder_phone"],
        'cardholder_email'    => $_POST["cardholder_email"],
        'require_complete'    => $_POST["require_complete"]
    ];

    if (isset($_POST['subscription']))
        $params["subscription"] = "true";

    if ($_POST['number_of_installments'] !== "")
        $params["number_of_installments"] = $_POST["number_of_installments"];

    if (isset($_POST['payment_all']))
        $params["payment_all"] = "Y0299";

    if (isset($_POST['payment_all_dynamic'])) {
        $params["payment_all_dynamic"] = "true";

        $params["payment_amex"]     = $_POST["payment_amex"];
        $params["payment_diners"]   = $_POST["payment_diners"];
        $params["payment_dina"]     = $_POST["payment_dina"];
        $params["payment_visa"]     = $_POST["payment_visa"];
        $params["payment_master"]   = $_POST["payment_master"];
        $params["payment_maestro"]  = $_POST["payment_maestro"];
        $params["payment_discover"] = $_POST["payment_discover"];
        $params["payment_jcb"]      = $_POST["payment_jcb"];
    }

    if (isset($_POST['allow_installments_map'])) {
        $amount = (float)$_POST["amount"];

        $installments_map = array();

        if (isset($_POST['installments_map_card_brand']) &&
            isset($_POST['installments_map_min_installments']) &&
            isset($_POST['installments_map_max_installments']) &&
            isset($_POST['installments_map_general_percentage']) &&
            isset($_POST['installments_map_specific_percentage'])) {
            $card_brand          = $_POST['installments_map_card_brand'];
            $min_installments    = $_POST['installments_map_min_installments'];
            $max_installments    = $_POST['installments_map_max_installments'];
            $general_percentage  = $_POST['installments_map_general_percentage'];
            $specific_percentage = $_POST['installments_map_specific_percentage'];

            foreach ($card_brand as $i => $brand) {
                if ( ! isset($card_brand[ $i ])) {
                    continue;
                }
                $installments_map[] = array(
                    'card_brand'          => $card_brand[ $i ],
                    'min_installments'    => $min_installments[ $i ],
                    'max_installments'    => $max_installments[ $i ],
                    'general_percentage'  => $general_percentage[ $i ],
                    'specific_percentage' => $specific_percentage[ $i ],
                );
            }

            $map = generateInstallmentsMap($amount, $installments_map);

            if ($map) $params["installments_map"] = $map;
        }
    }

    if ($_POST['cc_type'] !== "")
        $params["cc_type"] = $_POST["cc_type"];

    if ($_POST['cardholder_country_code'] !== "")
        $params["cardholder_country_code"] = $_POST["cardholder_country_code"];

    if (isset($_POST['hide_tabs']))
        $params["hide_tabs"] = implode(",", $_POST["hide_tabs"]);

    if ($_POST['creditor_reference'] !== "")
        $params["creditor_reference"] = $_POST["creditor_reference"];

    if ($_POST['debtor_iban'] !== "")
        $params["debtor_iban"] = $_POST["debtor_iban"];

    if ($_POST['best_before'] !== "") {
        $unixTimestamp         = time() + (int)$_POST["best_before"];
        $params["best_before"] = (string)$unixTimestamp;
    }

    if ($_POST['discount_amount'] !== "")
        $params["discount_amount"] = $_POST["discount_amount"];


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

/**
 * Generate 'installments_map' parameter.
 *
 * @param double $amount Amount to format.
 * @param array $initial_installments_map Installments map as array
 *
 * @return null|string Installments map as JSON encoded string or null on failure.
 */
function generateInstallmentsMap($amount, $initial_installments_map)
{
    $installments_map = [];

    foreach ($initial_installments_map as $installment) {
        $start = (int)$installment['min_installments'];
        $end   = (int)$installment['max_installments'];
        for ($installments = $start; $installments <= $end; $installments++) {
            if ('' !== $installment['general_percentage']) {
                $discountedAmount = $amount * (100 - (float)$installment['general_percentage']) / 100;
                foreach ($installment['card_brand'] as $card) {
                    $installments_map[ $card ][ $installments ]['amount'] = $discountedAmount;
                }
            }

            if ('' !== $installment['specific_percentage']) {
                $discountedAmount = $amount * (100 - (float)$installment['specific_percentage']) / 100;
                foreach ($installment['card_brand'] as $card) {
                    $installments_map[ $card ][ $installments ]['discounted_amount'] = $discountedAmount;
                }
            }
        }
    }

    $installments_map_json = json_encode($installments_map, JSON_FORCE_OBJECT);
    if ($installments_map_json === false) return null;

    return json_encode($installments_map, JSON_FORCE_OBJECT);
}

?>
<?php
include(BASEDIR . 'assets/js/cdn.php');
require_once('../../examples/navbar.php');
?>
<div class="card">
    <div class="p-4">
        CorvusPay advanced checkout
    </div>
    <div class="card-body">
        <form role="form" method="post">
            <div id="accordion">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne"
                                    aria-expanded="true" aria-controls="collapseOne">
                                Required fields
                            </button>
                        </h5>
                    </div>

                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="store_id" class="col-sm-2 col-form-label">Custom shop id</label>
                                <input class="text_input col-sm-2" id="store_id" name="store_id"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["config"]["store_id"])) echo $data_to_save["config"]['store_id'];
                                       else echo STORE_ID; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="secret_key" class="col-sm-2 col-form-label">Custom shop secret key</label>
                                <input class="text_input col-sm-2" id="secret_key" type="password" name="secret_key"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["config"]["secret_key"])) echo $data_to_save["config"]['secret_key'];
                                       else echo SECRET_KEY; ?>"
                                       required>
                            </div>

                            <div class="form-group">
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


                            <div class="form-group">
                                <label for="order_id" class="col-sm-2 col-form-label">Order number (string):</label>
                                <input class="text_input col-sm-2" id="order_id" name="order_number" type="text"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["order_number"])) echo $data_to_save["params"]['order_number'];
                                       else echo(uniqid()); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="amount_id" class="col-sm-2 col-form-label">Cart amount (float):</label>
                                <input class="text_input col-sm-2" id="amount_id" name="amount" type="text"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["amount"])) echo $data_to_save["params"]['amount'];
                                       else echo AMOUNT_FOR_CHECKOUT; ?>" required>
                            </div>

                            <div class="form-group">
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

                            <div class="form-group">
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

                            <div class="form-group">
                                <label for="cart_id" class="col-sm-2 col-form-label">Cart content (string):</label>
                                <textarea class="text_input col-sm-2" id="cart_id" name="cart"
                                          required><?php if (isset($data_to_save) && isset($data_to_save["params"]["cart"])) echo $data_to_save["params"]['cart']; else echo CART; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="preauthorization_id"
                                       class="col-sm-2 col-form-label">Preauthorization:</label>
                                <select class="form-control-inline col-sm-2" id="preauthorization_id"
                                        name="require_complete">
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

                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingTwo">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Optional fields for cardholder
                            </button>
                        </h5>
                    </div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="cardholder_name_id" class="col-sm-2 col-form-label">Name of the cardholder
                                    (string):</label>
                                <input id="cardholder_name_id" name="cardholder_name" class="text_input col-sm-2"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_name"])) echo $data_to_save["params"]['cardholder_name'];
                                       else echo CARDHOLDER_NAME; ?>">
                            </div>

                            <div class="form-group">
                                <label for="cardholder_surname_id" class="col-sm-2 col-form-label">Surname of the
                                    cardholder
                                    (string):</label>
                                <input id="cardholder_surname_id" name="cardholder_surname" class="text_input col-sm-2"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_surname"])) echo $data_to_save["params"]['cardholder_surname'];
                                       else echo CARDHOLDER_SURNAME; ?>">
                            </div>

                            <div class="form-group">
                                <label for="cardholder_address_id" class="col-sm-2 col-form-label">Cardholder address
                                    (string):</label>
                                <input id="cardholder_address_id" name="cardholder_address" class="text_input col-sm-2"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_address"])) echo $data_to_save["params"]['cardholder_address'];
                                       else echo CARDHOLDER_ADDRESS; ?>"><br>
                            </div>

                            <div class="form-group">
                                <label for="cardholder_city_id" class="col-sm-2 col-form-label">Cardholder city
                                    (string):</label>
                                <input id="cardholder_city_id" name="cardholder_city" class="text_input col-sm-2"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_city"])) echo $data_to_save["params"]['cardholder_city'];
                                       else echo CARDHOLDER_CITY; ?>"><br>
                            </div>

                            <div class="form-group">
                                <label for="cardholder_zip_code_id" class="col-sm-2 col-form-label">Cardholder ZIP code
                                    (string):</label>
                                <input class="text_input col-sm-2" id="cardholder_zip_code_id"
                                       name="cardholder_zip_code"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_zip_code"])) echo $data_to_save["params"]['cardholder_zip_code'];
                                       else echo CARDHOLDER_ZIP_CODE; ?>">
                            </div>

                            <div class="form-group">
                                <label for="cardholder_country_id" class="col-sm-2 col-form-label">Cardholder country
                                    (string):</label>
                                <input class="text_input col-sm-2" id="cardholder_country_id" name="cardholder_country"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_country"])) echo $data_to_save["params"]['cardholder_country'];
                                       else echo CARDHOLDER_COUNTRY; ?>">
                            </div>

                            <div class="form-group">
                                <label for="cardholder_phone_id" class="col-sm-2 col-form-label">Cardholder phone number
                                    (string):</label>
                                <input class="text_input col-sm-2" id="cardholder_phone_id" name="cardholder_phone"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_phone"])) echo $data_to_save["params"]['cardholder_phone'];
                                       else echo CARDHOLDER_PHONE; ?>">
                            </div>

                            <div class="form-group">
                                <label for="cardholder_email_id" class="col-sm-2 col-form-label">Cardholder email
                                    address
                                    (string):</label>
                                <input class="text_input col-sm-2" id="cardholder_email_id" name="cardholder_email"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_email"])) echo $data_to_save["params"]['cardholder_email'];
                                       else echo CARDHOLDER_EMAIL; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingThree">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Optional fields for installments
                            </button>
                        </h5>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="number_of_installments_id" class="col-sm-2 col-form-label">Enter the number
                                    of installments
                                    (string):</label>
                                <input class="text_input col-sm-2" id="number_of_installments_id"
                                       name="number_of_installments"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["number_of_installments"])) echo $data_to_save["params"]['number_of_installments'];
                                       else echo NUMBER_OF_INSTALLMENTS; ?>" aria-describedby="number_of_installments_help">
                                <small id="number_of_installments_help" class="form-text text-muted col-sm">
                                    A two-digit number (02 - 99) of installments valid only for American Express and
                                    Diners.
                                </small>
                            </div>
                            <p class="col-sm">or</p>
                            <div class="form-group  col-sm">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="payment_all_id"
                                           name="payment_all" <?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_all"])) echo "checked"; ?>>
                                    <label class="form-check-label" for="payment_all_id"> Allow customer to pick the
                                        number of
                                        installments </label>
                                </div>
                            </div>
                            <p class="col-sm">or</p>
                            <div class="form-group  col-sm">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="payment_all_dynamic_id"
                                           name="payment_all_dynamic" <?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_all_dynamic"])) echo "checked"; ?>>
                                    <label class="form-check-label" for="payment_all_dynamic_id"> Allow dynamic number
                                        of installments
                                        selection for specific payment card </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="payment_amex_id" class="col-sm-2 col-form-label">Amex
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_amex_id" name="payment_amex"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_amex"])) echo $data_to_save["params"]['payment_amex'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_diners_id" class="col-sm-2 col-form-label">Diners
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_diners_id" name="payment_diners"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_diners"])) echo $data_to_save["params"]['payment_diners'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_dina_id" class="col-sm-2 col-form-label">Dina
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_dina_id" name="payment_dina"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_dina"])) echo $data_to_save["params"]['payment_dina'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_visa_id" class="col-sm-2 col-form-label">Visa
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_visa_id" name="payment_visa"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_visa"])) echo $data_to_save["params"]['payment_visa'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_master_id" class="col-sm-2 col-form-label">Master
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_master_id" name="payment_master"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_master"])) echo $data_to_save["params"]['payment_master'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_maestro_id" class="col-sm-2 col-form-label">Maestro
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_maestro_id" name="payment_maestro"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_maestro"])) echo $data_to_save["params"]['payment_maestro'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_discover_id" class="col-sm-2 col-form-label">Discover
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_discover_id" name="payment_discover"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_discover"])) echo $data_to_save["params"]['payment_discover'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <div class="form-group">
                                <label for="payment_jcb_id" class="col-sm-2 col-form-label">Jcb
                                    (string):</label>
                                <input class="text_input col-sm-2" id="payment_jcb_id" name="payment_jcb"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["payment_jcb"])) echo $data_to_save["params"]['payment_jcb'];
                                       else echo DYNAMIC_NUMBER_OF_INSTALLMENTS; ?>">
                            </div>
                            <p class="col-sm">or</p>
                            <div class="form-group  col-sm">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value=""
                                           id="allow_installments_map_id"
                                           name="allow_installments_map"
                                        <?php if (isset($data_to_save) && isset($data_to_save["params"]["installments_map"])) echo "checked"; ?>>
                                    <label class="form-check-label" for="allow_installments_map_id"> Allow
                                        installments map</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <table id="installments_map">
                                    <thead>
                                    <tr>
                                        <th>Card brand</th>
                                        <th>Minimum installments</th>
                                        <th>Maximum installments</th>
                                        <th>General discount</th>
                                        <th>Specific discount</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (isset($_POST) && isset($_POST["installments_map_min_installments"])) { ?>
                                        <?php for ($i = 0; $i < count($_POST["installments_map_min_installments"]); $i++) { ?>
                                            <tr>
                                            <td>
                                                <select title="Card brand"
                                                        name="installments_map_card_brand[<?php echo $i; ?>][]"
                                                        class="selectpicker" multiple data-live-search="true">
                                                    <?php foreach (\CorvusPay\Service\CheckoutService::CARD_BRANDS as $code => $brand) { ?>
                                                        <option value="<?php echo $code ?>"
                                                            <?php if (isset($_POST) && isset($_POST["installments_map_card_brand"]) && isset($_POST["installments_map_card_brand"][ $i ]) && in_array($code, $_POST["installments_map_card_brand"][ $i ])) echo 'selected="selected" '; ?>>
                                                            <?php echo $brand ?></option>\<?php echo PHP_EOL;
                                                    } ?>
                                                </select>
                                            </td>
                                            <td><input title="Minimum installments"
                                                       value="<?php if (isset($_POST) && isset($_POST["installments_map_min_installments"]) && isset($_POST["installments_map_min_installments"][ $i ])) echo $_POST["installments_map_min_installments"][ $i ]; ?>"
                                                       name="installments_map_min_installments[<?php echo $i; ?>]"
                                                       type="number" min="1" max="99">
                                            </td>
                                            <td><input title="Maximum installments"
                                                       value="<?php if (isset($_POST) && isset($_POST["installments_map_max_installments"]) && isset($_POST["installments_map_max_installments"][ $i ])) echo $_POST["installments_map_max_installments"][ $i ]; ?>"
                                                       name="installments_map_max_installments[<?php echo $i; ?>]"
                                                       type="number" min="1" max="99">
                                            </td>
                                            <td><input title="General discount"
                                                       value="<?php if (isset($_POST) && isset($_POST["installments_map_general_percentage"]) && isset($_POST["installments_map_general_percentage"][ $i ])) echo $_POST["installments_map_general_percentage"][ $i ]; ?>"
                                                       name="installments_map_general_percentage[<?php echo $i; ?>]"
                                                       type="number" min="0" max="100">
                                            </td>
                                            <td><input title="Specific discount"
                                                       value="<?php if (isset($_POST) && isset($_POST["installments_map_specific_percentage"]) && isset($_POST["installments_map_specific_percentage"][ $i ])) echo $_POST["installments_map_specific_percentage"][ $i ]; ?>"
                                                       name="installments_map_specific_percentage[<?php echo $i; ?>]"
                                                       type="number" min="0" max="100">
                                            </td>
                                            <td><a class="delete" href="#">
                                                    <i class="material-icons">delete</i></a>
                                            </td>
                                            </tr><?php echo PHP_EOL;
                                        } ?><?php echo PHP_EOL;
                                    } ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th colspan="6">
                                            <a href="#" class="add button">+ Add installment entry</a>
                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>
                                <p>Example row: "Visa; 1; 2; 10; 15".</p>
                                <p>Explanation: All Visa cards get a 10% discount if customer pays in one payment or in
                                    two installments. Some Visa cards, issued by a specific issuer, get a 15% discount
                                    under the same conditions. To setup specific discounts, contact CorvusPay.</p>
                            </div>
                            <script type="text/javascript">
                                $(function () {
                                    $('#installments_map').on('click', 'a.add', function () {
                                        var size = $('#installments_map').find('tbody tr').length;
                                        $('<tr>\
									<td><select title="Card brand" multiple data-live-search="true" name="installments_map_card_brand[' + size + '][]">\<?php foreach ( \CorvusPay\Service\CheckoutService::CARD_BRANDS as $code => $brand ) { ?>
										<option value="<?php echo $code ?>"><?php echo $brand?></option>\<?php echo PHP_EOL; } ?>
									</select></td>\
									<td><input type="number" min="1" max="99" name="installments_map_min_installments[' + size + ']" /></td>\
									<td><input type="number" min="1" max="99" name="installments_map_max_installments[' + size + ']" /></td>\
									<td><input type="number" min="0" max="100" name="installments_map_general_percentage[' + size + ']" value="0" /></td>\
									<td><input type="number" min="0" max="100" name="installments_map_specific_percentage[' + size + ']" value="0" /></td>\
								    <td><a class="delete" href="#"><i class="material-icons">delete</i></a></td>\
								</tr>').appendTo('#installments_map tbody');
                                        $('#installments_map select').selectpicker();
                                        return false;
                                    });
                                    $('#installments_map').on('click', 'a.delete', function (e) {
                                        $(this).closest('tr').remove()
                                        return false;
                                    });
                                });
                            </script>
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Notes:</h5>
                                    <ul>
                                        <li>First character of payment_{{brandName}} value should be equal to N or Y as
                                            it indicates
                                            whether one-time payment is allowed or not
                                        </li>
                                        <li>Last four characters determines the range of the number of installments.
                                            Buyer can chose
                                            the number of installments in range between the first two-digit number and
                                            second two-digit
                                            number specified in the parameter
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingFour">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Other optional fields
                            </button>
                        </h5>
                    </div>
                    <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-group  col-sm-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="subscription_id"
                                           name="subscription" <?php if (isset($data_to_save) && isset($data_to_save["params"]["subscription"])) echo "checked"; ?>>
                                    <label class="form-check-label" for="subscription_id"> Subscribe (save
                                        card) </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cc_type_id" class="col-sm-2 col-form-label">Preselected payment
                                    card:</label>
                                <select class="form-control-inline col-sm-2 inline" id="cc_type_id"
                                        name="cc_type">
                                    <option value=""></option>
                                    <?php foreach (\CorvusPay\Service\CheckoutService::CARD_BRANDS as $code => $name) { ?>
                                        <?php if (isset($data_to_save) && isset($data_to_save["params"]["cc_type"]) && $data_to_save["params"]["cc_type"] === $code) { ?>
                                            <option value="<?php echo $code ?>"
                                                    selected="selected"><?php echo $name ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $code ?>"><?php echo $name ?></option>>
                                        <?php } ?><?php echo PHP_EOL;
                                    } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="cardholder_country_code_id" class="col-sm-2 col-form-label">Preselected
                                    cardholder country
                                    (string):</label>
                                <input class="text_input col-sm-2" id="cardholder_country_code_id"
                                       name="cardholder_country_code"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["cardholder_country_code"])) echo $data_to_save["params"]['cardholder_country_code'];
                                       else echo "HR"; ?>" aria-describedby="cardholder_country_code_help">
                                <small id="cardholder_country_code_help" class="form-text text-muted col-sm">
                                    ISO-3366-1 Alpha-2 code.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="hide_tabs_id" class="col-sm-2 col-form-label">Tabs to hide:</label>
                                <select class="form-control-inline col-sm-2 inline" id="hide_tabs_id"
                                        name="hide_tabs[]" multiple>
                                    <?php foreach (\CorvusPay\Service\CheckoutService::TABS as $code => $name) { ?>
                                        <?php if (isset($data_to_save) && isset($data_to_save["params"]["hide_tabs"]) && $data_to_save["params"]["hide_tabs"] === $code) { ?>
                                            <option value="<?php echo $code ?>"
                                                    selected="selected"><?php echo $name ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $code ?>"><?php echo $name ?></option>>
                                        <?php } ?><?php echo PHP_EOL;
                                    } ?>
                                </select>
                                <small id="hide_tabs_help" class="form-text text-muted col-sm">
                                    List of tab names which merchant want to hide during checkout.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="creditor_reference_id" class="col-sm-2 col-form-label">
                                    Creditor reference number
                                    (string):</label>
                                <input class="text_input col-sm-2" id="creditor_reference_id" name="creditor_reference"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["creditor_reference"])) echo $data_to_save["params"]['creditor_reference'];
                                       else echo CREDITOR_REFERENCE; ?>"
                                       aria-describedby="creditor_reference_help">
                                <small id="creditor_reference_help" class="form-text text-muted col-sm">
                                    Payee model and reference number.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="debtor_iban_id" class="col-sm-2 col-form-label">Prepopulate IBAN
                                    field
                                    (string):</label>
                                <input class="text_input col-sm-2" id="debtor_iban_id" name="debtor_iban"
                                       value="<?php if (isset($data_to_save) && isset($data_to_save["params"]["debtor_iban"])) echo $data_to_save["params"]['debtor_iban'];
                                       else echo DEBTOR_IBAN; ?>" aria-describedby="debtor_iban_help">
                                <small id="debtor_iban_help" class="form-text text-muted col-sm">
                                    If CorvusPay receive this value it will be prepopulated in the IBAN field on the
                                    checkout form.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="best_before_id" class="col-sm-2 col-form-label">Purchase time limitation
                                    (string):</label>
                                <input class="text_input col-sm-2" id="best_before_id" name="best_before"
                                       value="<?php if (isset($_POST) && isset($_POST["best_before"])) echo $_POST['best_before'];
                                       else echo BEST_BEFORE; ?>" aria-describedby="best_before_help" type="number" min="1"
                                       max="900">
                                <small id="best_before_help" class="form-text text-muted col-sm">
                                    UNIX timestamp in seconds. By setting the best_before optional parameter the
                                    merchant specifies when the purchase time for
                                    the transaction expires. The maximum time a merchant may specify is 900 seconds into
                                    the future.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="discount_amount_id" class="col-sm-2 col-form-label">Discount amount
                                    (string):</label>
                                <input class="text_input col-sm-2" id="discount_amount_id" name="discount_amount"
                                       value="<?php if (isset($_POST) && isset($_POST["discount_amount"])) echo $_POST['discount_amount'];
                                       else echo DISCOUNT_AMOUNT; ?>" aria-describedby="discount_amount_help">
                                <small id="discount_amount_help" class="form-text text-muted col-sm">
                                    To enable the discount amount functionality, the merchant first needs to send a mail
                                    to
                                    cps_support@corvus.hr requesting to enable the discount amount feature for a certain
                                    card.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="col-sm-2">
                    <input type="submit" value="Submit" class="btn btn-primary"/>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
