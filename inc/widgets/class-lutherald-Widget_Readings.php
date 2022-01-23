<?php

namespace Lutherald;

if (!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class Widget_Readings extends \WP_Widget
{
    //Construct
    function __construct()
    {
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
    public function widget($args, $instance)
    {
        
        wp_enqueue_script('bibleretriever', LUTHERALD_PLUGIN_URL  . 'inc/js/bibleretriever.js', ['jquery']);
        //Widget settings 
        $display_header = isset($instance['display_header']) ? $instance['display_header'] : 'h2';
        $readings_tag = isset($instance['readings_tag']) ? $instance['readings_tag'] : 'h3';
        $display_verse = isset($instance['display_verse']) ? $instance['display_verse'] : true;
        $display_devotions = isset($instance['display_devotions']) ? $instance['display_devotions'] : false;
        $pagination_position = isset($instance['pagination_position']) ? $instance['pagination_position'] : 'bottom';

        //determine which calendar to use
        $current_date = new \DateTime('now');

        if (isset($_GET['date'])) {
            try {
                $current_date = new \Datetime($_GET['date']);
            } catch (\Exception $e) {
                echo "Invalid date. Showing today's readings instead.";
            }
        }

        //Create a new Church Year 
        $calendar_to_use = ChurchYear::create_church_year($current_date);
       
        $day_info = $this->get_day_info($current_date, $calendar_to_use);
        echo '<script id="lection-data" type="application/json">' . \json_encode($day_info) . '</script>';

        $title = $day_info['display'];

        $first_reading = $day_info['readings'][0];
        $second_reading = $day_info['readings'][1];
        $color = $day_info['color'];
        echo '<div class="tlh-lectionary">';
        if ($pagination_position === 'top') {
            $this->draw_pagination($current_date);
        }

        $feast_day = $calendar_to_use->get_festival($current_date);
        
        if( $feast_day != false){
            echo '<p><script id="festival-data" type="application/json">' . \json_encode($feast_day) . '</script>';
            echo '<a id="festival-loader" href="#">Load readings for '. $feast_day['display'] .'</a> </p>';
        }

        echo "<$display_header". ' id="display" ' .">$title</$display_header>";
        if(array_key_exists('hymn', $day_info)){
            echo '<span><strong>Chief Hymn:</strong> ' . BibleGateway::get_hymn($day_info['hymn']) . '</span>';
        }
        
        echo '<p><span class=' . $color . ' id="color">' . 'Liturgical Color: ' . \ucfirst($color) . '</span></p>';
        
        //Introit
        echo '<div id="introit">';
        if(array_key_exists('introit', $day_info)){
            $introit = $day_info['introit'];
            echo "<$readings_tag>Introit</$readings_tag>";
            echo "<div>$introit</div>";
        } 

        //Psalm readings
        $psalm_readings =[];
        
        //Check for the appointed psalm for the week
        if(array_key_exists('psalm', $day_info)){
            $psalm_readings = $day_info['psalm'];
            
        }

        //Daily psalter
        if (array_key_exists('daily_psalter', $day_info)){
            $daily_psalter = $day_info['daily_psalter']; 
            $psalm_readings = array_merge($psalm_readings, $daily_psalter);
        }

        //Psalms div 
        echo '<p>';
        //Get the psalm reading for the day with the assembled psalm
        $this->render_psalms($psalm_readings, $readings_tag);
        echo '</p>';


        echo '</div>';


        //First Reading
        echo '<div id="first-reading">';
        echo "<$readings_tag>First Reading: $first_reading</$readings_tag>";
        if ($display_verse) {
            echo '<p>' . BibleGateway::get_verse($first_reading) . '</p>';
        } else {
            echo '<p data-bible="'. $first_reading . '">Loading verse...</p>';
        }
        echo '</div>';
        
        //Gradual, usually on Sundays or on Feasts
        echo '<div id="gradual">';
        if(array_key_exists('gradual', $day_info)){
            $gradual = $day_info['gradual'];
            echo "<$readings_tag>Gradual</$readings_tag>";
            echo "<div>$gradual</div>";
        }
        echo '</div>';

        //Second Reading
        echo '<div id="second-reading">';
        echo "<$readings_tag>Second Reading: $second_reading</$readings_tag>";
        if ($display_verse) {
            echo '<p>' . BibleGateway::get_verse($second_reading) . '</p>';
        } else {
            echo '<p data-bible="'. $second_reading . '">Loading verse...</p>';
        }
        echo '</div>';

        //Collect
        echo '<div id="collect">';
        if(array_key_exists('collect', $day_info)){
            $collect = $day_info['collect']; 
            echo "<$readings_tag>Collect</$readings_tag>";
            echo "<p>$collect</p>";
        }
        echo '</div>';

        //Lutheran Herald stuff
        if ($display_devotions) {
            echo "<$readings_tag>Devotion from the Lutheran Herald</$readings_tag>";
            echo '<div data-date="'. $current_date->format('Y-m-d'). '">The Devotion readings for today are not out yet. Check back later.</div>';
        }
        if(BibleGateway::$version === 'NKJV'){
            echo '<p><i>Scripture taken from the New King James Version®. Copyright © 1982 by Thomas Nelson. Used by permission. All rights reserved.</i></p>';
        }
        
        if ($pagination_position === 'bottom') {
            $this->draw_pagination($current_date);
        }
        echo '</div>';
    }

    public function render_psalms($array, $readings_tag){
        echo "<$readings_tag" . ' id="psalter-title"'. ">Psalm Readings</$readings_tag>";
        echo '<div id="psalter-selection">';
        
        //Matins
        echo '<p> <strong>Matins: </strong>';

        //Weekly psalms
        if(array_key_exists('matins', $array)){
            echo '<b><em>';
            $matin_psalm = $array['matins'];
            //Check if there are multiple psalms 
            if(is_array($matin_psalm))
            {
                foreach($matin_psalm as $psalm){
                    echo '<a href="#">' . $psalm . '</a> ';
                }
            } else {
                //Just output the appointed psalm
                echo '<a href="#">' . $matin_psalm . '</a> ';

            }
            echo '</em></b>';
        }

        //Daily psalms
        if(array_key_exists('morning', $array)){
            foreach($array['morning'] as $psalm){
                //If it's just a number, add to it
                if(\is_numeric($psalm)){
                    $psalm = "Psalm $psalm";
                    //Create button 
                    echo '<a href="#">' . $psalm . '</a> ';
                    continue;
                }
                $psalm = $psalm; 
                echo '<a href="#">' . $psalm . '</a> ';
    
            }
        }
        
        echo '</p>';

        //Vespers
        echo '<p> <strong>Vespers: </strong>';

         //Weekly psalms
         if(array_key_exists('vespers', $array)){
            echo '<b><em>';
            $vespers_psalm = $array['vespers'];
            //Check if there are multiple psalms 
            if(is_array($vespers_psalm))
            {
                foreach($vespers_psalm as $psalm){
                    echo '<a href="#">' . $psalm . '</a> ';
                }
            } else {
                //Just output the appointed psalm
                echo '<a href="#">' . $vespers_psalm . '</a> ';

            }
            echo '</em></b>';
        }

        if(array_key_exists('evening', $array)){
            foreach($array['evening'] as $psalm){
                //If it's just a number, add to it
                if(\is_numeric($psalm)){
                    $psalm = "Psalm $psalm";
                    //Create button 
                    echo '<a href="#">' . $psalm . '</a> ';
                    continue;
                }
                $psalm = $psalm; 
                echo '<a href="#">' . $psalm . '</a> ';
            }
        }
        
        echo '</p>';

        echo '</div>';

        echo '<div id="psalm-display"><p></p></div>';
    }

    public function get_day_info($current_date, $calendar_to_use)
    {

        $date_string = $current_date->format('Y-m-d');

        /*$cache_path = dirname(__FILE__) . '/cache.json';

        $cached_array = [];
        if (file_exists($cache_path)) {
            $json = file_get_contents($cache_path);
            $cached_array = json_decode($json, true);
            if (key_exists($date_string, $cached_array)) {
                $day_info = $cached_array[$date_string];
                return $day_info;
            }
        }*/


        
        $day_info = $calendar_to_use->retrieve_day_info($current_date);
        if(!array_key_exists('introit', $day_info)){
            $day_info['daily_psalter'] = $calendar_to_use->get_monthly_psalter($current_date);
        }
        //Cache it
        /*$cache_file_handler = fopen($cache_path, 'w');
        $cached_array[$date_string] = $day_info;
        $json = json_encode($cached_array);
        fwrite($cache_file_handler, $json);
        fclose($cache_file_handler);*/

        return $day_info;
    }
    

    public function draw_pagination($current_date)
    {
        $url = strtok($_SERVER["REQUEST_URI"], '?');
        $tomorrow = clone $current_date;
        $tomorrow->modify('tomorrow');
        $yesterday = clone $current_date;
        $yesterday->modify('yesterday');
        echo '<div class="pagination">';
        echo '<div class="nav-previous">' . '<a href="' . $url . '?date=' . $yesterday->format('Y-m-d') . '">&larr;' . $yesterday->format('M d Y') . '</a>' . '</div>';
        echo '<div class="current">' . $current_date->format('M d Y') . '</div>';
        echo '<div class="nav-next">' . '<a href="' . $url . '?date=' . $tomorrow->format('Y-m-d') . '">' . $tomorrow->format('M d Y') . '&rarr;</a>' . '</div>';
        echo '</div>';
    }

    // Creating widget Backend 
    public function form($instance)
    {
        $display_header = isset($instance['display_header']) ? $instance['display_header'] : 'h2';

        $display_header_field = new \Field($this->get_field_name('display_header'), 'First Header Level', $display_header);

        $heading_level_args = [
            'h1' => 'h1',
            'h2' => 'h2',
            'h3' => 'h3',
            'h4' => 'h4',
            'h5' => 'h5',
            'h6' => 'h6',
            'p' => 'p'
        ];

        $display_header_field->draw_dropdown($heading_level_args);


        $readings_tag = isset($instance['readings_tag']) ? $instance['readings_tag'] : 'h3';
        $readings_tag_field = new \Field($this->get_field_name('readings_tag'), 'Readings Heading Tag', $readings_tag);
        $readings_tag_field->draw_dropdown($heading_level_args);

        $display_verse = isset($instance['display_verse']) ? $instance['display_verse'] : true;
        $display_verse_field = new \Field($this->get_field_name('display_verse'), 'Load Bible Verses syncronously?', $display_verse);
        $display_verse_field->draw_checkbox();

        $display_devotions = isset($instance['display_devotions']) ? $instance['display_devotions'] : false;
        $display_devotions = new \Field($this->get_field_name('display_devotions'), 'Display Lutheran Herald Devotions?', $display_verse);
        $display_devotions->draw_checkbox();

        $pagination_position = isset($instance['pagination_position']) ? $instance['pagination_position'] : 'bottom';
        $pagination_position_field  = new \Field($this->get_field_name('pagination_position'), 'Pagination Position', $pagination_position);
        $pagination_position_field->draw_dropdown(
            array(
                'top' => 'Top',
                'bottom' => 'Bottom',
                'none' => 'None'
            )
        );
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['display_header'] = $new_instance['display_header'];
        $instance['readings_tag'] = $new_instance['readings_tag'];
        $instance['pagination_position'] = $new_instance['pagination_position'];
        $instance['display_verse'] = !empty($new_instance['display_verse']) ? true : false;
        $instance['display_devotions'] = !empty($new_instance['display_devotions']) ? true : false;
        return $instance;
    }
}
