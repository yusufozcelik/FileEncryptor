<?php

/**
 * FileEncryptor sınıfı, dosyaları şifrelemek ve şifreli dosyaları çözmek için kullanılır.
 * 
 * Bu sınıf, verilen anahtar kullanılarak dosyaları eğer belirtilmişse belirtilen
 * şifreleme algoritmasıyla belirtilmemişse AES-256-CBC şifreleme algoritmasıyla 
 * şifreler ve çözer. Şifreleme işlemi sırasında dosya içeriği Base64 ile kodlanır.
 * 
 * Örnek Kullanım:
 * $fileEncryptor = new FileEncryptor('anahtar');
 * $fileEncryptor->encryptFile('dosya.txt', 'sifrelenmis_dosya.txt');
 * $fileEncryptor->decryptFile('sifrelenmis_dosya.txt', 'cozulmus_dosya.txt');
 * 
 * @category File Encryption
 * @package  FileEncryptor
 * @author   Yusuf Özçelik
 * @link     https://yusufozcelik.com.tr
 * @version  1.0
 */

namespace YusufOzcelik;

class FileEncryptor {
    private $cipher;
    private $key;
    private $divider = '@';
    private $startTime = null;

    /**
     * @param string $key Şifreleme için kullanılacak anahtar.
     * @param string $cipher Şifreleme için kullanılacak algoritma.
     */
    public function __construct($key, $cipher = 'AES-256-CBC') {
        $this->key = $key;
        $this->cipher = $cipher;
    }

    /**
     * @return array Algoritmalar döner.
     */
    public static function getCipherMethods(){
        return openssl_get_cipher_methods();
    }

    /**
     * @return string Key döner.
     */
    public static function generateKey($length = 32) {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new \Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $length);
    }
    
    /**
     * @param string $inputFile  Şifrelenecek dosyanın adı.
     * @param string $outputFile Şifrelenmiş dosyanın adı.
     * @param bool $deleteOriginal Şifrelenen dosyayı sil.
     * 
     * @return bool Başarılıysa true, aksi halde false döner.
     */
    public function encryptFile($inputFile, $outputFile, $deleteOriginal = true) {
        if (!file_exists($inputFile)) {
            throw new \Exception("Girdi dosyası mevcut değil: $inputFile");
        }
        $inputFileExt = pathinfo($inputFile, PATHINFO_EXTENSION);
        $plaintext = $inputFileExt.$this->divider.file_get_contents($inputFile);
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->key, true);
        $ciphertext = base64_encode($iv.$hmac.$ciphertext_raw);
        file_put_contents($outputFile, $ciphertext);
        if ($deleteOriginal) {
            unlink($inputFile);
        }
        return true;
    }
    
    /**
     * @param string $inputFile  Çözülecek dosyanın adı.
     * @param string $outputFile Çözülmüş dosyanın adı.
     * @param bool $deleteOriginal Çözülecek dosyayı sil.
     * 
     * @return bool Başarılıysa true, aksi halde false döner.
     */
    public function decryptFile($inputFile, $outputFile, $deleteOriginal = true) {
        if (!file_exists($inputFile)) {
            throw new \Exception("Girdi dosyası mevcut değil: $inputFile");
        }
        $ciphertext = file_get_contents($inputFile);
        $ciphertext = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($ciphertext, 0, $ivlen);
        $hmac = substr($ciphertext, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($ciphertext, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $this->key, true);
        if (!hash_equals($hmac, $calcmac)) {
            return false;
        }

        [$ext, $content] = explode($this->divider, $original_plaintext, 2);

        file_put_contents($outputFile.'.'.$ext, $content);
    
        if ($deleteOriginal) {
            unlink($inputFile);
        }
        return true;
    }

    /**
     * @return float|string Başlangıç zamanını döndürür.
     */
    public function startTimer(){
        $this->startTime = microtime(true);
        return $this->startTime;
    }

    /**
     * @return float Süreyi döndürür.
     */
    public function endTimer(){
        $time = microtime(true)-$this->startTime;
        $this->startTime = null;
        return $time;
    }
}