<?php
$examples = ['Basic checkout'               => '/examples/checkout/basic-checkout.php',
             'Advanced checkout'            => '/examples/checkout/advanced-checkout.php',
             'Complete transaction'         => '/examples/api/complete.php',
             'Partial complete transaction' => '/examples/api/partial-complete.php',
             'Refund transaction'           => '/examples/api/refund.php',
             'Partial refund transaction'   => '/examples/api/partial-refund.php',
             'Next subscription payment'    => '/examples/api/next-sub-payment.php',
             'Cancel transaction'           => '/examples/api/cancel.php',
             'Check transaction status'     => '/examples/api/status.php',
             'Check PIS transaction status' => '/examples/api/check-pis-status.php'
];
$logo_link = HORIZONTAL_LOGO_URL;
?>
<nav class="navbar navbar-expand navbar-light bg-light">
    <img src="<?php echo $logo_link ?>" alt="corvuspay" style="max-width: 150px">

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/index.php">Home</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="" id="examples" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">Examples</a>
                <div class="dropdown-menu" aria-labelledby="examples">
                    <?php
                    foreach ($examples as $example_name => $example_link) {
                        echo '<a class="dropdown-item" href="' . $example_link . '">' . $example_name . '</a>';
                    }
                    ?>
                </div>
            </li>
        </ul>
    </div>
</nav>