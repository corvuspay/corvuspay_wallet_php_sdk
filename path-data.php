<?php

define( 'BASEDIR', dirname( __FILE__ ) . '/' );
define( 'BASEURL',
    ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https"
        : "http" ) . "://$_SERVER[HTTP_HOST]" );
const URL_TO_IMAGES = 'https://cps.corvuspay.com/img/plugins/';