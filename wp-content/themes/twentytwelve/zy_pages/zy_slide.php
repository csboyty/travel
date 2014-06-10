<?php

/**

 * Created by JetBrains PhpStorm.

 * User: ty

 * Date: 13-6-18

 * Time: 下午2:37

 * 添加幻灯片菜单页面。

 */

//在functions.php中已经将类包含进来了

include("zy_slide_save_class.php");

include("zy_articles_help_class.php");

$zy_save_slide=new zy_slide_save_class();

$zy_articles_help=new zy_articles_help_class();

$post_id="";

global $wpdb;
?>

<form action="<?php echo admin_url()."edit.php?page=zy_slide_menu"; ?>" class="zy_form" method="post">

<!--头部(标题)-->

<header id="zy_header" class="zy_header">

    <span class="zy_upload_logo"></span>

    <h2>编辑幻灯片</h2>

</header>



<?php

//从连接上获取post_id，这里主要是从列表跳转过来

if(isset($_GET["post_id"])){

    $post_id=$_GET["post_id"];

}

//保存是否成功

if(isset($_POST["zy_action_flag"])){

    if($_POST["zy_action_flag"]=="zy_new"){

        $post_id=$zy_save_slide->zy_slide_new();

    }else{

        $post_id=$zy_save_slide->zy_slide_edit();

    }

    echo "<h4 class='zy_message_tip'>保存成功,请继续进行其他操作</h4>";
}

//判断是新增还是修改，来设置标志位

if($post_id){

    echo "<input type='hidden' name='zy_action_flag' value='zy_edit'>";

    //设置编辑标志

    if(($edit_time=get_post_meta($post_id,"_edit_lock",true))!=""){

        $time_array=explode(":",$edit_time);
        $old_user_id=$time_array[1];

        if($old_user_id!=get_current_user_id()){

            //如果不是单前用户在编辑,并且时间小于5分钟，进行提示
            if(time()-$time_array[0]<5*60){
                $user_info=get_userdata($old_user_id);
                $user_name=$user_info->display_name;

                $message = __( 'Warning: %s is currently editing this post' );
                $message = sprintf( $message, esc_html( $user_name ) );

                echo "<h4 class='zy_message_tip'>$message</h4>";
            }
        }

        //设置版本值，即数据库的锁定标志
        echo "<input type='hidden' name='_edit_lock' value='$edit_time'>";

    }

}else{

    echo "<input type='hidden' name='zy_action_flag' value='zy_new'>";

}

echo "<input type='hidden' id='post_ID' name='zy_slide_id' value='$post_id'>";

?>

<!--导航栏-->

<nav id="zy_nav">

    <li><a href="#zy_attribute" class="zy_nav1 zy_active">基本设定</a></li>

    <li><a href="#zy_content" class="zy_nav2">内容编辑</a></li>

    <li><a href="#zy_preview" class="zy_nav3">预览并发布</a></li>

    <input id="zy_insert_btn" type="submit" class="zy_btn_insert zy_hidden" value="发布">

</nav>



<!--第一步(设置属性等)-->

<article id="zy_attribute" class="zy_attribute">

    <!--左边栏-->

    <section id="zy_left_bar">



        <section>

            <label class="zy_label">幻灯片主题</label>

            <input required id="zy_title" maxlength="150" value="<?php echo get_the_title($post_id) ?>" class="zy_item" type="text" name="zy_title"/>



        </section>



        <section class="zy_left_box">

            <label class="zy_label">描述</label>

            <textarea id="zy_memo" class="zy_descr zy_textarea" type="text" name="zy_memo"><?php echo get_post($post_id)->post_excerpt ?></textarea>

        </section>



        <section class="zy_left_box">

            <label class="zy_label">封面</label>



            <div id="zy_thumb_container" class="zy_thumb_toolbar">

                <input class="zy_upload_btn" type="button" id="zy_upload_thumb_button" value="上传"/>

                <p class="zy_tool_tips">限高宽比为1：1的jpg或png</p>

            </div>

            <?php
                //获取原来的缩略图
                $zy_old_thumb=$zy_articles_help->zy_get_old_thumb($post_id);

            ?>

            <img id="zy_uploaded_thumb" class="zy_cover_pic" src="<?php

                 if($zy_old_thumb){

                     //显示压缩后的图片

                    $zy_articles_help->zy_get_compress_thumb($zy_old_thumb["filepath"]);

                 }else{

                     echo get_template_directory_uri()."/images/app/zy_default_thumb.png";

                 }

                 ?>" class="zy_post_img">

        </section>
    </section>

    <!--右边栏-->

    <aside id="zy_right_bar">



        <section class="zy_right_box">

            <header class="zy_box_header">

                分类

            </header>

            <section id="zy_category_list">

                <!--获取幻灯片文章的类别-->

                <?php wp_category_checklist( $post_id, 0, false,

                false, null, false); ?>

            </section>

        </section>

        <section class="zy_right_box">

            <header class="zy_box_header">

                标签

            </header>

            <script type="text/javascript">
                var zy_tags=[
                    <?php
                        $tags=get_tags();
                        foreach($tags as $tag){
                            echo "'$tag->name',";
                        }
                    ?>
                ];
            </script>

            <input id="zy_tags" type="text">
            <input type="button" value="添加" id="zy_add_tag" class="zy_add_tag_btn">

            <script id="zy_tag_tpl" type="text/template">
                <span class="zy_tag"><input type="hidden" name="zy_tags[]" value="${tag_name}"> <a class="zy_tag_delete">X</a><span class="zy_tag_name">${tag_name}</span></span>
            </script>

            <div id="zy_select_tags" class="zy_select_tags">
                <span>已选标签：</span>

                <!--输出原来的标签-->
                <?php
                    $zy_articles_help->zy_get_tags($post_id);
                ?>
            </div>

        </section>

        <section class="zy_right_box">

            <header class="zy_box_header">

                背景

            </header>



            <section id="zy_background_container" style="text-align: right">

            	<div class="zy_bg_toolbar">

                    <input id="zy_upload_background_clear" type="button" class="zy_upload_btn" value="清除">

                    <input class="zy_upload_btn" type="button"  id="zy_upload_background_button" value="上传"/>

                    <p class="zy_tool_tips">限jpg、png、mp4，分辨率1280*720</p>

                </div>

                <span id="zy_background_percent" class="zy_background_percent"></span>

                <?php

                    //获取原来的缩略图
                    $zy_articles_help->zy_get_old_background($post_id);

                ?>



            </section>

        </section>

    </aside>



</article>



<!--第二步(上传媒体文件)-->



<article id="zy_content" class="zy_hidden zy_uploader">

    <input  id="zy_slide_content" class="zy_item zy_input" type="hidden" name="zy_content"/>

    <input  id="zy_medias" class="zy_item zy_input" type="hidden" name="zy_medias"/>

    <section id="zy_section_left" class="zy_uploader_column_left">

        <span class="zy_section_left_header" id="zy_add_medias_button"></span>

        <!--媒体文件类型的menu-->

        <div id="zy_add_media_menu" class="zy_add_media_menu">

            <ul>

                <li><a id="zy_add_image" class="zy_types1">图片</a></li>

                <!--弹出thickbox窗口，来进行网络视频输入-->

                <li><a title="网络视频" href="#TB_inline?width=150&height=200&inlineId=zy_thickbox_id" class="thickbox zy_types2">网络视频</a></li>

                <li><a id="zy_add_location_video" class="zy_types3">本地视频</a></li>

                <li><a id="zy_add_3d" class="zy_types4">3D文件</a></li>

                <li><a id="zy_add_ppt" class="zy_types5">ppt文件</a></li>

            </ul>

        </div>



        <!-- 媒体文件列表-->

        <!-- 上传未完成的html模版-->

        <script type="text/template" id="zy_uncomplete_tpl">

            <li data-zy-filename="${filename}" class="zy_uncomplete_li" data-zy-media-id="${media_id}">

                <img class="zy_small_thumb" src="${thumb_src}">

                <span class="zy_media_percent">0%</span>

                <span class="zy_uncomplete_delete"></span>

            </li>

        </script>

        <!-- 上传完成的html模版-->

        <script type="text/template" id="zy_complete_tpl">

            <li ${classString}><a class="zy_media_list" data-zy-media-type="${media_type}" data-zy-media-id='${media_id}' href="${iframe_src}" target="zy_media_iframe">

                <img class="zy_small_thumb" src="${thumb_src}">

                <span title='${filename}' draggable="true" class="zy_media_filename">${filename}</span><span class="zy_media_delete"></span></a>

            </li>

        </script>



        <ol id="zy_uploaded_medias_ol" class="zy_uploaded_medias_ol">

        <?php

        /*--------获取原来的绑定了的媒体文件，如果存在的情况下====================*/

            $zy_articles_help->zy_get_slide_medias($post_id);

        ?>

        </ol>





    </section>



    <section id="zy_section_right" class="zy_uploader_column_right">

        <header class="zy_section_right_header"><p><b id="zy_media_type">图片</b></p></header>

        <iframe id="zy_media_iframe" name="zy_media_iframe" class="zy_iframe">

        </iframe>

    </section>

	<div class="zy_clear_float"></div>

</article>



<!--第三步(预览)-->



<article id="zy_preview" class="zy_hidden zy_preview">



    <script type="text/template" id="zy_xinjiang_preview_tpl">

        <section class='zy_preview_article_xinjiang'>

            <h1 class='zy_preview_article_title_xinjiang'>${title}</h1>

            <h2 class='zy_preview_author_xinjiang'>${author}</h2>

            <h2 class='zy_preview_category_xinjiang'>${category} | ${date}</h2>

            <div class='zy_preview_abstract_xinjiang'><p>${excerpt}</p></div>

            <div class='zy_preview_allslides_xinjiang'>



                {@each slides as slide}

                <div class="zy_preview_slide_xinjiang">

                    {@if slide.title}



                        {@if slide.memo}

                            <div class="zy_preview_imgcaption_xinjiang">${slide.title}:${slide.memo}</div>

                        {@else}

                            <div class="zy_preview_imgcaption_xinjiang">${slide.title}</div>



                    {@/if}



                    {@else if slide.memo}

                         <div class="zy_preview_imgcaption_xinjiang">${slide.memo}</div>

                    {@/if}



                    $${slide.content}

                </div>

                {@/each}



            </div>

        </section>

    </script>



    <!--媒体文件的缩略图-->

    <section id="zy_preview_content">



    </section>



</article>

</form>



<!--第二步中，输入网络视频的弹出窗口,响应的事件在第二步上传文件的菜单中-->

<?php add_thickbox();//加载thickbox的js库 ?>

<div id="zy_thickbox_id" style="display:none;">

    <label class="zy_network_label">网络视频</label><span class="zy_network_tip">(请使用包含iframe标签的通用代码)</span>

    <div style="margin-top: 30px;">

        <input id="zy_network_input" title="请使用通用代码" class="zy_network_input">

        <input id="zy_network_input_ok" type="button" class="zy_upload_btn" value="确定">

    </div>

</div>

<!--<a href="#TB_inline?width=600&height=550&inlineId=zy_thickbox_id" class="thickbox">View my inline content!</a>-->



<!--第三步中，点击图片要进行媒体文件预览-->

<div id="zy_show_div" style="display:none;">

    <!--这里面一定要有html标签，不然打开的面板会为空-->

    <div>预览内容</div>

</div>