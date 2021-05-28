<?php

namespace Lutherald;
if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');
class Core{
    public static function after_setup_theme(){
        require_once plugin_dir_path(__FILE__) . 'calendar/class-lutherald-ChurchYear.php';
        require_once plugin_dir_path(__FILE__) . 'class-lutherald-BibleGateway.php';
        require_once plugin_dir_path(__FILE__) . 'class-Field.php';
        add_action('wp_enqueue_scripts',array(__CLASS__,'enqueue'));
        add_action('widgets_init', array(__CLASS__, 'widgets_init'));

        if(is_admin()){
            
        }
    }

    // Register all the widgets 
    public static function widgets_init(){
        require_once plugin_dir_path(__FILE__) . 'widgets/class-lutherald-Widget_Readings.php';
        require_once plugin_dir_path(__FILE__) . 'widgets/class-lutherald-Widget_Lectionary.php';
        require_once plugin_dir_path(__FILE__) . 'widgets/class-lutherald-Widget_Calendar.php';
        register_widget('Lutherald\Widget_Readings');
        register_widget('Lutherald\Widget_Lectionary');
        register_widget('Lutherald\Widget_Calendar');
    }

    public static function enqueue(){
        wp_enqueue_style('lutherald', plugin_dir_url(LUTHERALD_PLUGIN_DIR . 'css/style.css').'style.css');
    }
}