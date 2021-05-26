<?php
namespace Lutherald; 

if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class Widget_Readings extends \WP_Widget{
    //Construct
    function __construct() {
        parent::__construct(
            //ID
            'lutherald_Widget_Readings',

            //Widget Name
            __('Lectionary Readings'),

            array(
				'description' => __('Displays lectionary readings')               
			)
        );
    }
    //Front end
    public function widget( $args, $instance ) {
        //Widget settings 
        $display_header = isset($instance['display_header'])? $instance['display_header'] : 'h2';
        $readings_tag = isset($instance['readings_tag'])? $instance['readings_tag'] : 'h3';
        $display_verse = isset($instance['display_verse'])? $instance['display_verse'] : true;
        $display_devotions = isset($instance['display_devotions'])? $instance['display_devotions'] : false;
        $pagination_position = isset($instance['pagination_position'])? $instance['pagination_position'] : 'bottom';

        //determine which calendar to use
        $current_date = new \DateTime('now');

        if(isset($_GET['date'])){
            try{
                $current_date = new \Datetime($_GET['date']);
                
            } catch (\Exception $e) {
                echo "Invalid date. Showing today's readings instead.";
            }
            
        }

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
        $title = $day_info['display'];

        $first_reading = $day_info['readings'][0];
        $second_reading = $day_info['readings'][1];
        $color = $day_info['color'];
        echo '<div class="tlh-lectionary">';
            if($pagination_position === 'top'){
                $this->draw_pagination($current_date);
            }

            echo "<$display_header>$title</$display_header>";
            echo '<span class=' . $color . '>' . 'Liturgical Color: '. \ucfirst($color) .'</span>';

            echo "<$readings_tag>First Reading: $first_reading</$readings_tag>";
            if($display_verse){
                echo '<p>'. BibleGateway::get_verse($first_reading) . '</p>';
            }

            echo "<$readings_tag>Second Reading: $second_reading</$readings_tag>";
            if($display_verse){
                echo '<p>' . BibleGateway::get_verse($second_reading) . '</p>';
            }

            if($display_devotions){
                $devotions = BibleGateway::get_devotions($current_date);
                if($devotions != false){
                    echo "<$readings_tag>Devotion from the Lutheran Herald</$readings_tag>";
                    echo "<p>$devotions</p>";
                }
                
            }

            if($pagination_position === 'bottom'){
                $this->draw_pagination($current_date);
            }
        echo '</div>';

        
    }

    public function draw_pagination($current_date){
        $url = strtok($_SERVER["REQUEST_URI"], '?');
            $tomorrow = clone $current_date;
            $tomorrow->modify('tomorrow');
            $yesterday = clone $current_date;
            $yesterday->modify('yesterday');
            echo '<div class="pagination">';
            echo '<div class="nav-previous">'. '<a href="'. $url . '?date=' . $yesterday->format('Y-m-d') .'">&larr;'. $yesterday->format('M d Y') .'</a>' . '</div>';
            echo '<div class="current">'. $current_date->format('M d Y') . '</div>';
            echo '<div class="nav-next">' . '<a href="'. $url . '?date=' . $tomorrow->format('Y-m-d') .'">'. $tomorrow->format('M d Y') .'&rarr;</a>' . '</div>';
            echo '</div>';

    }

    // Creating widget Backend 
    public function form( $instance ) {
        $display_header = isset($instance['display_header'])? $instance['display_header'] : 'h2';

        $display_header_field = new \Field($this->get_field_name( 'display_header' ), 'First Header Level', $display_header);

        $heading_level_args = [
            'h1'=>'h1',
            'h2'=>'h2',
            'h3'=>'h3',
            'h4'=>'h4',
            'h5'=>'h5',
            'h6'=>'h6',
            'p'=>'p'
        ];

        $display_header_field->draw_dropdown($heading_level_args);


        $readings_tag = isset($instance['readings_tag'])? $instance['readings_tag'] : 'h3';
        $readings_tag_field = new \Field($this->get_field_name('readings_tag'), 'Readings Heading Tag', $readings_tag);
        $readings_tag_field->draw_dropdown($heading_level_args);

        $display_verse = isset($instance['display_verse'])? $instance['display_verse'] : true;
        $display_verse_field = new \Field($this->get_field_name( 'display_verse' ), 'Display Bible Verses?', $display_verse);
        $display_verse_field->draw_checkbox();

        $display_devotions = isset($instance['display_devotions'])? $instance['display_devotions'] : false;
        $display_devotions = new \Field($this->get_field_name( 'display_devotions' ), 'Display Lutheran Herald Devotions?', $display_verse);
        $display_devotions->draw_checkbox();

        $pagination_position = isset($instance['pagination_position'])? $instance['pagination_position'] : 'bottom';
        $pagination_position_field  = new \Field($this->get_field_name( 'pagination_position' ), 'Pagination Position', $pagination_position);
        $pagination_position_field->draw_dropdown(
            array(
                'top'=>'Top',
                'bottom'=>'Bottom',
                'none'=>'None'
            )
        ); 




    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['display_header'] = $new_instance['display_header'];  
        $instance['readings_tag'] = $new_instance['readings_tag'];
        $instance['pagination_position'] = $new_instance['pagination_position'];
        $instance['display_verse'] = ! empty( $new_instance['display_verse'] ) ? true : false;
        $instance['display_devotions'] = ! empty( $new_instance['display_devotions'] ) ? true : false;
        return $instance;
    }


}