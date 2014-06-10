<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-7-10
 * Time: 上午10:03
 * 图片处理类，主要是图片的压缩
 */
class zy_image_class
{
      /**
     * 创建图片，返回资源类型
     * @param string $src 图片路径
     * @return resource $im 返回资源类型
     * **/
   public function create($src)
    {
        $info=getimagesize($src);
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


        $savepath=$dir."/".$filename."_zy_compress.".$extension;//缩略图保存路径,新的文件名为*_$wx$h.jpg

        //获取图片的基本信息
        $info=getimagesize($src);
        $width=$info[0];//获取图片宽度
        $height=$info[1];//获取图片高度
        $temp_w=$width;
        $temp_h=$height;


        /*//计算缩放比例
        if($per1>$per2||$per1==$per2)
        {
            //原图长宽比大于或者等于缩略图长宽比，则按照宽度优先
            $per=$w/$width;
        }
        if($per1<$per2)
        {
            //原图长宽比小于缩略图长宽比，则按照高度优先
            $per=$h/$height;
        }*/

        //实际高度大于要压缩的高度才压缩
        if($height>$h){
            $per=$h/$height;
            $temp_w=intval($width*$per);//计算原图缩放后的宽度
            $temp_h=intval($height*$per);//计算原图缩放后的高度
        }
        $temp_img=imagecreatetruecolor($temp_w,$temp_h);//创建画布
        $im=$this->create($src);
        imagecopyresampled($temp_img,$im,0,0,0,0,$temp_w,$temp_h,$width,$height);

        imagejpeg($temp_img,$savepath, 100);
        imagedestroy($im);
        return $savepath;
    }
}
