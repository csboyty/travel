<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-5
 * Time: 下午12:20
 * To change this template use File | Settings | File Templates.
 */

class Zy_Image {

    const ZY_COMPRESS_SUFFIX="_zy_compress";

    /**
     * 创建图片，返回资源类型
     * @param string $src 图片路径
     * @return resource $im 返回资源类型
     * **/
    public function create($src)
    {
        $info=getimagesize($src);
        $im=null;
        switch ($info[2])
        {
            case 1:
                $im=imagecreatefromgif($src);
                break;
            case 2:
                $im=imagecreatefromjpeg($src);
                break;
            case 3:
                $im=imagecreatefrompng($src);
                break;
        }
        return $im;
    }

    /**
     * 缩略图主函数
     * @param string $src 图片路径
     * @param int $w 缩略图宽度
     * @param int $h 缩略图高度
     * @return mixed 返回缩略图路径
     * **/
    public function resize($src,$w,$h)
    {
        $temp=pathinfo($src);
        $dir=$temp["dirname"];//文件所在的文件夹
        $extension=$temp["extension"];//文件扩展名


        //中文为自首的文件会是空
        $filename=substr($src,strrpos($src,"/")+1,strrpos($src,'.')-strrpos($src,"/")-1);


        $save_path=$dir."/".$filename.self::ZY_COMPRESS_SUFFIX.".".$extension;


        $gm=new Gmagick($filename);

        if($w=="*"){
            //不考虑宽度
            if($gm->getimageheight() > $h){
                $width=$gm->getimageheight()*($h/$gm->getimageheight());
                $gm->resizeimage($width,$h,1,1);
            }
        }else if($h=="*"){
            //不考虑高度
            if($gm->getimagewidth() > $w){
                $height=$gm->getimageheight()*($w/$gm->getimagewidth());
                $gm->resizeimage($w,$height,1,1);
            }
        }else{
            $gm->resizeimage($w,$h,1,1);
        }

        //不管压缩与否，都生成一个新文件在临时目录下，上传到fast后删除
        $gm->write($save_path);

    }
}