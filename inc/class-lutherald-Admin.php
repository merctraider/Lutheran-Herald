<?php 
namespace Lutherald; 

class Admin{
    public static function init(){
        add_action('admin_menu', array(__CLASS__, 'admin_menu'));
    }
    public static function admin_menu(){		
        add_menu_page('Moveable Feasts', 'Moveable Feasts', 'manage_options', 'fb-album-admin-menu', array('\FBAlbum\Settings', 'render_admin_menu_page'),  'dashicons-facebook', 88);
        
    }
}