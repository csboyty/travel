<?php
/**
 * Template Name: 显示图片文件
 *
 * Description:用于客户端调用，显示图片绑定的媒体文件
 *
 * Tip: post_id作为第一个参数,media_id作为第二个参数.url重写在functions.php中实现
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
global $wp_query;
$querys=$wp_query->query;
//$image_url=urldecode($querys["zy_image_url"]);//后面那一截url
$image_url=$querys["zy_image_url"];//后面那一截url
$image_url=str_replace("*_*","/",$image_url);
$dir=wp_upload_dir();
$targetUrl=$dir["baseurl"]."/".$image_url;

$temp=pathinfo($targetUrl);
$extension=$temp["extension"];//文件扩展名
//中文为自首的文件会是空
$filename=substr($targetUrl,0,strrpos($targetUrl,'.'));
$compressUrl=$filename."_zy_compress.".$extension;//缩略图保存路径,新的文件名为*_$wx$h.jpg


//设置每个域都可以访问
header('Access-Control-Allow-Origin: *');

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>展示图片</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <style type="text/css">
        body{
            text-align: center;
            margin:0;
            padding:0;
        }
    </style>

</head>
<body>
<?php
    echo "<img id='imgEl' src='".$targetUrl."' onerror='this.src=\"".$compressUrl."\"'/>";
?>
</body>
</html>