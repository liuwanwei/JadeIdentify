<?php

class spidder {
	
	var $fp      = 0;
	var $url     = '';
	var $tmpFile = '';
	
	public function __construct(){
		$this->fp = null;
		$this->tmpDirectory = "./tmp/";
		$this->is_local_file_exist = false;
		if(false === file_exists($this->tmpDirectory)){
			echo "临时目录不存在...创建。\n";
			mkdir($this->tmpDirectory);
		}
	}
	
	// 设定要下载的目标文件地址。
	public function setUrl($url){
		$this->url = $url;

		$pathParts = pathinfo($url);
		if (empty($pathParts['basename'])
		|| strtolower($pathParts['extension']) != 'html'){
			$this->tmpFile = $this->tmpDirectory."default_temp_file.txt";
		}else{
			$this->tmpFile = $this->tmpDirectory.$pathParts['basename'];
		}
	} 
	
	// 下载目标url到本地。
	public function download($url, $localPath){
		$this->setUrl($url);
		$ch = curl_init($this->url);
		$fp = fopen($localPath, "w+");
		
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko)');
		curl_setopt($ch, CURLOPT_HEADER, 0);		

		curl_exec($ch);
		curl_close($ch);
		
		fclose($fp);
	}
	
	// 抓取目标html到临时文件中。
	public function beginFetch($url = '', $refresh = false){
		if($url != ''){
			$this->setUrl($url);
		}
		
		if(empty($this->url) || $this->url == ''){
			return false;
		}

		echo "临时文件输出到...".$this->tmpFile."\n";

		if(file_exists($this->tmpFile) && $refresh === false){
			echo "临时文件已经存在...中断操作\n";
			$this->is_local_file_exist = true;
			return true;
		}

		$this->is_local_file_exist = false;
		
		$ch = curl_init($this->url);
		if(!($fp = fopen($this->tmpFile, "w+"))){
			return false;
		}

		if(! curl_setopt($ch, CURLOPT_FILE, $fp)){
			return false;
		}
		if(! curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko)')){
			return false;
		}
		if(! curl_setopt($ch, CURLOPT_HEADER, 0)){
			return false;
		}		

		if(! curl_exec($ch)){
			return false;
		}
		curl_close($ch);
		
		fseek($fp, 0, SEEK_SET);
		
		$this->fp = $fp;				
		return true;
	}
	
	function endFetch(){
		if ($this->fp != null){
			fclose($this->fp);
		}
	}	
}
