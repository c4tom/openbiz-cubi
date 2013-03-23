<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Crypt
 * @subpackage DiffieHellman
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DiffieHellman.php 16971 2009-07-22 18:05:45Z mikaelkael $
 */

/**
 * PHP implementation of the Diffie-Hellman public key encryption algorithm.
 * Allows two unassociated parties to establish a joint shared secret key
 * to be used in encrypting subsequent communications.
 *
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Crypt_DiffieHellman
{

    /**
     * Static flag to select whether to use PHP5.3's openssl extension
     * if available.
     *
     * @var boolean
     */
    public static $useOpenssl = true;

    /**
     * Default large prime number; required by the algorithm.
     *
     * @var string
     */
    private $_prime = null;

    /**
     * The default generator number. This number must be greater than 0 but
     * less than the prime number set.
     *
     * @var string
     */
    private $_generator = null;

    /**
     * A private number set by the local user. It's optional and will
     * be generated if not set.
     *
     * @var string
     */
    private $_privateKey = null;

    /**
     * BigInteger support object courtesy of Zend_Crypt_Math
     *
     * @var Zend_Crypt_Math_BigInteger
     */
    private $_math = null;

    /**
     * The public key generated by this instance after calling generateKeys().
     *
     * @var string
     */
    private $_publicKey = null;

    /**
     * The shared secret key resulting from a completed Diffie Hellman
     * exchange
     *
     * @var string
     */
    private $_secretKey = null;

    /**
     * Constants
     */
    const BINARY = 'binary';
    const NUMBER = 'number';
    const BTWOC  = 'btwoc';

    /**
     * Constructor; if set construct the object using the parameter array to
     * set values for Prime, Generator and Private.
     * If a Private Key is not set, one will be generated at random.
     *
     * @param string $prime
     * @param string $generator
     * @param string $privateKey
     * @param string $privateKeyType
     * @return void
     */
    public function __construct($prime, $generator, $privateKey = null, $privateKeyType = self::NUMBER)
    {
        $this->setPrime($prime);
        $this->setGenerator($generator);
        if (!is_null($privateKey)) {
            $this->setPrivateKey($privateKey, $privateKeyType);
        }
        $this->setBigIntegerMath();
    }

    /**
     * Generate own public key. If a private number has not already been
     * set, one will be generated at this stage.
     *
     * @return Zend_Crypt_DiffieHellman
     */
    public function generateKeys()
    {
        if (function_exists('openssl_dh_compute_key') && self::$useOpenssl !== false) {
            $details = array();
            $details['p'] = $this->getPrime();
            $details['g'] = $this->getGenerator();
            if ($this->hasPrivateKey()) {
                $details['priv_key'] = $this->getPrivateKey();
            }
            $opensslKeyResource = openssl_pkey_new( array('dh' => $details) );
            $data = openssl_pkey_get_details($opensslKeyResource);
            $this->setPrivateKey($data['dh']['priv_key'], self::BINARY);
            $this->setPublicKey($data['dh']['pub_key'], self::BINARY);
        } else {
            // Private key is lazy generated in the absence of PHP 5.3's ext/openssl
            $publicKey = $this->_math->powmod($this->getGenerator(), $this->getPrivateKey(), $this->getPrime());
            $this->setPublicKey($publicKey);
        }
        return $this;
    }

    /**
     * Setter for the value of the public number
     *
     * @param string $number
     * @param string $type
     * @return Zend_Crypt_DiffieHellman
     */
    public function setPublicKey($number, $type = self::NUMBER)
    {
        if ($type == self::BINARY) {
            $number = $this->_math->fromBinary($number);
        }
        if (!preg_match("/^\d+$/", $number)) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number');
        }
        $this->_publicKey = (string) $number;
        return $this;
    }

    /**
     * Returns own public key for communication to the second party to this
     * transaction.
     *
     * @param string $type
     * @return string
     */
    public function getPublicKey($type = self::NUMBER)
    {
        if (is_null($this->_publicKey)) {
            // require_once 'Zend/Crypt/DiffieHellman/Exception.php';
            throw new Zend_Crypt_DiffieHellman_Exception('A public key has not yet been generated using a prior call to generateKeys()');
        }
        if ($type == self::BINARY) {
            return $this->_math->toBinary($this->_publicKey);
        } elseif ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_publicKey));
        }
        return $this->_publicKey;
    }

    /**
     * Compute the shared secret key based on the public key received from the
     * the second party to this transaction. This should agree to the secret
     * key the second party computes on our own public key.
     * Once in agreement, the key is known to only to both parties.
     * By default, the function expects the public key to be in binary form
     * which is the typical format when being transmitted.
     *
     * If you need the binary form of the shared secret key, call
     * getSharedSecretKey() with the optional parameter for Binary output.
     *
     * @param string $publicKey
     * @param string $type
     * @return mixed
     */
    public function computeSecretKey($publicKey, $type = self::NUMBER, $output = self::NUMBER)
    {
        if ($type == self::BINARY) {
            $publicKey = $this->_math->fromBinary($publicKey);
        }
        if (!preg_match("/^\d+$/", $publicKey)) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number');
        }
        if (function_exists('openssl_dh_compute_key') && self::$useOpenssl !== false) {
            $this->_secretKey = openssl_dh_compute_key($publicKey, $this->getPublicKey());
        } else {
            $this->_secretKey = $this->_math->powmod($publicKey, $this->getPrivateKey(), $this->getPrime());
        }
        return $this->getSharedSecretKey($output);
    }

    /**
     * Return the computed shared secret key from the DiffieHellman transaction
     *
     * @param string $type
     * @return string
     */
    public function getSharedSecretKey($type = self::NUMBER)
    {
        if (!isset($this->_secretKey)) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('A secret key has not yet been computed; call computeSecretKey()');
        }
        if ($type == self::BINARY) {
            return $this->_math->toBinary($this->_secretKey);
        } elseif ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_secretKey));
        }
        return $this->_secretKey;
    }

    /**
     * Setter for the value of the prime number
     *
     * @param string $number
     * @return Zend_Crypt_DiffieHellman
     */
    public function setPrime($number)
    {
        if (!preg_match("/^\d+$/", $number) || $number < 11) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number or too small: should be a large natural number prime');
        }
        $this->_prime = (string) $number;
        return $this;
    }

    /**
     * Getter for the value of the prime number
     *
     * @return string
     */
    public function getPrime()
    {
        if (!isset($this->_prime)) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('No prime number has been set');
        }
        return $this->_prime;
    }


    /**
     * Setter for the value of the generator number
     *
     * @param string $number
     * @return Zend_Crypt_DiffieHellman
     */
    public function setGenerator($number)
    {
        if (!preg_match("/^\d+$/", $number) || $number < 2) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number greater than 1');
        }
        $this->_generator = (string) $number;
        return $this;
    }

    /**
     * Getter for the value of the generator number
     *
     * @return string
     */
    public function getGenerator()
    {
        if (!isset($this->_generator)) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('No generator number has been set');
        }
        return $this->_generator;
    }

    /**
     * Setter for the value of the private number
     *
     * @param string $number
     * @param string $type
     * @return Zend_Crypt_DiffieHellman
     */
    public function setPrivateKey($number, $type = self::NUMBER)
    {
        if ($type == self::BINARY) {
            $number = $this->_math->fromBinary($number);
        }
        if (!preg_match("/^\d+$/", $number)) {
            // require_once('Zend/Crypt/DiffieHellman/Exception.php');
            throw new Zend_Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number');
        }
        $this->_privateKey = (string) $number;
        return $this;
    }

    /**
     * Getter for the value of the private number
     *
     * @param string $type
     * @return string
     */
    public function getPrivateKey($type = self::NUMBER)
    {
        if (!$this->hasPrivateKey()) {
            $this->setPrivateKey($this->_generatePrivateKey());
        }
        if ($type == self::BINARY) {
            return $this->_math->toBinary($this->_privateKey);
        } elseif ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_privateKey));
        }
        return $this->_privateKey;
    }

    /**
     * Check whether a private key currently exists.
     *
     * @return boolean
     */
    public function hasPrivateKey()
    {
        return isset($this->_privateKey);
    }

    /**
     * Setter to pass an extension parameter which is used to create
     * a specific BigInteger instance for a specific extension type.
     * Allows manual setting of the class in case of an extension
     * problem or bug.
     *
     * @param string $extension
     * @return void
     */
    public function setBigIntegerMath($extension = null)
    {
        /**
         * @see Zend_Crypt_Math
         */
        // require_once 'Zend/Crypt/Math.php';
        $this->_math = new Zend_Crypt_Math($extension);
    }

    /**
     * In the event a private number/key has not been set by the user,
     * or generated by ext/openssl, a best attempt will be made to
     * generate a random key. Having a random number generator installed
     * on linux/bsd is highly recommended! The alternative is not recommended
     * for production unless without any other option.
     *
     * @return string
     */
    protected function _generatePrivateKey()
    {
        $rand = $this->_math->rand($this->getGenerator(), $this->getPrime());
        return $rand;
    }

}