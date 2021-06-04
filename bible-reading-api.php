<?php 

if(isset($_GET['lookup'])){
    require_once plugin_dir_path(__FILE__) . 'inc/class-lutherald-BibleGateway.php';
    echo lutherald\BibleGateway::get_verse($_GET['lookup']); 
} else {
    exit('No Verse Given');
}