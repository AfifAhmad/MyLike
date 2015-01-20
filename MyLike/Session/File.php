<?php

class MyLike__Session__File {

    private $save_path;
	protected $session_name;

    public function open($save_path, $session_name){
        $this->save_path = $save_path;
        if (!is_dir($this->save_path)) {
            mkdir($this->save_path, 0777);
        }
		$this -> session_name = $session_name;
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
		$file =  $this -> getFilePath($id);
		if(file_exists($file))
        return (string) file_get_contents($file);
    }

    public function write($id, $data)
    {
		if($data)
        return file_put_contents($this -> getFilePath($id), $data) === false ? false : true;
		else{
			$file = $this -> getFilePath($id);
			if(file_exists($file)){
				unlink($file);
			}
			return  false;
		}
    }

    public function destroy($id)
    {
        $file =  $this -> getFilePath($id);
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

	public function gc($maxlifetime)
    {
        foreach ( glob( $this -> getFilePath() . "*" ) as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
	
	protected function getFilePath(){
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			return $this->save_path.$this -> session_name.'_'.$arguments[0];
		} else {
			return $this->save_path.$this -> session_name.'_';
		}
	}
}