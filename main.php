<?php

use HackAssembler\Assembler;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$assembler = new Assembler();
$assembler->handle(__DIR__ . '/tests/add.asm');
