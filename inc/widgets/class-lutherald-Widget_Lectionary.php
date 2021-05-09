<?php
namespace Lutherald; 

if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class Widget_Lectionary extends \WP_Widget{
    //Construct
    function __construct() {
        parent::__construct(
            //ID
            'lutherald_Widget_Lectionary',

            //Widget Name
            __('Lectionary Table'),

            array(
                'description' => __('Displays lectionary readings for every Sunday')               
            )
        );
    }
    public function widget( $args, $instance ) {
        //determine which calendar to use
        $current_date = new \DateTime('now');
        $year=$current_date->format('Y');

        $last_year = new ChurchYear($year-1);
        $this_year = new ChurchYear($year);
        $calendar = null;

        if($last_year->find_season($current_date)!= false){
            $calendar = $last_year;
        } 

        if($this_year->find_season($current_date) != false){
            $calendar = $this_year;
        }
        
        $dates = $this->get_dates($calendar); 


       
        
        //Draw calendar
        ?>
        <table>
            <tbody>
                <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Epistle</th>
                <th>Gospel</th>
                </tr>
                <?php
                foreach ($dates as $date){
                    if(!$date) echo '<tr><td>'.$date->format('d M Y') . '</td><td>null</td><td>null</td></tr>';

                    $day_info = $calendar->retrieve_day_info($date);
                    echo '<tr class="'. $day_info['color'].'">';
                        echo '<td>' . $date->format('d M Y') .'</td>'; //Date
                        echo '<td>' . $day_info['display'] .'</td>'; //Dau
                        echo '<td>' . $day_info['readings'][0] . '</td>' . '<td>' . $day_info['readings'][1]. '</td>';
                    echo '</tr>';
                }
                ?>

            </tbody>
        </table>
        <script> 
            var refTagger = {
                settings: { 
                    bibleVersion: "NKJV"   
                }  
            }; 
            (function(d, t) { 
                var g = d.createElement(t), s = d.getElementsByTagName(t)[0]; 
                g.src = '//api.reftagger.com/v2/RefTagger.js'; 
                s.parentNode.insertBefore(g, s); 
            }(document, 'script')); 
        </script>
        <?php

    }

    function get_dates($calendar){
        //Important Dates
        $dates = []; 
        //Advent
        $advent_sundays = $calendar->get_advent_sundays();
        $advent_sundays[] = $calendar->get_christmas('-1 days');
        $dates= $advent_sundays; 
        
        //Christmas
        $christmas = $calendar->get_christmas();
        $first_sunday_after_christmas = $calendar->get_christmas('next Sunday');
        $second_sunday_after_christmas = $calendar->get_christmas('+1 Weeks Sunday');
        $christmastide = [$christmas, $first_sunday_after_christmas, $second_sunday_after_christmas];
        $christmastide = $this->fix_season_dates($calendar, $christmastide, 'christmas');
        $dates = \array_merge($dates,$christmastide);

        //Epiphany
        $epiphany = $calendar->get_epiphany();
        $sundays_in_epiphany = $calendar->get_epiphany_sundays();
        $transfiguration = $calendar->get_transfiguration();
        $epiphany_sundays = [$epiphany];
        $epiphany_sundays = array_merge($epiphany_sundays,$sundays_in_epiphany, [$transfiguration]);
        
        $dates = \array_merge($dates, $epiphany_sundays);
        
        //Lententide
        $septuagesima = $calendar->get_gesima('septuagesima');
        $sexagesima = $calendar->get_gesima('sexagesima');
        $quinquagesima = $calendar->get_gesima('quinquagesima');
        $ash_wednesday = $calendar->get_lent_start();
        $lenten_sundays = [];
        for($i=1; $i<=6; $i++){
            $lenten_sundays[] = $calendar->get_lent($i);
        }
        $good_friday = $calendar->get_goodfriday();
        $lententide = array_merge([$septuagesima, $sexagesima, $quinquagesima, $ash_wednesday], $lenten_sundays, [$calendar->get_maundy_thursday(), $good_friday]); 
        
        $dates = array_merge($dates,$lententide);
        //Easter 
        $easter = $calendar->get_easter();
        $eastertide =  [$easter];
        for($i=1; $i<6; $i++){
            $eastertide[] = $calendar->get_eastertide($i);
        }
        $eastertide = array_merge($eastertide, [$calendar->get_ascension(), $calendar->get_eastertide(6)]);
        $dates = array_merge($dates, $eastertide); 

        //Ordinary Time
        $pentecost = $calendar->get_pentecost();
        $trinity_sunday = $calendar->get_trinity_sunday();
        $ordinary_sundays = $calendar->get_trinity_sundays(); 

        $ordinary_time = array_merge([$pentecost, $trinity_sunday],$ordinary_sundays);

        
        $dates = array_merge($dates, $ordinary_time);

        return $dates;
        
    }


    function fix_season_dates(ChurchYear $church_year, $array, $season){
        $season_dates = [];

        foreach($array as $date){
            //Make sure the date falls on the season
            if($church_year->find_season($date) == $season){
                $season_dates[] = $date; 
            } 
        }

        return $season_dates;
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