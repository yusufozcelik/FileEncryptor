<?php

require_once '../FileEncryptor.php';

$methods = \YusufOzcelik\FileEncryptor::getCipherMethods();

echo '<pre>';
print_r($methods);