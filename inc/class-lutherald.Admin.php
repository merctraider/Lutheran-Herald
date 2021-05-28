<?php 
namespace Lutherald; 

class Admin{
    public static function init(){
        add_action('admin_menu', array(__CLASS__, 'admin_menu'));
    }
    public static function admin_menu(){		
        add_menu_page('Moveable Feasts', 'Moveable Feasts', 'manage_options', 'lutherald-moveable-feasts-editor', array(__CLASS__, 'render_moveable_feasts_editor'),  'dashicons-media-code', 88);
        
    }

    public static function render_moveable_feasts_editor(){
        include plugin_dir_path(__FILE__) . 'calendar/jsoneditor.php';
    }
}