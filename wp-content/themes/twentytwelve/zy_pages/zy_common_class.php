<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-20
 * Time: 上午10:54
 * 此类包含一些常用的函数,都是使用静态方法
 */
class zy_common_class{

    /**
     * 定义删除目录函数,
     * @static
     * @param string $dir 需要删除的目录
     * @return bool true|false 删除是否成功
     */
    public static function zy_deldir($dir){
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    if(!unlink($fullpath)){
                        return false;
                    }
                } else {

                    //嵌套调用
                    if(!self::zy_deldir($fullpath)){
                        return false;
                    }
                }
            }
        }
        closedir($dh);


        //删除当前文件夹：
        if(!rmdir($dir)) {
            return false;
        }

        return true;
    }

    /**
     * 由于json_decode转化中文的时候，会转成unicode编码，写一个函数代码
     * @static
     * @param array $array 需要转化的数组
     * @return string 转化后的json字符串
     */
    public static function zy_array_to_string($array){

        //$array是一维的键值数组
        $string=array();
        foreach($array as $key=>$value){
            array_push($string,'"'.$key.'":"'.$value.'"');
        }
        return '{'.implode(",",$string).'}';
    }

    /**
     * 发送http请求的函数
     *  函数在成功的情况下返回信息，不成功的情况下返回false，不成功有两种情况（code为5xx、返回的信息为failure）
     * @static
     * @param string $ids 文章或者幻灯片的id，用逗号链接
     * @param string $url 发送到的地址
     * @return bool true|false 发送是否成功
     */
    public static function zy_http_send($ids,$url){
        $response = wp_remote_post( $url, array(
                'method' => 'POST',
                'timeout' => 30,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array( 'docId' => $ids),
                'cookies' => array()
            )
        );

        //获取结果
        //$response_code = wp_remote_retrieve_response_code( $response );
        //$response_message = wp_remote_retrieve_response_message( $response );
        $response_body=wp_remote_retrieve_body($response);
        if ($response_body=="success"){
            return true;
        }else{
            return false;
        }
    }
}
