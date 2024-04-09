<?php

use \YusufOzcelik\FileEncryptor;

require_once 'FileEncryptor.php';

$encryptor = new FileEncryptor('key');

$encryptor->startTimer();
$encryptor->encryptFile('input.txt', 'output.file');
echo $encryptor->endTimer() . " saniye";