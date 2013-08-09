<?php

// 解析问题。
function parse_question($text){
	$fans = '';
	$date = '';
	$content = '';

	$html = str_get_html($text);

	// 提问者信息在<strong></strong>标签中。
	$strong = $html->find('strong');
	$strong = strip_tags($strong[0]);// 查询结果的第一项。理应只有一个查询结果。
	//echo $strong."\n";
	$pattern = '/[0-9|\-| |:]+$/';// 时间在最后。
	$result = preg_match($pattern, $strong, $matches);
	if ($result === 1){
		// 得到提问时间。
		$date = trim($matches[0]);	// 匹配时没有用group，所以第一个位置就是匹配结果。
		echo "提问时间：".$date."\n";

		// 剔除内容中的“提问者“部分，剩下的就是问题内容。
		$content = str_replace($strong, '', $text);
		// 去除html标签。
		$content = trim(strip_tags($content));
		// 去除&nbsp。
		$content = str_replace('&nbsp;', ' ', $content);
		// $content = iconv("UTF-8", "GBK", $content);
		echo "提问内容: ".$content."\n";
	
		return array('date'=>$date, 'content'=> $content, 'fans' => $fans);
	}

}

// 解析答案。
function parse_answer($text){
	$date = '';
	$content = '';

	$html = str_get_html($text);
	$strong = $html->find('strong');
	$content = trim(strip_tags($strong[1]));	// 第一个<strong></strong>内是“鉴定师岳工回答于。。。"，第二个<strong>内才是内容。
	// 去除&nbsp。
	$content = str_replace('&nbsp;', ' ', $content);
	//$content = iconv("GBK", "UTF-8", $content);
	echo "回答内容：".$content."\n";

	$pattern = '/[0-9|\-| |:]+ +<br>/';
	$result = preg_match($pattern, $text, $matches);
	if ($result == 1){
		$date = trim(strip_tags($matches[0]));
		echo "回答时间：".$date."\n";
		return array('date' => $date, 'content' => $content);
	}
}


function fetch_jade_qa_from_url($url){
	$spidder = new spidder();
	if(true === $spidder->beginFetch($url)){
		echo "抓取页面成功，页面抓取到 ".$spidder->tmpFile."\n";
	}else{
		echo "抓取页面失败，停止。";
		return;
	}

	if (false === $spidder->is_local_file_exist){
		$sleep_seconds = 10;
		echo "\n\n休息 $sleep_seconds 秒\n";
		sleep($sleep_seconds);
	}

	$spidder->endFetch();

	$html = file_get_html($spidder->tmpFile);

	$count = 0;
	$photoCount = 0;
	foreach($html->find('table[style*=BORDER-left: #D4D4D4"]') as $element){
		$count ++;

		// 获取提问和回答信息。
		$rows = $element->find('tr');
		$i = 0;
		echo "\n\n问题 $count : \n";
		foreach($rows as $tr){
			//echo $tr->plaintext."\n";
			if($i == 0){
				$question = parse_question($tr->innertext);
			}else if($i == 1){
				$answer = parse_answer($tr->innertext);
			}
			$i ++;
		}

		// 获取图片信息，如果有的话。
		$img_url = '';
		$img = $element->find('img');
		if(empty($img)){
			echo "没有照片!!!\n";
		}else{
			$img_url = trim($img[0]->src);
			echo $img_url."\n";
			$photoCount ++;
		}

		if (!empty($question) && !empty($answer)){

			// $record代表解析出的一条问答信息，添加到数据库中。
			global $dbh;
			if(!empty($dbh)){
				// 检查记录是否已添加。

				$sql = "SELECT * FROM jade_identify WHERE answer_date = :answer_date AND question_date = :question_date";
				$sth = $dbh->prepare($sql);
				$sth->execute(array(':answer_date' => $answer['date'], ':question_date'=>$question['date']));
				$result = $sth->fetch();
				if (empty($result)){
					$sql = "INSERT INTO jade_identify (question_date, question_content, answer_date, answer_content, jade_image) 
							VALUES(:question_date, :question_content, :answer_date, :answer_content, :jade_image)";

					$param = array(':answer_date' => $answer['date'], ':answer_content' => $answer['content'], 
							   ':question_date' => $question['date'], ':question_content' => $question['content'],
							   ':jade_image' => $img_url);


					if(! $dbh->prepare($sql)->execute($param)) {
						echo $sql."\n";
						die(print_r($dbh->errorInfo(), true));
					}
				}else{
					echo "记录已存在\n";
				}

				/*
				if(true === download_jade_image($img_url)){
					echo "下载一张图片后，等待1秒\n";
					sleep(1);
				}
				*/
			}
		}
	}

	echo "共".$count."条记录\n";
	echo "共".$photoCount."张照片\n";
}

require_once("spidder.class.php");

// 使用SimpleHtmlDom解析缓存下来的内容。
require_once("../simplehtmldom_1_5/simple_html_dom.php");

// 要抓取的页面开始序号和结束序号。
$page_begin = 1;
$page_end = 800;

// 支持通过传递参数方式抓取某个页面。
if($argc == 2 && true == is_numeric($argv[1])){
	$page_begin = $argv[1];
	$page_end = $page_begin + 1;
}else if($argc == 3 && true == is_numeric($argv[1]) && true == is_numeric($argv[2])){
	$page_begin = $argv[1];
	$page_end = $argv[2] + 1;
}

$user = 'root';
$pass = '';
$dbh = new PDO('mysql:host=127.0.0.1;dbname=jade', $user, $pass);
if(empty($dbh)){
	echo "打开数据库连接失败。";
	exit();
}

require_once("download.php");

for ($i = $page_begin; $i < $page_end; $i++){
	$jade_url = "http://www.chinajade.cn/jianding/qlist-".$i.".html";
	fetch_jade_qa_from_url($jade_url);
}

?>
