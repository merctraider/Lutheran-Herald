<?php 

namespace Lutherald;
if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');


class Shortcodes{

    public static function init(){
        add_shortcode('introit', array(__CLASS__, 'lectionary_part')); 
        add_shortcode('lection', array(__CLASS__, 'lectionary_part')); 
        add_shortcode('collect', array(__CLASS__, 'lectionary_part')); 
    }

    public static function lectionary_part($atts, $content, $tag){
        wp_enqueue_script('bibleretriever', LUTHERALD_PLUGIN_URL  . 'inc/js/bibleretriever.js', ['jquery']);
        $output = ''; 

        // Attributes
        $atts = shortcode_atts(
            array(
                'show_feast_day' => false,
                'readings_tag' => 'h3'
            ),
            $atts
        );
        $readings_tag = $atts['readings_tag'];

        //determine which calendar to use
         $current_date = new \DateTime('now');

         if (isset($_GET['date'])) {
             try {
                 $current_date = new \Datetime($_GET['date']);
             } catch (\Exception $e) {
                 
             }
         }
        //Create a new Church Year 
        $calendar_to_use = ChurchYear::create_church_year($current_date);

        //Get day info 
        $day_info = $calendar_to_use->retrieve_day_info($current_date);
        if(!array_key_exists('introit', $day_info)){
            $day_info['daily_psalter'] = $calendar_to_use->get_monthly_psalter($current_date);
        }

        $output .= '<script id="lection-data" type="application/json">' . \json_encode($day_info) . '</script>';

        //Feast day button
        if($atts['show_feast_day']){
            $feast_day = $calendar_to_use->get_festival($current_date);
            $output .= '<p><script id="festival-data" type="application/json">' . \json_encode($feast_day) . '</script>';
            $output .= '<a id="festival-loader" href="#">Load readings for '. $feast_day['display'] .'</a> </p>';
        }

        

        switch($tag){
            case 'introit': 
                $output .= self::render_introit($readings_tag, $content, $day_info);
                break; 
            case 'lection':
                $output .= self::render_lection($readings_tag, $content, $day_info);
                break; 
            case 'collect':
                $output .= self::render_collect($readings_tag, $content, $day_info);
                break; 
            
        }

        return $output; 
    }

    public static function render_introit($readings_tag, $content, $day_info){
       $output = '<div id="introit">';
        if(array_key_exists('introit', $day_info)){
            $introit = $day_info['introit'];
            $output .= "<$readings_tag>Introit</$readings_tag>";
            $output .= $content; 
            $output .= "<div>$introit</div>";

        } else if (array_key_exists('daily_psalter', $day_info)){
            $daily_psalter = $day_info['daily_psalter']; 
            $output .=  "<$readings_tag" . ' id="psalter-title"'. ">Psalms</$readings_tag>";
            $output .= '<div id="psalter-selection">';
            //matins
            $output .= '<p> <strong>Matins: </strong>';
            foreach($daily_psalter['morning'] as $psalm){
                //If it's just a number, add to it
                if(\is_numeric($psalm)){
                    $psalm = "Psalm $psalm";
                    //Create button 
                    $output .= '<a href="#">' . $psalm . '</a> ';
                    continue;
                }
                $psalm = $psalm; 
                $output .= '<a href="#">' . $psalm . '</a> ';

            }
            $output .= '</p>';

            //vespers
            $output .= '<p> <strong>Vespers: </strong>';
            foreach($daily_psalter['evening'] as $psalm){
                //If it's just a number, add to it
                if(\is_numeric($psalm)){
                    $psalm = "Psalm $psalm";
                    //Create button 
                    $output .= '<a href="#">' . $psalm . '</a> ';
                    continue;
                }
                $psalm = $psalm; 
                $output .= '<a href="#">' . $psalm . '</a> ';
            }
            $output .= '</p>';

            $output .= '</div>';

            $output .= '<div id="psalm-display"><p>Select a Psalm.</p></div>';
                
        }

        
        $output .= '</div>';

        return $output;
    }

    public static function render_lection($readings_tag, $content, $day_info){
        $output = $content; 

        $first_reading = $day_info['readings'][0];
        $second_reading = $day_info['readings'][1];

        //First Reading
        $output .= '<div id="first-reading">';
        $output .= "<$readings_tag>First Reading: $first_reading</$readings_tag>";
        $output .= '<p data-bible="'. $first_reading . '">Loading verse...</p>';
        $output .= '</div>';
        
        //Gradual, usually on Sundays or on Feasts
        $output .= '<div id="gradual">';
        if(array_key_exists('gradual', $day_info)){
            $gradual = $day_info['gradual'];
            $output .= "<$readings_tag>Gradual</$readings_tag>";
            $output .= "<div>$gradual</div>";
        }
        $output .= '</div>';

        //Second Reading
        $output .= '<div id="second-reading">';
        $output .= "<$readings_tag>Second Reading: $second_reading</$readings_tag>";
        $output .= '<p data-bible="'. $second_reading . '">Loading verse...</p>';
        $output .= '</div>';

        return $output;
    }
    public static function render_collect($readings_tag, $content, $day_info){
        $output = '';
        $output .= '<div id="collect">';
        if(array_key_exists('collect', $day_info)){
            $collect = $day_info['collect']; 
            $output .= "<$readings_tag>The Collect for the Day</$readings_tag>";
            $output .= $content;
            $output .= "<p>$collect</p>";
        }
        $output .= '</div>'; 
        return $output;
    }

}