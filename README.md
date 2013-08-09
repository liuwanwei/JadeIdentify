JadeIdentify
============

“岳工鉴玉”栏目的一些数据抓取脚本
抓取到的数据将被保存到mysql数据库中，数据库名称：jade，数据表名称：jade_identify。（需要提前设置好）
下载到的图片文件会保存在./image/子目录下。
下载过程中产生的临时文件会被保存在./image/子目录下。

main.php
    入口，支持两个参数：./main.php page_number_1 [page_number_2]
    page_number是这个地址最后面的数字序号：http://www.chinajade.cn/jianding/qlist-1.html
    只传page_number_1时，抓取单个页面。
    传入page_number_1和page_number_2时，抓取两个数字之间的页面（从page_number_1开始）。
    
    
download.php
    不能直接调用，被main.php使用，下载玉的照片。
