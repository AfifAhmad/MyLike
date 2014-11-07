<?php	

class MyLike__Network__Query extends MyLike__ArrayObject__Magic{

	public function __construct(){
		$args = func_get_args();
		if(!empty($args)){
			parent::__construct($args[0]);
		}
	}

	public function add($input){
		if(is_string($input)){
			$raw = array();
			$x = $input;
			while(true){
				if(preg_match('/^(?P<kval>[^=]+=[^&]*)(?:&(?P<extended>.*))?$/', $x, $rslt)){
					$raw[] = $rslt['kval'];
					if(!empty($rslt['extended'])){
						$x = $rslt['extended'];
					}else break;
				}else break;
			}
			$input = array();
			if($raw){
				foreach($raw as $field){
					if(preg_match('/^(?P<key>[^=]+)(?:=(?<value>.*))$/i',urldecode($field), $result)){
						$key = $result['key'];
						if(preg_match('/^(?P<variable>[^\[\]]+)(?P<keys>(?:\[[^\[\]]+\])*)(?<push>\[\])?$/i', $key, $rs)){
							if('[date][start][year]'==$rs['keys']){
								$yeah = 1;
							}
							$pointer =& $input[$rs['variable']];
							if(!empty($rs['keys'])){
								$keys = $rs['keys'];
								while(true){
									if(preg_match('/^\[(?P<key>[^\[\]]+)\](?P<extended>(?:\[[^\[\]]+\])+)?$/i', $keys, $rs1)){
										if(!is_array($pointer)){
											$pointer = array();
										}
										$keys = $rs1['extended'];
										$pointer =& $pointer[$rs1['key']];
									}else break;
								}
							}
							if(!empty($rs['push'])){
								if(!is_array($pointer)){
									$pointer = array();
								}
								$pointer[] = $result['value'];
							}else $pointer = $result['value'];
						}
					}
				}
			}
		}
		parent::add($input);
		return $this;
	}

	public function __toString(){
		return http_build_query($this -> toArray());
	}

}