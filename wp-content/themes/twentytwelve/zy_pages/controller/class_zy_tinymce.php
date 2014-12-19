<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 14-10-22
 * Time: 下午2:56
 * To change this template use File | Settings | File Templates.
 */

class Zy_Tinymce {
    public function __construct(){
        add_filter('mce_external_plugins', array($this,'tinymce_plugins'));
    }

    public function tinymce_plugins () {

        $plugins = array('zy_insert_media'); //Add any more plugins you want to load here

        $plugins_array = array();

        //Build the response - the key is the plugin name, value is the URL to the plugin JS
        foreach ($plugins as $plugin ) {
            $plugins_array[ $plugin ] = get_template_directory_uri() . '/tinymce/' . $plugin . '/editor_plugin.js';
        }

        return $plugins_array;
    }
}