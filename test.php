<?php

$jadeUrl = "http://www.chinajade.cn/jianding/qlist-1.html";

$pathParts = pathinfo($jadeUrl);

echo '$pathParts[basename]='.$pathParts['basename']."\n";
echo $pathParts['extension'];

echo 'basename($jadeUrl)='.basename($jadeUrl)."\n";

//phpinfo();

?>
