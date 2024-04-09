<?php

use \YusufOzcelik\FileEncryptor;

require_once '../FileEncryptor.php';

try {
    $encryptor = new FileEncryptor('key');

    $inputFile = 'blog.sql';
    $outputFile = 'blog.sql.file';

    $decryptResult = $encryptor->encryptFile($inputFile, $outputFile, false);
    if ($decryptResult) {
        echo 'Başarıyla şifrelendi.';
    } else {
        echo 'Şifreleme sırasında bir sorun oluştu.';
    }
} catch (Exception $e) {
    print_r($e->getMessage());
}