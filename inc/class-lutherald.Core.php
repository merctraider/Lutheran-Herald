<?php

namespace Lutherald;
if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');
class Core{
    public static function after_setup_theme(){
        add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue'));
        add_action('widgets_init', array(__CLASS__, 'widgets_init'));
    }

    // Register all the widgets 
    public static function widgets_init(){

    }

    public static function enqueue(){
        wp_enqueue_style('lutherald', plugin_dir_url(LUTHERALD_PLUGIN_DIR . 'css/style.css').'style.css');
    }
}