<?php
namespace Lutherald; 

if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class Widget_Readings extends \WP_Widget{
    //Construct
    function __construct() {
        parent::__construct(
            //ID
            'FBAlbum_widget',

            //Widget Name
            __('Lectionary Readings'),

            array(
				'description' => __('Displays lectionary readings')               
			)
        );
    }
    //Front end
    public function widget( $args, $instance ) {
        //determine which calendar to use
        $current_date = new \DateTime('now');
        $year=$current_date->format('Y');

        $last_year = new ChurchYear($year-1);
        $this_year = new ChurchYear($year);
        $calendar_to_use = null;

        if($last_year->find_season($current_date)!= false){
            $calendar_to_use = $last_year;
        } 

        if($this_year->find_season($current_date) != false){
            $calendar_to_use = $this_year;
        }


        $day_info = $calendar_to_use->retrieve_day_info($current_date);

        ?>
        <div>
        <h2><?php echo $day_info['display']?></h2>

        <h3>First Reading: <?php echo $day_info['readings'][0];?></h3>
        <?php 
            echo BibleGateway::get_verse($day_info['readings'][0]);
        ?> 
        <h3>Second Reading: <?php echo $day_info['readings'][1];?></h3>
        <?php 
            echo BibleGateway::get_verse($day_info['readings'][1]);
        ?> 

        
        </div>
        <?php

    }

    // Creating widget Backend 
    public function form( $instance ) {

    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance; 
        return $instance;
    }


}