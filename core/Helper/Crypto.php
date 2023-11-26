<?php
namespace Helper;

/**
 * Crypto class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.2
 */
class Crypto
{   
    /** @var string $algo */
    protected static $algo = 'whirlpool';

    /** @var string $encryptMethod */
    protected static $encryptMethod = 'AES-256-CBC';

    /** @var string $secretKey */
    protected static $secretKey = ')(YU8Flj88sdfZv%a!@#NoLLp765';

    /** @var string $secretFlag */
    protected static $secretFlag = 'DiVaK-CRYPT####';

    /** @var boolean $allowed */
    protected static $allowed = true;

    /** @var int $idLength database ID field length */
    protected static $idLength = 36;

    /**
     * Hash 
     * 
     * @param string $string
     * 
     * @return string encoded
     */
    public static function hash($string)
    {
        return hash(static::$algo, $string);
    }

    /**
     * Encrypt string
     *
     * @param string $string
     * 
     * @return string
     */
    public static function encrypt($string)
    {
        if (static::$allowed) {
            $string = static::$secretFlag . $string;
            $iv = openssl_random_pseudo_bytes(static::ivByteLength());
            $key = static::generateKey();
            $encryptedString= openssl_encrypt( $string, static::$encryptMethod, $key, 0, $iv);
            $encryptedString = bin2hex($iv) . $encryptedString;
        } else {
            $encryptedString = $string;
        }

        $encryptedString = base64_encode($encryptedString);

        return $encryptedString; 
    }

    /**
     * Decrypt string
     *
     * @param string $string
     * 
     * @return string | false
     */
    public static function decrypt($string)
    {
        $result = false;

        $string = base64_decode($string);

        if (
            !static::$allowed
            || (strlen($string) === self::$idLength && strpos($string, '-') !== false)
        ) {
            return $string;
        }

        $ivStrlen = 2  * static::ivByteLength();
        $key = static::generateKey();

        if (preg_match("/^(.{" . $ivStrlen . "})(.+)$/", $string, $regs)) {
            list(, $iv, $cryptedString) = $regs;
            $decryptedString = openssl_decrypt(
                $cryptedString, 
                static::$encryptMethod, 
                $key, 
                0, 
                hex2bin($iv)
            );
            
            if (strpos($decryptedString, static::$secretFlag) !== false) {
                $decryptedString = str_replace(static::$secretFlag, '', $decryptedString);
                $result = $decryptedString;
            }
        } 

        return $result;
    }

    /**
     * Generate key
     *
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function generateKey()
    {
        $key = static::$secretKey;
        if (ctype_print($key)) {
            // convert key to binary format
            $key = openssl_digest($key, 'SHA256', TRUE);
        }

        // available ecrypting methods
        $cipherMethods = openssl_get_cipher_methods();

        $foundMethod = false;
        foreach ($cipherMethods as $cipherMethod) {
            if (strtoupper(static::$encryptMethod) === strtoupper($cipherMethod)) {
                static::$encryptMethod = $cipherMethod;
                $foundMethod = true;
            }
        }

        if (!$foundMethod) {
            static::$allowed = false;
            throw new \RuntimeException('Unrecognised encryption method: ' . static::$encryptMethod);
        }

        return $key;
    } 

    /**
     * Get iv length in bytes depending on using  the encrypt method
     *
     * @return int
     */
    protected static function ivByteLength()
    {
        return openssl_cipher_iv_length(static::$encryptMethod);
    }
}
