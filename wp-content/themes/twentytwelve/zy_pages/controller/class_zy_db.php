<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-5
 * Time: 上午10:13
 * 插入自定义的数据表格，最好是放在插件中，以免每次启动的时候都执行action
 */

class Zy_Db {
    public function __construct() {

        add_action( 'admin_init', array( $this, 'insert_all_table' ) );
    }
    public function insert_pack_table(){
        global $wpdb,$jal_db_version;
        $jal_db_version="1.0";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_name=$wpdb->prefix."pack_ids";
        if($wpdb->get_var("show tables like '$table_name'")!=$table_name){
            $sql="CREATE TABLE  ".$table_name." (post_id bigint(20) PRIMARY KEY NOT NULL,
            pack_time tinytext,
            pack_lock int DEFAULT 0 NOT NULL
            ) DEFAULT CHARSET=utf8;";

            dbDelta( $sql );

            add_option( "jal_db_version", $jal_db_version );
        }
    }
    public function insert_logs_table(){
        global $wpdb,$jal_db_version;
        $jal_db_version="1.0";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_name=$wpdb->prefix."logs";
        if($wpdb->get_var("show tables like '$table_name'")!=$table_name){
            $sql="CREATE TABLE ".$table_name." (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `type` varchar(32) NOT NULL,
            `level` char(10) NOT NULL,
            `message` varchar(2048) NOT NULL,
            `log_time` datetime NOT NULL,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";

            dbDelta( $sql );

            add_option( "jal_db_version", $jal_db_version );
        }
    }
    public function insert_posts_view(){
        global $wpdb;
        $posts_view=$wpdb->prefix."posts_view";
        if($wpdb->get_var("show tables like '$posts_view'")!=$posts_view){

            $sql="create view $posts_view as SELECT p.ID AS post_id,p.post_title,p.post_excerpt,p.post_date,
            p.post_mime_type,m.meta_value AS thumb,u.display_name,c.term_id FROM $wpdb->posts AS p,
            $wpdb->users AS u,$wpdb->term_relationships AS s,$wpdb->postmeta AS m,$wpdb->term_taxonomy AS t,
            $wpdb->terms AS c WHERE  t.term_id=c.term_id AND s.term_taxonomy_id=t.term_taxonomy_id AND s.object_id=p.ID
            AND p.post_author=u.ID AND p.post_status='publish' AND m.post_id=p.ID AND m.meta_key='zy_thumb' ORDER BY p.post_date DESC;";

            $wpdb->query( $sql );
        }
    }
    public function insert_all_table(){
        $this->insert_pack_table();
        $this->insert_logs_table();
        $this->insert_posts_view();
    }
}