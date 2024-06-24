<?php

namespace App\Http\Traits;

trait CryptAES {

	// Do not use the same literals again and again	
    protected $CIPHER = 'aes-128-cbc';
    protected $GLUE = '::';
 
    public function encrypt($plain, $key) {

		// Initialization vector comes in binary. If we want to carry that
		// thru text-like worlds then we should convert it to Base64 later.
		$iv= openssl_random_pseudo_bytes( openssl_cipher_iv_length( $this->CIPHER ) );
		//echo "\n iv=\t\t(binary as hex)\t". bin2hex( $iv ). "\tlength=". strlen( $iv );

		// By default OpenSSL already returns Base64, but it could be changed 
		// to binary with the 4th parameter, if we want.
		$encryptedData= openssl_encrypt( $plain, $this->CIPHER, $key, 0, $iv );
		//echo "\n encrypted=\t(Base64)\t". $encryptedData;

		// The encrypted data already came in Base64 - no need to encode it
		// again in Base64. Just concatenate it with the initialization
		// vector, which is the only part that should also be encoded to
		// Base64. And now we have a 7bit-safe ASCII text, which could be
		// easily inserted into emails.
		return $encryptedData. $this->GLUE. base64_encode( $iv ). $this->GLUE. strlen( $plain );
    }

    public function decrypt($allinone, $key) {

		// The "glue" must be a sequence that would never occur in Base64, so
		// we chose "::" for it. If everything works as expected we get an
		// array with exactly 3 elements: first is data, second is IV, third
		// is size.
		$aParts= explode( $this->GLUE, $allinone, 3 );

		// OpenSSL expects Base64 by default as input - don't decode it!
		$data= $aParts[0];
		//echo "\n data=\t\t(Base64)\t". $data;

		// The initialization vector was encoded in Base64 by us earlier and
		// now needs to be decoded to its binary form. Should size 16 bytes.
		$iv= base64_decode( $aParts[1] );
		//echo "\n iv=\t\t(binary as hex)\t". bin2hex( $iv ). "\tlength=". strlen( $iv );

		return openssl_decrypt( $data, $this->CIPHER, $key, 0, $iv );
    }

}