<?php

require_once("spidder.class.php");

static $s_jade_image_base_url = "http://www.chinajade.cn/jianding/";
static $s_local_image_dir = "./image/";

function download_jade_image($image_url){
	global $s_jade_image_base_url;
	global $s_local_image_dir;

	$remote_image = $s_jade_image_base_url.$image_url;
	$local_image = $s_local_image_dir.basename($image_url);	
	if (file_exists($local_image)){
		echo "$remote_image 已经下载！\n";
		return true;
	}else{
		$spidder = new spidder();
		$spidder->download($remote_image, $local_image);
		if (file_exists($local_image)){
			echo "$remote_image 下载成功！\n";
			return true;
		}else{
			die("下载失败: $remote_image");
		}
	}
}

?>
