<?php


abstract class MyLike__Crypt__Base {

    protected $key;
    protected $mode;
    protected $block_size = 16;
    protected $iv;
    protected $encryptIV;
    protected $decryptIV;
    protected $continuousBuffer = false;
    protected $enbuffer;
    protected $debuffer;
    protected $enmcrypt;
    protected $demcrypt;
    protected $enchanged = true;
    protected $dechanged = true;
    protected $ecb;
    protected $cfb_init_len = 600;
    protected $changed = true;
    protected $padding = true;
    protected $paddable = false;
    protected $engine;
    protected $cipher_name_mcrypt;
    protected $password_key_size = 32;
    protected $password_default_salt = 'phpseclib/salt';
    protected $const_namespace;
    protected $inline_crypt;
    protected $use_inline_crypt;
	
	const CRYPT_MODE_CTR = -1;
	const CRYPT_MODE_ECB = 1;
	const CRYPT_MODE_CBC = 2;
	const CRYPT_MODE_CFB = 3;
	const CRYPT_MODE_OFB = 4;
	const CRYPT_MODE_STREAM = 5;
	const CRYPT_MODE_INTERNAL = 1;
	const CRYPT_MODE_MCRYPT = 2;
	
	const OUTPUT_MODE_HEX_LOWERCASE = 1;
	const OUTPUT_MODE_HEX_UPPERCASE = 2;
	const OUTPUT_MODE_CIPHERTEXT = 3;
	const OUTPUT_MODE_HEX_BASE64 = 4;
	
	protected $output_mode = self::OUTPUT_MODE_HEX_LOWERCASE;

    public function __construct($mode = self::CRYPT_MODE_CBC)
    {
        $const_crypt_mode = 'CRYPT_' . $this->const_namespace . '_MODE';

        // Determining the availibility of mcrypt support for the cipher
        if (!defined($const_crypt_mode)) {
            switch (true) {
                case extension_loaded('mcrypt') && in_array($this->cipher_name_mcrypt, mcrypt_list_algorithms()):
                    define($const_crypt_mode, self::CRYPT_MODE_MCRYPT);
                    break;
                default:
                    define($const_crypt_mode, self::CRYPT_MODE_INTERNAL);
            }
        }

        switch (true) {
            case empty($this->cipher_name_mcrypt):
                $this->engine = self::CRYPT_MODE_INTERNAL;
                break;
            case constant($const_crypt_mode) == self::CRYPT_MODE_MCRYPT:
                $this->engine = self::CRYPT_MODE_MCRYPT;
                break;
            default:
                $this->engine = self::CRYPT_MODE_INTERNAL;
        }

        // $mode dependent settings
        switch ($mode) {
            case self::CRYPT_MODE_ECB:
                $this->paddable = true;
                $this->mode = $mode;
                break;
            case self::CRYPT_MODE_CTR:
            case self::CRYPT_MODE_CFB:
            case self::CRYPT_MODE_OFB:
            case self::CRYPT_MODE_STREAM:
                $this->mode = $mode;
                break;
            case self::CRYPT_MODE_CBC:
            default:
                $this->paddable = true;
                $this->mode = self::CRYPT_MODE_CBC;
        }

        // Determining whether inline crypting can be used by the cipher
        if ($this->use_inline_crypt !== false && function_exists('create_function')) {
            $this->use_inline_crypt = true;
        }
    }
	
	public function setOutputMode()
	{
		$this -> output_mode;
		
	}

    public function setIV($iv)
    {
        if ($this->mode == self::CRYPT_MODE_ECB) {
            return;
        }

        $this->iv = $iv;
        $this->changed = true;
    }

    public function setKey($key)
    {
        $this->key = $key;
        $this->changed = true;
    }

    public function setPassword($password, $method = 'pbkdf2')
    {
        $key = '';

        switch ($method) {
            default: // 'pbkdf2'
                $func_args = func_get_args();

                // Hash function
                $hash = isset($func_args[2]) ? $func_args[2] : 'sha1';

                // WPA and WPA2 use the SSID as the salt
                $salt = isset($func_args[3]) ? $func_args[3] : $this->password_default_salt;

                // RFC2898#section-4.2 uses 1,000 iterations by default
                // WPA and WPA2 use 4,096.
                $count = isset($func_args[4]) ? $func_args[4] : 1000;

                // Keylength
                $dkLen = isset($func_args[5]) ? $func_args[5] : $this->password_key_size;

                // Determining if php[>=5.5.0]'s hash_pbkdf2() function avail- and useable
                switch (true) {
                    case !function_exists('hash_pbkdf2'):
                    case !function_exists('hash_algos'):
                    case !in_array($hash, hash_algos()):
                        $i = 1;
                        while (strlen($key) < $dkLen) {
                            $hmac = new MyLike__Crypt__Hash();
                            $hmac->setHash($hash);
                            $hmac->setKey($password);
                            $f = $u = $hmac->hash($salt . pack('N', $i++));
                            for ($j = 2; $j <= $count; ++$j) {
                                $u = $hmac->hash($u);
                                $f^= $u;
                            }
                            $key.= $f;
                        }
                        $key = substr($key, 0, $dkLen);
                        break;
                    default:
                        $key = hash_pbkdf2($hash, $password, $salt, $count, $dkLen, true);
                }
        }

        $this->setKey($key);
    }

    public function encrypt($plaintext)
    {
        if ($this->engine == self::CRYPT_MODE_MCRYPT) {
            if ($this->changed) {
                $this->_setupMcrypt();
                $this->changed = false;
            }
            if ($this->enchanged) {
                mcrypt_generic_init($this->enmcrypt, $this->key, $this->encryptIV);
                $this->enchanged = false;
            }

            if ($this->mode == self::CRYPT_MODE_CFB && $this->continuousBuffer) {
                $block_size = $this->block_size;
                $iv = &$this->encryptIV;
                $pos = &$this->enbuffer['pos'];
                $len = strlen($plaintext);
                $ciphertext = '';
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    $ciphertext = substr($iv, $orig_pos) ^ $plaintext;
                    $iv = substr_replace($iv, $ciphertext, $orig_pos, $i);
                    $this->enbuffer['enmcrypt_init'] = true;
                }
                if ($len >= $block_size) {
                    if ($this->enbuffer['enmcrypt_init'] === false || $len > $this->cfb_init_len) {
                        if ($this->enbuffer['enmcrypt_init'] === true) {
                            mcrypt_generic_init($this->enmcrypt, $this->key, $iv);
                            $this->enbuffer['enmcrypt_init'] = false;
                        }
                        $ciphertext.= mcrypt_generic($this->enmcrypt, substr($plaintext, $i, $len - $len % $block_size));
                        $iv = substr($ciphertext, -$block_size);
                        $len%= $block_size;
                    } else {
                        while ($len >= $block_size) {
                            $iv = mcrypt_generic($this->ecb, $iv) ^ substr($plaintext, $i, $block_size);
                            $ciphertext.= $iv;
                            $len-= $block_size;
                            $i+= $block_size;
                        }
                    }
                }

                if ($len) {
                    $iv = mcrypt_generic($this->ecb, $iv);
                    $block = $iv ^ substr($plaintext, -$len);
                    $iv = substr_replace($iv, $block, 0, $len);
                    $ciphertext.= $block;
                    $pos = $len;
                }

            } else {

				if ($this->paddable) {
					$plaintext = $this->_pad($plaintext);
				}

				$ciphertext = mcrypt_generic($this->enmcrypt, $plaintext);

				if (!$this->continuousBuffer) {
					mcrypt_generic_init($this->enmcrypt, $this->key, $this->encryptIV);
				}

			}
        } else {

			if ($this->changed) {
				$this->_setup();
				$this->changed = false;
			}
			if ($this->use_inline_crypt) {
				$inline = $this->inline_crypt;
				return $inline('encrypt', $this, $plaintext);
			}
			if ($this->paddable) {
				$plaintext = $this->_pad($plaintext);
			}

			$buffer = &$this->enbuffer;
			$block_size = $this->block_size;
			$ciphertext = '';
			switch ($this->mode) {
				case self::CRYPT_MODE_ECB:
					for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
						$ciphertext.= $this->_encryptBlock(substr($plaintext, $i, $block_size));
					}
					break;
				case self::CRYPT_MODE_CBC:
					$xor = $this->encryptIV;
					for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
						$block = substr($plaintext, $i, $block_size);
						$block = $this->_encryptBlock($block ^ $xor);
						$xor = $block;
						$ciphertext.= $block;
					}
					if ($this->continuousBuffer) {
						$this->encryptIV = $xor;
					}
					break;
				case self::CRYPT_MODE_CTR:
					$xor = $this->encryptIV;
					if (strlen($buffer['encrypted'])) {
						for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
							$block = substr($plaintext, $i, $block_size);
							if (strlen($block) > strlen($buffer['encrypted'])) {
								$buffer['encrypted'].= $this->_encryptBlock($this->_generateXor($xor, $block_size));
							}
							$key = $this->_stringShift($buffer['encrypted'], $block_size);
							$ciphertext.= $block ^ $key;
						}
					} else {
						for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
							$block = substr($plaintext, $i, $block_size);
							$key = $this->_encryptBlock($this->_generateXor($xor, $block_size));
							$ciphertext.= $block ^ $key;
						}
					}
					if ($this->continuousBuffer) {
						$this->encryptIV = $xor;
						if ($start = strlen($plaintext) % $block_size) {
							$buffer['encrypted'] = substr($key, $start) . $buffer['encrypted'];
						}
					}
					break;
				case self::CRYPT_MODE_CFB:
					if ($this->continuousBuffer) {
						$iv = &$this->encryptIV;
						$pos = &$buffer['pos'];
					} else {
						$iv = $this->encryptIV;
						$pos = 0;
					}
					$len = strlen($plaintext);
					$i = 0;
					if ($pos) {
						$orig_pos = $pos;
						$max = $block_size - $pos;
						if ($len >= $max) {
							$i = $max;
							$len-= $max;
							$pos = 0;
						} else {
							$i = $len;
							$pos+= $len;
							$len = 0;
						}
						// ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
						$ciphertext = substr($iv, $orig_pos) ^ $plaintext;
						$iv = substr_replace($iv, $ciphertext, $orig_pos, $i);
					}
					while ($len >= $block_size) {
						$iv = $this->_encryptBlock($iv) ^ substr($plaintext, $i, $block_size);
						$ciphertext.= $iv;
						$len-= $block_size;
						$i+= $block_size;
					}
					if ($len) {
						$iv = $this->_encryptBlock($iv);
						$block = $iv ^ substr($plaintext, $i);
						$iv = substr_replace($iv, $block, 0, $len);
						$ciphertext.= $block;
						$pos = $len;
					}
					break;
				case self::CRYPT_MODE_OFB:
					$xor = $this->encryptIV;
					if (strlen($buffer['xor'])) {
						for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
							$block = substr($plaintext, $i, $block_size);
							if (strlen($block) > strlen($buffer['xor'])) {
								$xor = $this->_encryptBlock($xor);
								$buffer['xor'].= $xor;
							}
							$key = $this->_stringShift($buffer['xor'], $block_size);
							$ciphertext.= $block ^ $key;
						}
					} else {
						for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
							$xor = $this->_encryptBlock($xor);
							$ciphertext.= substr($plaintext, $i, $block_size) ^ $xor;
						}
						$key = $xor;
					}
					if ($this->continuousBuffer) {
						$this->encryptIV = $xor;
						if ($start = strlen($plaintext) % $block_size) {
							 $buffer['xor'] = substr($key, $start) . $buffer['xor'];
						}
					}
					break;
				case self::CRYPT_MODE_STREAM:
					$ciphertext = $this->_encryptBlock($plaintext);
					break;
			}

		}
		return $this -> outputEncrypt($ciphertext);
    }

    public function decrypt($ciphertext)
    {
		$ciphertext = $this -> getCiphertext($ciphertext);
        if ($this->engine == self::CRYPT_MODE_MCRYPT) {
            $block_size = $this->block_size;
            if ($this->changed) {
                $this->_setupMcrypt();
                $this->changed = false;
            }
            if ($this->dechanged) {
                mcrypt_generic_init($this->demcrypt, $this->key, $this->decryptIV);
                $this->dechanged = false;
            }

            if ($this->mode == self::CRYPT_MODE_CFB && $this->continuousBuffer) {
                $iv = &$this->decryptIV;
                $pos = &$this->debuffer['pos'];
                $len = strlen($ciphertext);
                $plaintext = '';
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                    $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                }
                if ($len >= $block_size) {
                    $cb = substr($ciphertext, $i, $len - $len % $block_size);
                    $plaintext.= mcrypt_generic($this->ecb, $iv . $cb) ^ $cb;
                    $iv = substr($cb, -$block_size);
                    $len%= $block_size;
                }
                if ($len) {
                    $iv = mcrypt_generic($this->ecb, $iv);
                    $plaintext.= $iv ^ substr($ciphertext, -$len);
                    $iv = substr_replace($iv, substr($ciphertext, -$len), 0, $len);
                    $pos = $len;
                }

                return $plaintext;
            }

            if ($this->paddable) {
                $ciphertext = str_pad($ciphertext, strlen($ciphertext) + ($block_size - strlen($ciphertext) % $block_size) % $block_size, chr(0));
            }

            $plaintext = mdecrypt_generic($this->demcrypt, $ciphertext);

            if (!$this->continuousBuffer) {
                mcrypt_generic_init($this->demcrypt, $this->key, $this->decryptIV);
            }

            return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
        }

        if ($this->changed) {
            $this->_setup();
            $this->changed = false;
        }
        if ($this->use_inline_crypt) {
            $inline = $this->inline_crypt;
            return $inline('decrypt', $this, $ciphertext);
        }

        $block_size = $this->block_size;
        if ($this->paddable) {
            // we pad with chr(0) since that's what mcrypt_generic does [...]
            $ciphertext = str_pad($ciphertext, strlen($ciphertext) + ($block_size - strlen($ciphertext) % $block_size) % $block_size, chr(0));
        }

        $buffer = &$this->debuffer;
        $plaintext = '';
        switch ($this->mode) {
            case self::CRYPT_MODE_ECB:
                for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                    $plaintext.= $this->_decryptBlock(substr($ciphertext, $i, $block_size));
                }
                break;
            case self::CRYPT_MODE_CBC:
                $xor = $this->decryptIV;
                for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                    $block = substr($ciphertext, $i, $block_size);
                    $plaintext.= $this->_decryptBlock($block) ^ $xor;
                    $xor = $block;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                }
                break;
            case self::CRYPT_MODE_CTR:
                $xor = $this->decryptIV;
                if (strlen($buffer['ciphertext'])) {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['ciphertext'])) {
                            $buffer['ciphertext'].= $this->_encryptBlock($this->_generateXor($xor, $block_size));
                        }
                        $key = $this->_stringShift($buffer['ciphertext'], $block_size);
                        $plaintext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        $key = $this->_encryptBlock($this->_generateXor($xor, $block_size));
                        $plaintext.= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                        $buffer['ciphertext'] = substr($key, $start) . $buffer['ciphertext'];
                    }
                }
                break;
            case self::CRYPT_MODE_CFB:
                if ($this->continuousBuffer) {
                    $iv = &$this->decryptIV;
                    $pos = &$buffer['pos'];
                } else {
                    $iv = $this->decryptIV;
                    $pos = 0;
                }
                $len = strlen($ciphertext);
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                    $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                }
                while ($len >= $block_size) {
                    $iv = $this->_encryptBlock($iv);
                    $cb = substr($ciphertext, $i, $block_size);
                    $plaintext.= $iv ^ $cb;
                    $iv = $cb;
                    $len-= $block_size;
                    $i+= $block_size;
                }
                if ($len) {
                    $iv = $this->_encryptBlock($iv);
                    $plaintext.= $iv ^ substr($ciphertext, $i);
                    $iv = substr_replace($iv, substr($ciphertext, $i), 0, $len);
                    $pos = $len;
                }
                break;
            case self::CRYPT_MODE_OFB:
                $xor = $this->decryptIV;
                if (strlen($buffer['xor'])) {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['xor'])) {
                            $xor = $this->_encryptBlock($xor);
                            $buffer['xor'].= $xor;
                        }
                        $key = $this->_stringShift($buffer['xor'], $block_size);
                        $plaintext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $plaintext.= substr($ciphertext, $i, $block_size) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                         $buffer['xor'] = substr($key, $start) . $buffer['xor'];
                    }
                }
                break;
            case self::CRYPT_MODE_STREAM:
                $plaintext = $this->_decryptBlock($ciphertext);
                break;
        }
        return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
    }
	
	
	protected function outputEncrypt($ciphertext)
	{
		switch($this -> output_mode){
			case self::OUTPUT_MODE_HEX_UPPERCASE:
				return strtoupper($this -> stringToHex($ciphertext));
			case self::OUTPUT_MODE_CIPHERTEXT:
				return $ciphertext;
			case self::OUTPUT_MODE_HEX_BASE64:
				return base64_encode($ciphertext);
			default:
				return $this -> stringToHex($ciphertext);
		}
	}
	
	protected function getCiphertext($text)
	{
		switch($this -> output_mode){
			case self::OUTPUT_MODE_HEX_UPPERCASE:
				return $this -> hexToString(strtolower($text));
			case self::OUTPUT_MODE_CIPHERTEXT:
				return $text;
			case self::OUTPUT_MODE_HEX_BASE64:
				return base64_decode($text);
			default:
				return $this -> hexToString($text);
		}
	}
	
	protected function stringToHex ($s) {
		$r = "";
		$hexes = array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
		for ($i=0; $i<strlen($s); $i++) {$r .= ($hexes [(ord($s{$i}) >> 4)] . $hexes [(ord($s{$i}) & 0xf)]);}
		return $r;
	}

	protected function hexToString ($h) {
		$r = "";
		for ($i= 0; $i<strlen($h); $i+=2) {
			$r .= chr (base_convert (substr ($h, $i, 2), 16, 10));
		}
		return $r;
	}

    public function enablePadding()
    {
        $this->padding = true;
    }

    public function disablePadding()
    {
        $this->padding = false;
    }

    public function enableContinuousBuffer()
    {
        if ($this->mode == self::CRYPT_MODE_ECB) {
            return;
        }

        $this->continuousBuffer = true;
    }

    public function disableContinuousBuffer()
    {
        if ($this->mode == self::CRYPT_MODE_ECB) {
            return;
        }
        if (!$this->continuousBuffer) {
            return;
        }

        $this->continuousBuffer = false;
        $this->changed = true;
    }

    protected function _encryptBlock($in)
    {
        throw new MyLike__Crypt__Exception__UndefinedMethod(__METHOD__ . '() must extend by class ' . get_class($this));
    }

    protected function _decryptBlock($in)
    {
        throw new MyLike__Crypt__Exception__UndefinedMethod(__METHOD__ . '() must extend by class ' . get_class($this));
    }

    protected function _setupKey()
    {
        throw new MyLike__Crypt__Exception__UndefinedMethod(__METHOD__ . '() must extend by class ' . get_class($this));
    }

    protected function _setup()
    {
        $this->_clearBuffers();
        $this->_setupKey();

        if ($this->use_inline_crypt) {
            $this->_setupInlineCrypt();
        }
    }

    protected function _setupMcrypt()
    {
        $this->_clearBuffers();
        $this->enchanged = $this->dechanged = true;

        if (!isset($this->enmcrypt)) {
            static $mcrypt_modes = array(
                self::CRYPT_MODE_CTR    => 'ctr',
                self::CRYPT_MODE_ECB    => MCRYPT_MODE_ECB,
                self::CRYPT_MODE_CBC    => MCRYPT_MODE_CBC,
                self::CRYPT_MODE_CFB    => 'ncfb',
                self::CRYPT_MODE_OFB    => MCRYPT_MODE_NOFB,
                self::CRYPT_MODE_STREAM => MCRYPT_MODE_STREAM,
            );

            $this->demcrypt = mcrypt_module_open($this->cipher_name_mcrypt, '', $mcrypt_modes[$this->mode], '');
            $this->enmcrypt = mcrypt_module_open($this->cipher_name_mcrypt, '', $mcrypt_modes[$this->mode], '');

            if ($this->mode == self::CRYPT_MODE_CFB) {
                $this->ecb = mcrypt_module_open($this->cipher_name_mcrypt, '', MCRYPT_MODE_ECB, '');
            }

        } // else should mcrypt_generic_deinit be called?

        if ($this->mode == self::CRYPT_MODE_CFB) {
            mcrypt_generic_init($this->ecb, $this->key, str_repeat("\0", $this->block_size));
        }
    }

    public function _pad($text)
    {
        $length = strlen($text);

        if (!$this->padding) {
            if ($length % $this->block_size == 0) {
                return $text;
            } else {
                throw new MyLike__Crypt__Exception__InvalidLength("The plaintext's length ($length) is not a multiple of the block size ({$this->block_size})");
            }
        }

        $pad = $this->block_size - ($length % $this->block_size);

        return str_pad($text, $length + $pad, chr($pad));
    }

    public function _unpad($text)
    {
        if (!$this->padding) {
            return $text;
        }

        $length = ord($text[strlen($text) - 1]);

        if (!$length || $length > $this->block_size) {
			return null;
        }

        return substr($text, 0, -$length);
    }

    public function _clearBuffers()
    {
        $this->enbuffer = array('encrypted'  => '', 'xor' => '', 'pos' => 0, 'enmcrypt_init' => true);
        $this->debuffer = array('ciphertext' => '', 'xor' => '', 'pos' => 0, 'demcrypt_init' => true);

        $this->encryptIV = $this->decryptIV = str_pad(substr($this->iv, 0, $this->block_size), $this->block_size, "\0");
    }

    public function _stringShift(&$string, $index = 1)
    {
        $substr = substr($string, 0, $index);
        $string = substr($string, $index);
        return $substr;
    }

    public function _generateXor(&$iv, $length)
    {
        $xor = '';
        $block_size = $this->block_size;
        $num_blocks = floor(($length + ($block_size - 1)) / $block_size);
        for ($i = 0; $i < $num_blocks; $i++) {
            $xor.= $iv;
            for ($j = 4; $j <= $block_size; $j+= 4) {
                $temp = substr($iv, -$j, 4);
                switch ($temp) {
                    case "\xFF\xFF\xFF\xFF":
                        $iv = substr_replace($iv, "\x00\x00\x00\x00", -$j, 4);
                        break;
                    case "\x7F\xFF\xFF\xFF":
                        $iv = substr_replace($iv, "\x80\x00\x00\x00", -$j, 4);
                        break 2;
                    default:
                        extract(unpack('Ncount', $temp));
                        $iv = substr_replace($iv, pack('N', $count + 1), -$j, 4);
                        break 2;
                }
            }
        }

        return $xor;
    }

    protected function _setupInlineCrypt()
    {
        $this->use_inline_crypt = false;
    }

    public function _createInlineCryptFunction($cipher_code)
    {
        $block_size = $this->block_size;

        // optional
        $init_crypt    = isset($cipher_code['init_crypt'])    ? $cipher_code['init_crypt']    : '';
        $init_encrypt  = isset($cipher_code['init_encrypt'])  ? $cipher_code['init_encrypt']  : '';
        $init_decrypt  = isset($cipher_code['init_decrypt'])  ? $cipher_code['init_decrypt']  : '';
        // required
        $encrypt_block = $cipher_code['encrypt_block'];
        $decrypt_block = $cipher_code['decrypt_block'];

        switch ($this->mode) {
            case self::CRYPT_MODE_ECB:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_text = $self->_pad($_text);
                    $_plaintext_len = strlen($_text);

                    for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.');
                        '.$encrypt_block.'
                        $_ciphertext.= $in;
                    }

                    return $_ciphertext;
                    ';

                $decrypt = $init_decrypt . '
                    $_plaintext = "";
                    $_text = str_pad($_text, strlen($_text) + ('.$block_size.' - strlen($_text) % '.$block_size.') % '.$block_size.', chr(0));
                    $_ciphertext_len = strlen($_text);

                    for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.');
                        '.$decrypt_block.'
                        $_plaintext.= $in;
                    }

                    return $self->_unpad($_plaintext);
                    ';
                break;
            case self::CRYPT_MODE_CTR:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);
                    $_xor = $self->encryptIV;
                    $_buffer = &$self->enbuffer;

                    if (strlen($_buffer["encrypted"])) {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["encrypted"])) {
                                $in = $self->_generateXor($_xor, '.$block_size.');
                                '.$encrypt_block.'
                                $_buffer["encrypted"].= $in;
                            }
                            $_key = $self->_stringShift($_buffer["encrypted"], '.$block_size.');
                            $_ciphertext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            $in = $self->_generateXor($_xor, '.$block_size.');
                            '.$encrypt_block.'
                            $_key = $in;
                            $_ciphertext.= $_block ^ $_key;
                        }
                    }
                    if ($self->continuousBuffer) {
                        $self->encryptIV = $_xor;
                        if ($_start = $_plaintext_len % '.$block_size.') {
                            $_buffer["encrypted"] = substr($_key, $_start) . $_buffer["encrypted"];
                        }
                    }

                    return $_ciphertext;
                ';

                $decrypt = $init_encrypt . '
                    $_plaintext = "";
                    $_ciphertext_len = strlen($_text);
                    $_xor = $self->decryptIV;
                    $_buffer = &$self->debuffer;

                    if (strlen($_buffer["ciphertext"])) {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["ciphertext"])) {
                                $in = $self->_generateXor($_xor, '.$block_size.');
                                '.$encrypt_block.'
                                $_buffer["ciphertext"].= $in;
                            }
                            $_key = $self->_stringShift($_buffer["ciphertext"], '.$block_size.');
                            $_plaintext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            $in = $self->_generateXor($_xor, '.$block_size.');
                            '.$encrypt_block.'
                            $_key = $in;
                            $_plaintext.= $_block ^ $_key;
                        }
                    }
                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_xor;
                        if ($_start = $_ciphertext_len % '.$block_size.') {
                            $_buffer["ciphertext"] = substr($_key, $_start) . $_buffer["ciphertext"];
                        }
                    }

                    return $_plaintext;
                    ';
                break;
            case self::CRYPT_MODE_CFB:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_buffer = &$self->enbuffer;

                    if ($self->continuousBuffer) {
                        $_iv = &$self->encryptIV;
                        $_pos = &$_buffer["pos"];
                    } else {
                        $_iv = $self->encryptIV;
                        $_pos = 0;
                    }
                    $_len = strlen($_text);
                    $_i = 0;
                    if ($_pos) {
                        $_orig_pos = $_pos;
                        $_max = '.$block_size.' - $_pos;
                        if ($_len >= $_max) {
                            $_i = $_max;
                            $_len-= $_max;
                            $_pos = 0;
                        } else {
                            $_i = $_len;
                            $_pos+= $_len;
                            $_len = 0;
                        }
                        $_ciphertext = substr($_iv, $_orig_pos) ^ $_text;
                        $_iv = substr_replace($_iv, $_ciphertext, $_orig_pos, $_i);
                    }
                    while ($_len >= '.$block_size.') {
                        $in = $_iv;
                        '.$encrypt_block.';
                        $_iv = $in ^ substr($_text, $_i, '.$block_size.');
                        $_ciphertext.= $_iv;
                        $_len-= '.$block_size.';
                        $_i+= '.$block_size.';
                    }
                    if ($_len) {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $_block = $_iv ^ substr($_text, $_i);
                        $_iv = substr_replace($_iv, $_block, 0, $_len);
                        $_ciphertext.= $_block;
                        $_pos = $_len;
                    }
                    return $_ciphertext;
                ';

                $decrypt = $init_encrypt . '
                    $_plaintext = "";
                    $_buffer = &$self->debuffer;

                    if ($self->continuousBuffer) {
                        $_iv = &$self->decryptIV;
                        $_pos = &$_buffer["pos"];
                    } else {
                        $_iv = $self->decryptIV;
                        $_pos = 0;
                    }
                    $_len = strlen($_text);
                    $_i = 0;
                    if ($_pos) {
                        $_orig_pos = $_pos;
                        $_max = '.$block_size.' - $_pos;
                        if ($_len >= $_max) {
                            $_i = $_max;
                            $_len-= $_max;
                            $_pos = 0;
                        } else {
                            $_i = $_len;
                            $_pos+= $_len;
                            $_len = 0;
                        }
                        $_plaintext = substr($_iv, $_orig_pos) ^ $_text;
                        $_iv = substr_replace($_iv, substr($_text, 0, $_i), $_orig_pos, $_i);
                    }
                    while ($_len >= '.$block_size.') {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $cb = substr($_text, $_i, '.$block_size.');
                        $_plaintext.= $_iv ^ $cb;
                        $_iv = $cb;
                        $_len-= '.$block_size.';
                        $_i+= '.$block_size.';
                    }
                    if ($_len) {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $_plaintext.= $_iv ^ substr($_text, $_i);
                        $_iv = substr_replace($_iv, substr($_text, $_i), 0, $_len);
                        $_pos = $_len;
                    }

                    return $_plaintext;
                    ';
                break;
            case self::CRYPT_MODE_OFB:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);
                    $_xor = $self->encryptIV;
                    $_buffer = &$self->enbuffer;

                    if (strlen($_buffer["xor"])) {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["xor"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $_xor = $in;
                                $_buffer["xor"].= $_xor;
                            }
                            $_key = $self->_stringShift($_buffer["xor"], '.$block_size.');
                            $_ciphertext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $in = $_xor;
                            '.$encrypt_block.'
                            $_xor = $in;
                            $_ciphertext.= substr($_text, $_i, '.$block_size.') ^ $_xor;
                        }
                        $_key = $_xor;
                    }
                    if ($self->continuousBuffer) {
                        $self->encryptIV = $_xor;
                        if ($_start = $_plaintext_len % '.$block_size.') {
                             $_buffer["xor"] = substr($_key, $_start) . $_buffer["xor"];
                        }
                    }
                    return $_ciphertext;
                    ';

                $decrypt = $init_encrypt . '
                    $_plaintext = "";
                    $_ciphertext_len = strlen($_text);
                    $_xor = $self->decryptIV;
                    $_buffer = &$self->debuffer;

                    if (strlen($_buffer["xor"])) {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["xor"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $_xor = $in;
                                $_buffer["xor"].= $_xor;
                            }
                            $_key = $self->_stringShift($_buffer["xor"], '.$block_size.');
                            $_plaintext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $in = $_xor;
                            '.$encrypt_block.'
                            $_xor = $in;
                            $_plaintext.= substr($_text, $_i, '.$block_size.') ^ $_xor;
                        }
                        $_key = $_xor;
                    }
                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_xor;
                        if ($_start = $_ciphertext_len % '.$block_size.') {
                             $_buffer["xor"] = substr($_key, $_start) . $_buffer["xor"];
                        }
                    }
                    return $_plaintext;
                    ';
                break;
            case self::CRYPT_MODE_STREAM:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    '.$encrypt_block.'
                    return $_ciphertext;
                    ';
                $decrypt = $init_decrypt . '
                    $_plaintext = "";
                    '.$decrypt_block.'
                    return $_plaintext;
                    ';
                break;
            // case CRYPT_MODE_CBC:
            default:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_text = $self->_pad($_text);
                    $_plaintext_len = strlen($_text);

                    $in = $self->encryptIV;

                    for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.') ^ $in;
                        '.$encrypt_block.'
                        $_ciphertext.= $in;
                    }

                    if ($self->continuousBuffer) {
                        $self->encryptIV = $in;
                    }

                    return $_ciphertext;
                    ';

                $decrypt = $init_decrypt . '
                    $_plaintext = "";
                    $_text = str_pad($_text, strlen($_text) + ('.$block_size.' - strlen($_text) % '.$block_size.') % '.$block_size.', chr(0));
                    $_ciphertext_len = strlen($_text);

                    $_iv = $self->decryptIV;

                    for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                        $in = $_block = substr($_text, $_i, '.$block_size.');
                        '.$decrypt_block.'
                        $_plaintext.= $in ^ $_iv;
                        $_iv = $_block;
                    }

                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_iv;
                    }

                    return $self->_unpad($_plaintext);
                    ';
                break;
        }

        return create_function('$_action, &$self, $_text', $init_crypt . 'if ($_action == "encrypt") { ' . $encrypt . ' } else { ' . $decrypt . ' }');
    }

    public function &_getLambdaFunctions()
    {
        static $functions = array();
        return $functions;
    }
}
