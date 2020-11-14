<?php

use HackAssembler\Assembler;

if (php_sapi_name() !== PHP_SAPI) {
    echo 'Please run this in the command line!';
    exit(1);
}


require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if ($argc !== 2) {
    echo 'Please specify the file we should translate!';
    exit(1);
}

$assembler = new Assembler();
$assembler->handle($argv[1]);
