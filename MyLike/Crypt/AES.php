<?php



class MyLike__Crypt__AES extends MyLike__Crypt__Rijndael {

    protected $const_namespace = 'AES';
	const CRYPT_AES_MODE_CTR = MyLike__Crypt__Base::CRYPT_MODE_CTR;
	const CRYPT_AES_MODE_ECB = MyLike__Crypt__Base::CRYPT_MODE_ECB;
	const CRYPT_AES_MODE_CBC = MyLike__Crypt__Base::CRYPT_MODE_CBC;
	const CRYPT_AES_MODE_CFB = MyLike__Crypt__Base::CRYPT_MODE_CFB;
	const CRYPT_AES_MODE_OFB = MyLike__Crypt__Base::CRYPT_MODE_OFB;
	const CRYPT_AES_MODE_INTERNAL = MyLike__Crypt__Base::CRYPT_MODE_INTERNAL;
	const CRYPT_AES_MODE_MCRYPT = MyLike__Crypt__Base::CRYPT_MODE_MCRYPT;

    function __construct($mode = CRYPT_AES_MODE_CBC)
    {
        parent::__construct($mode);
    }

    function setBlockLength($length)
    {
        return;
    }
}
