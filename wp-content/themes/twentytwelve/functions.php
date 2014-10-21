<?php
/**
 * Twenty Twelve functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook.
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

/**
 * Sets up the content width value based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 625;

/**
 * Sets up theme defaults and registers the various WordPress features that
 * Twenty Twelve supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_setup() {
	/*
	 * Makes Twenty Twelve available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Twelve, use a find and replace
	 * to change 'twentytwelve' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'twentytwelve', get_template_directory() . '/languages' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme supports a variety of post formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'twentytwelve' ) );

	/*
	 * This theme supports custom background color and image, and here
	 * we also set up the default background color.
	 */
	add_theme_support( 'custom-background', array(
		'default-color' => 'e6e6e6',
	) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	//set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
}
add_action( 'after_setup_theme', 'twentytwelve_setup' );

/**
 * Adds support for a custom header image.
 */
require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Enqueues scripts and styles for front-end.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_scripts_styles() {
	global $wp_styles;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/*
	 * Adds JavaScript for handling the navigation menu hide-and-show behavior.
	 */
	wp_enqueue_script( 'twentytwelve-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '1.0', true );

	/*
	 * Loads our special font CSS file.
	 *
	 * The use of Open Sans by default is localized. For languages that use
	 * characters not supported by the font, the font can be disabled.
	 *
	 * To disable in a child theme, use wp_dequeue_style()
	 * function mytheme_dequeue_fonts() {
	 *     wp_dequeue_style( 'twentytwelve-fonts' );
	 * }
	 * add_action( 'wp_enqueue_scripts', 'mytheme_dequeue_fonts', 11 );
	 */

	/* translators: If there are characters in your language that are not supported
	   by Open Sans, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'twentytwelve' ) ) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language, translate
		   this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language. */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'twentytwelve' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => 'Open+Sans:400italic,700italic,400,700',
			'subset' => $subsets,
		);
		wp_enqueue_style( 'twentytwelve-fonts', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
	}

	/*
	 * Loads our main stylesheet.
	 */
	wp_enqueue_style( 'twentytwelve-style', get_stylesheet_uri() );

	/*
	 * Loads the Internet Explorer specific stylesheet.
	 */
	wp_enqueue_style( 'twentytwelve-ie', get_template_directory_uri() . '/css/ie.css', array( 'twentytwelve-style' ), '20121010' );
	$wp_styles->add_data( 'twentytwelve-ie', 'conditional', 'lt IE 9' );
}
add_action( 'wp_enqueue_scripts', 'twentytwelve_scripts_styles' );

/**
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since Twenty Twelve 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function twentytwelve_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentytwelve' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'twentytwelve_wp_title', 10, 2 );

/**
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'twentytwelve_page_menu_args' );

/**
 * Registers our main widget area and the front page widget areas.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Main Sidebar', 'twentytwelve' ),
		'id' => 'sidebar-1',
		'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'First Front Page Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-2',
		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Second Front Page Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-3',
		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'twentytwelve_widgets_init' );

if ( ! function_exists( 'twentytwelve_content_nav' ) ) :
/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_nav( $html_id ) {
	global $wp_query;

	$html_id = esc_attr( $html_id );

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
			<div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentytwelve' ) ); ?></div>
			<div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>
		</nav><!-- #<?php echo $html_id; ?> .navigation -->
	<?php endif;
}
endif;

if ( ! function_exists( 'twentytwelve_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own twentytwelve_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'twentytwelve' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<header class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 44 );
					printf( '<cite class="fn">%1$s %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span> ' . __( 'Post author', 'twentytwelve' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'twentytwelve' ), get_comment_date(), get_comment_time() )
					);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentytwelve' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'twentytwelve' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'twentytwelve' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

if ( ! function_exists( 'twentytwelve_entry_meta' ) ) :
/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own twentytwelve_entry_meta() to override in a child theme.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Custom fonts enabled.
 * 5. Single or multiple authors.
 *
 * @since Twenty Twelve 1.0
 *
 * @param array Existing class values.
 * @return array Filtered class values.
 */
function twentytwelve_body_class( $classes ) {
	$background_color = get_background_color();

	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
		$classes[] = 'full-width';

	if ( is_page_template( 'page-templates/front-page.php' ) ) {
		$classes[] = 'template-front-page';
		if ( has_post_thumbnail() )
			$classes[] = 'has-post-thumbnail';
		if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
			$classes[] = 'two-sidebars';
	}

	if ( empty( $background_color ) )
		$classes[] = 'custom-background-empty';
	elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
		$classes[] = 'custom-background-white';

	// Enable custom font class only if the font CSS is queued to load.
	if ( wp_style_is( 'twentytwelve-fonts', 'queue' ) )
		$classes[] = 'custom-font-enabled';

	if ( ! is_multi_author() )
		$classes[] = 'single-author';

	return $classes;
}
add_filter( 'body_class', 'twentytwelve_body_class' );

/**
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_width() {
	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() || ! is_active_sidebar( 'sidebar-1' ) ) {
		global $content_width;
		$content_width = 960;
	}
}
add_action( 'template_redirect', 'twentytwelve_content_width' );

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @since Twenty Twelve 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function twentytwelve_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
add_action( 'customize_register', 'twentytwelve_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_customize_preview_js() {
	wp_enqueue_script( 'twentytwelve-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20120827', true );
}
add_action( 'customize_preview_init', 'twentytwelve_customize_preview_js' );



/*--------------------------------------------------自定义功能代码===============================================*/


/*============================================加载自定义资源=====================================================*/
include(get_template_directory()."/zy_pages/controller/class_zy_resource.php");
$resource=new Zy_Resource();


/*============================================添加自定义菜单=====================================================*/
/*
 * 添加文章菜单栏下“幻灯片”菜单,添加设置菜单栏“打包数据”菜单
 * */
include(get_template_directory()."/zy_pages/controller/class_zy_menu.php");
add_action("admin_menu",array("Zy_Menu","add_all_menu"));

/*
 * 自定义数据表,如果作为插件的话只在插件启用的时候创建表格,
 * register_activation_hook( __FILE__,'insert_own_table');
 * 写在这里每次都会执行，效率不高，最好写到插件中
 * 主要是保存打包的id
 * */
include(get_template_directory()."/zy_pages/controller/class_zy_db.php");
$db=new Zy_Db();


/*----------------------------------------------添加音乐文章类型--------------------------------------------*/
//引入类,此类中还引入了zy_common_class类,此类会在init的时候初始化，所以所有的页面其实都引入了这个类和common类
include(get_template_directory()."/zy_pages/controller/class_zy_music.php");
$zy_music=new Zy_Music();


/*
 * 删除时的处理函数
 * */
function zy_music_delete($post_id){
    $zy_music=new zy_music_class();
    if(!$zy_music->zy_music_delete($post_id)){
        return false;
    }
    return true;
}
/*
 * 保存文件
 * */
//add_action("publish_zy_music",array("zy_music_class","zy_music_save"));


/*===============================================================图文混排页面代码===============================*/
//添加文章展现形式
/*---------------------------------------------------添加右边栏输入项部分-------------------------------------------*/
/*
 *添加字段到图文混排页面右边
 * */
function zy_add_box(){
    include(get_template_directory()."/zy_pages/view/class_zy_box.php");

    //add_meta_box("zy_thumb_id","缩略图",array($zy_post_box,'zy_post_thumb_box'),'post','side');
    add_meta_box("zy_thumb_id","缩略图",array("Zy_box",'zy_post_thumb_box'),'post','side');
    add_meta_box("zy_background_id","背景",array("Zy_box",'zy_post_background_box'),'post','side');
}
add_action("add_meta_boxes",'zy_add_box');

/*--------------------------------------------------------图文混排保存数据部分---------------------------------*/
global $zy_post_save;
/*
 * 保存媒体文件
 * */
function zy_save_medias($post_id){
    global $zy_post_save;
    //引入类，必须在这一类函数的第一个执行的函数中引入，不然后面的类无法使用对象
    include(get_template_directory()."/zy_pages/zy_articles_save_class.php");
    $zy_post_save=new zy_articles_save_class();

    $new_medias=$_POST["zy_medias"];

    if(isset($_POST["zy_old_medias"])){
        //判断是否为修改
       if(!$zy_post_save->zy_edit_save_medias($post_id,$new_medias)){
           return false;
       }
    }else{
        //判断为新增
       if(!$zy_post_save->zy_new_save_medias($post_id,$new_medias)){
           return false;
       }
    }
    //返回值
    return true;
};
/*
 * 存储缩略图数据函数
 * */
function zy_save_thumb($post_id){
    global $zy_post_save;
    $filename=$_POST["zy_thumb"];

    //分为新建和修改两种类型
    if(isset($_POST["zy_old_thumb"])){
        $old_filename=$_POST["zy_old_thumb"];
        //如果是修改了文件
        if(!$zy_post_save->zy_edit_save_thumb($post_id,$filename,$old_filename)){
            return false;
        }
    }else{
        if(!$zy_post_save->zy_new_save_thumb($post_id,$filename)){
            return false;
        }
    }

    //返回值,让
    return true;
}
/*
 * 存储背景图数据函数
 * */
function zy_save_background($post_id){
    global $zy_post_save;
    $filename=$_POST["zy_background"];

    //分为新建和修改两种类型
    if(isset($_POST["zy_old_background"])){
        $old_filename=$_POST["zy_old_background"];
        //如果是修改了文件
        if(!$zy_post_save->zy_edit_save_background($post_id,$filename,$old_filename)){
            return false;
        }
    }else{
        if(!$zy_post_save->zy_new_save_background($post_id,$filename)){
            return false;
        }
    }

    //返回值,让
    return true;
}

/*
 * 保存自定义数据,所有的数据在一个函数保存
 * */
function zy_data_save( $post_id ) {
    global $wpdb;
    $post=get_post($post_id);

    /*
     * 需要判断是图文混排还是幻灯片，因为幻灯片的wp_insert_post也会出发publish_post
     * */
    if(strpos($post->post_mime_type,"zyslide")===false&&isset($_POST["zy_thumb"])){

        //设置页面编码
        header("content-type:text/html;charset=utf-8");

        /*存储媒体文件数据*/
        if(!zy_save_medias($post_id)){
            //提示错误
            die("保存媒体数据出错，请联系开发人员");
        }

        /*存储缩略图数据*/
        if(!zy_save_thumb($post_id)){
            //提示错误
            die("保存缩略图数据出错，请联系开发人员");
        }
        /*存储背景数据*/
        if(!zy_save_background($post_id)){
            //提示错误
            die("保存背景数据出错，请联系开发人员");
        }

        //删除临时存储文件夹
        /*global $user_ID;
        $target_dir=wp_upload_dir();
        $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
        if(is_dir($target_dir)){
            zy_common_class::zy_deldir($target_dir);
        }*/

        //保存打包数据
        $tablename=$wpdb->prefix."pack_ids";
        if(count($wpdb->get_col("SELECT post_id FROM $tablename WHERE post_id=$post_id"))){
            //存在的情况下，修改
            if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
                die("保存打包数据出错，请联系开发人员");
            }
        }else{
            //不存在的情况下新增
            if(!$wpdb->insert($wpdb->prefix."pack_ids",array("post_id"=>$post_id),array("%d"))){
                die("保存打包数据出错，请联系开发人员");
            }
        }

        //更新发布时间
        $post_date=current_time('mysql');
        $wpdb->update($wpdb->posts, array("post_date"=>$post_date,"post_date_gmt"=>date("Y-m-d H:i:s")), array("ID"=>$post_id));
    }
}
add_action('publish_post', 'zy_data_save');
//add_action('pre_post_update','zy_data_save');

/*===========================================处理ajax部分====================================*/
//引入类
include(get_template_directory()."/zy_pages/controller/class_zy_ajax.php");
/*
 * 处理文件上传的ajax函数
 * */
add_action('wp_ajax_uploadfile', array("Zy_Ajax",'zy_action_uploadfile'));
//火狐里面这个地方不会带登陆标志过来，需要加下面这句或者前台上传插件使用html5引擎
//add_action('wp_ajax_nopriv_uploadfile', array("zy_ajax_class",'zy_action_uploadfile'));

/*
 * 打包程序接口,ajax请求，告知wordpress打包是否成功
 * */
//无需登陆，即可使用
add_action("wp_ajax_nopriv_zy_pack_unlock",array("zy_ajax_class","zy_pack_unlock_callback"));
add_action("wp_ajax_zy_pack_unlock",array("zy_ajax_class","zy_pack_unlock_callback"));

/*
 * 获取音乐
 * */
//无需登陆，即可使用
add_action("wp_ajax_zy_get_music",array("zy_ajax_class","zy_get_music"));
add_action("wp_ajax_nopriv_zy_get_music",array("zy_ajax_class","zy_get_music"));

/*
 * 获取分类型文章
 * */
add_action("wp_ajax_zy_get_posts",array("zy_ajax_class","zy_get_posts"));
add_action("wp_ajax_nopriv_zy_get_posts",array("zy_ajax_class","zy_get_posts"));


/*
 * 获取首页置顶文章
 * */
add_action("wp_ajax_zy_get_top_posts",array("zy_ajax_class","zy_get_top_posts"));
add_action("wp_ajax_nopriv_zy_get_top_posts",array("zy_ajax_class","zy_get_top_posts"));

/*
 * 获取项目中的分类
 * */
add_action("wp_ajax_zy_get_categories",array("zy_ajax_class","zy_get_categories"));
add_action("wp_ajax_nopriv_zy_get_categories",array("zy_ajax_class","zy_get_categories"));

/*
 * 获取文章详情
 * */
add_action("wp_ajax_zy_get_post_detail",array("zy_ajax_class","zy_get_post_detail"));
add_action("wp_ajax_nopriv_zy_get_post_detail",array("zy_ajax_class","zy_get_post_detail"));

/*------------------------------------------自定义tinymce插件部分-------------------------------------------*/
/*
 * 添加自定义的tinymce插件
 * */
//添加tinyMCE插件函数
function zy_tinymce_plugins () {

    $plugins = array('zy_insert_media'); //Add any more plugins you want to load here

    $plugins_array = array();

    //Build the response - the key is the plugin name, value is the URL to the plugin JS
    foreach ($plugins as $plugin ) {
        $plugins_array[ $plugin ] = get_template_directory_uri() . '/tinymce/' . $plugin . '/editor_plugin.js';
    }

    return $plugins_array;
}
add_filter('mce_external_plugins', 'zy_tinymce_plugins');


/*
 * 禁用图文混排的自动保存草稿和修订版本
 * */

//取消保存修订版本，这个是在defalut-filters中加了一个action，在保存文章之前，先保存修订版本
remove_action("pre_post_update","wp_save_post_revision");
//禁用自动保存草稿
function zy_disable_autosave(){
    wp_deregister_script("autosave");
}
add_action("wp_print_scripts","zy_disable_autosave");
//删除数据库多余的记录
function zy_delete_autodraft($post_id){
    global $wpdb;
    //在发布文章的时候删除掉除自己外的其他垃圾文章，除自己外是因为当没填写任何内容发布时，状态也是auto-draft
    $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'");
}

add_action("publish_post","zy_delete_autodraft");


/*===========================================================文章锁定的控制==================================*/
/*
 * 文章处于锁定阶段的判断
 * */
function zy_check_lock($post_id){
    global $wpdb;
    $tablename=$wpdb->prefix."pack_ids";
    $zy_pack=$wpdb->get_row("SELECT * FROM $tablename WHERE post_id=$post_id");
    if($zy_pack->pack_time){
        if(time()-$zy_pack->pack_time<1800&&$zy_pack->pack_lock==0){

            //打包时间在30分钟内，并且还没有设置打包标志为1的需要锁定
            header("content-type:text/html; charset=utf-8");
            die("文章正在被打包，请稍后进行操作，<a href='javascript:history.back()'>返回</a>进行其他操作");
        }
    }

    //如果提交的edit_lock和数据库中保存的不一样，那么要阻止提交
    $current_edit_lock=get_post_meta($post_id,"_edit_lock",true);
    $edit_lock=$_POST["_edit_lock"];
    if($current_edit_lock!=$edit_lock&&$edit_lock){
        header("content-type:text/html; charset=utf-8");
        die("其他人以先于你提交更改，请重新编辑后再提交，<a href='".site_url()."/wp-admin/edit.php'>返回</a>");
    }

}
add_action("pre_post_update","zy_check_lock");


/*===================================================数据清理=====================================*/

/**
 * 清除上传时产生的临时文件
 */
function zy_delete_tmp(){
    global $user_ID;
    $currentTimeS=time();
    $target_dir=wp_upload_dir();
    $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
    if(is_dir($target_dir)){
        $fileTimeS=filemtime($target_dir);
        if($currentTimeS-$fileTimeS>12*60*60){
            zy_common_class::zy_deldir($target_dir);
        }
    }
}
add_action("admin_init","zy_delete_tmp");

/*
 * 移入回收站的操作,通知打包程序删除文章
 * 不进行页面报错
 * */
function zy_trash_post($post_id){

    header("content-type:text/html; charset=utf-8");
    //只有文章和幻灯片才发送请求去打包程序
    if(get_post($post_id)->post_type=="post"){
        //发送数据给打包程序，删除zip包
        $url=get_site_url()."/bundle-app/removeBundle";
        $zy_http_result=false;

        for($i=0;$i<3;$i++){
            if(zy_common_class::zy_http_send($post_id,$url)){
                $zy_http_result=true;
                break;//跳出循环
            }
        }

        //设置数据库的值
        global $wpdb;
        if($zy_http_result){
            if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
                die("重置打包数据库失败，请将文章id".$post_id."告诉开发人员！");
            }
        }else{
            die("删除打包文件失败，请将文章id".$post_id."告诉开发人员！");
        }
    }
}
add_action('trashed_post','zy_trash_post');

/*
 * 删除时的操作函数
 * */
function zy_delete_post($post_id){
    //设置页面编码
    header("content-type:text/html; charset=utf-8");
    global $wpdb;
    if(get_post($post_id)->post_type=="zy_music"){

        //如果是音乐类型
        if(!zy_music_delete($post_id)){
            die("删除音乐文件失败，请将音乐id".$post_id."告诉开发人员！");
        }

    }else if(get_post($post_id)->post_type=="post"){
        //如果是文章或者幻灯片类型（post）
        $targetDir=wp_upload_dir();


        /*不管删除打包文件是否成功，都删除服务器的内容*/
        //删除打包表中的数据
        $sql_result=$wpdb->delete($wpdb->prefix."pack_ids",array("post_id"=>$post_id));
        $delete_file_result=true;
        if(is_dir($targetDir["basedir"]."/".$post_id)){

            //这里删除可能不会成功，所以出错后应该手动删除文件夹
            $delete_file_result=zy_common_class::zy_deldir($targetDir["basedir"]."/".$post_id);
        }

        //如果成功删除媒体文件夹
        if(!$delete_file_result||$sql_result===false){
            die("删除文件或者打包数据表记录失败，请将文章id".$post_id."告诉开发人员！");
        }

    }

}
add_action('deleted_post', 'zy_delete_post');
//add_action('delete_post',"zy_delete_post");

//删除之前判断文章是否在锁定期
add_action("before_delete_post","zy_check_lock");


/*================================================修改时的跳转===========================================*/
/*
 * 控制文章显示后的修改链接跳转。
 * */
function zy_page_template_redirect(){
    if(isset($_GET["post"])){
        $post_id=$_GET["post"];
        if(strpos(get_post($post_id)->post_mime_type,"zyslide")!==false){
            wp_redirect(admin_url()."edit.php?page=zy_slide_menu&post_id=$post_id");
            exit();
        }
    }
    //echo $_SERVER["REQUEST_URI"];
}
//hook，在admin的初始化时admin_head,admin_init这两个每次页面都会检测，加重系统负担
add_action( 'add_meta_boxes', 'zy_page_template_redirect' );


/*==================================================添加重写规则，客户端播放视频的页面================*/
function add_zy_rewrite(){

    //添加展示媒体文件的链接地址重写
    add_rewrite_rule('show_media/(\d+)/(\w+)$','index.php?pagename=show_media&zy_post_id=$matches[1]&zy_media_id=$matches[2]','top');
    //如果不加下面两句，wordpress无法识别到自定义的参数
    add_rewrite_tag('%zy_post_id%','([^&]+)');
    add_rewrite_tag('%zy_media_id%','([^&]+)');

    //添加下载音乐的链接地址重写
    add_rewrite_rule('download_music/(\d+)/(.+)$','index.php?pagename=download_music&music_id=$matches[1]','top');
    add_rewrite_tag('%music_id%','([^&]+)');


    //添加展示图片的链接地址重写
    add_rewrite_rule('show_image/(.+)$','index.php?pagename=show_image&zy_image_url=$matches[1]','top');
    add_rewrite_tag('%zy_image_url%','([^&]+)');
}
add_action("init","add_zy_rewrite");



