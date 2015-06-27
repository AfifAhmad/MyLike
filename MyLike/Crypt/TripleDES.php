<?php


class MyLike__Crypt__TripleDES extends MyLike__Crypt__DES {

    var $password_key_size = 24;
    var $password_default_salt = 'phpseclib';
    var $const_namespace = 'DES';
    var $cipher_name_mcrypt = 'tripledes';
    var $cfb_init_len = 750;
    var $key_size_max = 24;
    var $mode_3cbc;
    var $des;
	const CRYPT_DES_MODE_3CBC = -2;
	const CRYPT_DES_MODE_CBC3 = MyLike__Crypt__DES::CRYPT_DES_MODE_CBC;

    public function __construct($mode = MyLike__Crypt__DES::CRYPT_DES_MODE_CBC)
    {
        switch ($mode) {
            case self::CRYPT_DES_MODE_3CBC:
                parent::__construct(MyLike__Crypt__DES::CRYPT_DES_MODE_CBC);
                $this->mode_3cbc = true;

                $this->des = array(
                    new MyLike__Crypt__DES(MyLike__Crypt__DES::CRYPT_DES_MODE_CBC),
                    new MyLike__Crypt__DES(MyLike__Crypt__DES::CRYPT_DES_MODE_CBC),
                    new MyLike__Crypt__DES(MyLike__Crypt__DES::CRYPT_DES_MODE_CBC),
                );

                $this->des[0]->disablePadding();
                $this->des[1]->disablePadding();
                $this->des[2]->disablePadding();
                break;
            default:
                parent::__construct($mode);
        }
    }

    public function setIV($iv)
    {
        parent::setIV($iv);
        if ($this->mode_3cbc) {
            $this->des[0]->setIV($iv);
            $this->des[1]->setIV($iv);
            $this->des[2]->setIV($iv);
        }
    }

    public function setKey($key)
    {
        $length = strlen($key);
        if ($length > 8) {
            $key = str_pad(substr($key, 0, 24), 24, chr(0));
        } else {
            $key = str_pad($key, 8, chr(0));
        }
        parent::setKey($key);
        if ($this->mode_3cbc && $length > 8) {
            $this->des[0]->setKey(substr($key,  0, 8));
            $this->des[1]->setKey(substr($key,  8, 8));
            $this->des[2]->setKey(substr($key, 16, 8));
        }
    }

    public function encrypt($plaintext)
    {
        if ($this->mode_3cbc && strlen($this->key) > 8) {
            return $this->des[2]->encrypt(
					$this->des[1]->decrypt(
					$this->des[0]->encrypt($this->_pad($plaintext)
					)));
        } else {
			return parent::encrypt($plaintext);
		}

    }

    public function decrypt($ciphertext)
    {
        if ($this->mode_3cbc && strlen($this->key) > 8) {
            return $this->_unpad($this->des[0]->decrypt(
                                 $this->des[1]->encrypt(
                                 $this->des[2]->decrypt(str_pad($ciphertext, (strlen($ciphertext) + 7) & 0xFFFFFFF8, "\0")))));
        } else {
			return parent::decrypt($$text);
		}

    }

    public function enableContinuousBuffer()
    {
        parent::enableContinuousBuffer();
        if ($this->mode_3cbc) {
            $this->des[0]->enableContinuousBuffer();
            $this->des[1]->enableContinuousBuffer();
            $this->des[2]->enableContinuousBuffer();
        }
    }

    public function disableContinuousBuffer()
    {
        parent::disableContinuousBuffer();
        if ($this->mode_3cbc) {
            $this->des[0]->disableContinuousBuffer();
            $this->des[1]->disableContinuousBuffer();
            $this->des[2]->disableContinuousBuffer();
        }
    }

    protected function _setupKey()
    {
        switch (true) {
            case strlen($this->key) <= 8:
                $this->des_rounds = 1;
                break;

            default:
                $this->des_rounds = 3;

                if ($this->mode_3cbc) {
                    $this->des[0]->_setupKey();
                    $this->des[1]->_setupKey();
                    $this->des[2]->_setupKey();
                    return;
                }
        }
        parent::_setupKey();
    }
}
