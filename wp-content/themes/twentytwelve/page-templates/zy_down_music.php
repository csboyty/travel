<?php
/**
 * Template Name: 音乐下载
 *
 * Description:用于下载音乐
 *
 * Tip: music_id幻灯片的id,music_name音乐的名称
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
global $wp_query;
$music_id=$wp_query->query_vars['music_id'];

$dir=wp_upload_dir();
$music_name=get_post($music_id)->post_content; //音乐的文件名（包含后缀）保存在post_content字段中
$file_dir=$dir["basedir"]."/".$music_id."/".$music_name;

if(file_exists($file_dir)){
    Header ( "Content-type: application/octet-stream" );
    Header ( "Accept-Ranges: bytes" );
    Header ( "Accept-Length: " . filesize ( $file_dir) );
    Header ( "Content-Disposition: attachment;");
    readfile($file_dir);
    exit();
}else{
    echo "文件没有找到";
    exit();
}