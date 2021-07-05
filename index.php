<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>CorvusPay demo</title>
    <?php
    include ('path-data.php');
    include (BASEDIR . 'assets/css/cdn.php');
    ?>

    <style>
        .card {
            height: 100%;
        }

        img {
            width: 100%;
        }
    </style>
</head>
<body>

<?php require_once('./examples/navbar.php');?>
<main role="main">

    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">CorvusPay demo</h1>
            <p class="lead text-muted">This library enables easy integration of CorvusPay for web shops developed in
                PHP.</p>
            <p>
                <a href="https://www.corvuspay.com/" class="btn btn-primary my-2">CorvusPay</a>
                <a href="https://cps.corvus.hr/public/corvuspay/"
                   class="btn btn-secondary my-2">Integration manual</a>
            </p>
        </div>
    </section>

    <div class="album py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Basic checkout</h4>
                            <p class="card-text">Example form with the required fields for the POST and how to use the
                                library to checkout to CorvusPay platform. This is covered in the integration manual in
                                the following chapters:</p>
                            <ul>
                                <li>3.1. Mandatory fields for the POST</li>
                                <li>3.2. Creating post</li>
                            </ul>

                        </div>
                        <div class="d-flex justify-content-between align-items-center m-3">
                            <a type="button" class="btn btn-dark" href="examples/checkout/basic-checkout.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Advanced checkout</h4>
                            <p class="card-text">Example form with the required and optional fields for the POST and how
                                to use the library to checkout to CorvusPay platform. This is covered in the integration
                                manual from 3.1. Mandatory fields for the POST to 3.7. Subscription feedback (applicable
                                only for card transactions).</p>

                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark"
                               href="examples/checkout/advanced-checkout.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Completing a transaction</h4>
                            <p class="card-text">Example form for the POST and how to use the library to complete a
                                transaction. This is covered in the integration manual in the following chapters:</p>
                            <ul>
                                <li>4.1.1. Mandatory fields for the POST</li>
                                <li>4.1.2. Completing a transaction</li>
                                <li>4.1.3. Completing a transaction for subscription</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark" href="examples/api/complete.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Partially completing a transaction</h4>
                            <p class="card-text">Example form for the POST and how to use the library to partially
                                complete a transaction. This is covered in the integration manual in the following
                                chapters:</p>
                            <ul>
                                <li>4.1.1. Mandatory fields for the POST</li>
                                <li>4.1.4. Partially completing a transaction</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark"
                               href="examples/api/partial-complete.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Canceling a transaction</h4>
                            <p class="card-text">Example form for the POST and how to use the library to
                                cancel a transaction. This is covered in the integration manual in the following
                                chapters:</p>
                            <ul>
                                <li>4.1.1. Mandatory fields for the POST</li>
                                <li>4.1.5. Canceling a transaction
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark" href="examples/api/cancel.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Refunding a transaction</h4>
                            <p class="card-text">Example form for the POST and how to use the library to
                                refund a transaction. This is covered in the integration manual in the following
                                chapters:</p>
                            <ul>
                                <li>4.1.1. Mandatory fields for the POST</li>
                                <li>4.1.6. Refunding a transaction</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark" href="examples/api/refund.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Partially refunding a transaction</h4>
                            <p class="card-text">Example form for the POST and how to use the library to partially
                                refund a transaction. This is covered in the integration manual in the following
                                chapters:</p>
                            <ul>
                                <li>4.1.1. Mandatory fields for the POST</li>
                                <li>4.1.7. Partially refunding a transaction</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark"
                               href="examples/api/partial-refund.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Charging the next subscription payment</h4>
                            <p class="card-text">Example form for the POST and how to use the library to
                                charge the next subscription payment. This is covered in the integration manual in the
                                following chapters:</p>
                            <ul>
                                <li>4.1.1. Mandatory fields for the POST</li>
                                <li>4.1.8. Charging the next subscription payment</li>
                                <li>4.1.9. Charging the next subscription with a different amount</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark"
                               href="examples/api/next-sub-payment.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Checking transaction status</h4>
                            <p class="card-text">Example form for the POST and how to use the library to
                                check the transaction status. This is covered in the integration manual in the
                                following chapters:</p>
                            <ul>
                                <li>4.1.10. Checking transaction status</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between  align-items-center m-3">
                            <a type="button" class="btn btn-dark" href="examples/api/status.php">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h4>Checking PIS transaction status</h4>
                            <p class="card-text">Example form for the POST and how to use the library to
                                check the PIS transaction status. This is covered in the integration manual in the
                                following chapters:</p>
                            <ul>
                                <li>4.2.1. Checking PIS transaction status</li>
                                <li>4.2.1.1. Example</li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-center m-3">
                            <a type="button" class="btn btn-dark"
                               href="examples/api/check-pis-status.php">View</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Supported card brands -->
    <div class="container marketing">
        <div class="row d-flex justify-content-center m-3">
            <div class="col-lg-12 d-flex justify-content-center">
                <h1>Supported credit cards</h1>
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/amex.svg'?>" alt="american_express">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/dina.svg'?>" alt="dina_card">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/diners.svg'?>"" alt="diners">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/discover.svg'?>" alt="discover">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/jcb.svg'?>" alt="jcb">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/maestro.svg'?>"alt="maestro">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/master.svg'?>"alt="mastercard">
            </div>
            <div class="col-lg-2 my-auto">
                <img src="<?php echo URL_TO_IMAGES . 'cards/visa.svg'?>" alt="visa">
            </div>
        </div>
    </div>
</main>
<?php include (BASEDIR . 'assets/js/cdn.php'); ?>
</body>
</html>