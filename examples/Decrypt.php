<?php

use \YusufOzcelik\FileEncryptor;

require_once '../FileEncryptor.php';

try {
    $encryptor = new FileEncryptor('key');

    $inputFile = 'blog.sql.file';
    $outputFile = 'blog.sql';

    $encryptResult = $encryptor->decryptFile($inputFile, $outputFile, false);
    if ($encryptResult) {
        echo 'Başarıyla şifre çözüldü.';
    } else {
        echo 'Şifre çözme işlemi sırasında bir sorun oluştu.';
    }
} catch (Exception $e) {
    print_r($e->getMessage());
}

