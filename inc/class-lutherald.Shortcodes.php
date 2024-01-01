<?php

namespace Lutherald;

if (!defined('ABSPATH')) wp_die('Cannot access this file directly.');


class Shortcodes
{

    public static function init()
    {
        add_shortcode('introit', array(__CLASS__, 'lectionary_part'));
        add_shortcode('lection', array(__CLASS__, 'lectionary_part'));
        add_shortcode('collect', array(__CLASS__, 'lectionary_part'));
        add_shortcode('biblereadingsmin', array(__CLASS__, 'biblereadingsmin'));
        add_shortcode('lectionary_upcoming', array(__CLASS__, 'lectionary_upcoming'));
    }

    static function lectionary_upcoming($atts)
    {
        $output = '';
        //Attributes: Entry URL
        $atts = shortcode_atts(
            array(
                'entry_url' => '/resources/daily-bible-reading',
            ),
            $atts
        );

        //Get current date
        $current_date = date('Y-m');

        if (isset($_GET['date'])) {
            $current_date = $_GET['date'];
        }
        // Check format
        $timestamp = strtotime($current_date . '-01');
        if ($timestamp === false) {
            $current_date = date('Y-m');
            $timestamp = strtotime($current_date . '-01');
        }

        $year = date('Y', $timestamp);

        $last_year = new ChurchYear($year - 1);
        $this_year = new ChurchYear($year);
        $calendar_to_use = null;

        if ($last_year->find_season($current_date) != false) {
            $calendar_to_use = $last_year;
        }

        if ($this_year->find_season($current_date) != false) {
            $calendar_to_use = $this_year;
        }

        //Today's date day
        $today_day = date('j', time());

        for ($day = $today_day-1; $day < $today_day + 3; $day++) {
            $date = $current_date . '-' . $day;
            $day_info = $calendar_to_use->retrieve_day_info($date);

            //If the day info is false, the calendar is next year
            if (!$day_info) {

                $calendar_to_use =  $this_year;
                $day_info = $calendar_to_use->retrieve_day_info($date);
            }

            $day_display = $day_info['display'];
            $feast_day = $calendar_to_use->get_festival($date);
            $output .= '<div class="mini-calendar-lectionary-date">';
            $output .= '<small>';
            //Format the date to Day, Month Date 
            $output .=  date('D, M j', strtotime($date));
            $output .= '</small>';
            $output .= '</div>';
            $output .= '<a href=" ' . $atts['entry_url'] . '?date=' . $date . '">';
            $output .= '<div class="mini-calendar-lectionary-day">';
            $output .= $day_display;
            if ($feast_day != false) {
                $output .= '<span>';
                $output .=', ' . $feast_day['display'];
                $output .= '</span>';
            }
            
            $output .= '</div>';
            $output .= '</a>';
        }

        return $output;
    }

    static function biblereadingsmin($atts)
    {
        //Attributes: Entry URL
        $atts = shortcode_atts(
            array(
                'entry_url' => '/resources/daily-bible-reading',
            ),
            $atts
        );

        $entry_url = $atts['entry_url'];

        $bible_icon = '<span class="bible-icon"> <svg aria-hidden="true" class="e-font-icon-svg e-fas-bible" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M448 358.4V25.6c0-16-9.6-25.6-25.6-25.6H96C41.6 0 0 41.6 0 96v320c0 54.4 41.6 96 96 96h326.4c12.8 0 25.6-9.6 25.6-25.6v-16c0-6.4-3.2-12.8-9.6-19.2-3.2-16-3.2-60.8 0-73.6 6.4-3.2 9.6-9.6 9.6-19.2zM144 144c0-8.84 7.16-16 16-16h48V80c0-8.84 7.16-16 16-16h32c8.84 0 16 7.16 16 16v48h48c8.84 0 16 7.16 16 16v32c0 8.84-7.16 16-16 16h-48v112c0 8.84-7.16 16-16 16h-32c-8.84 0-16-7.16-16-16V192h-48c-8.84 0-16-7.16-16-16v-32zm236.8 304H96c-19.2 0-32-12.8-32-32s16-32 32-32h284.8v64z"></path></svg>						</span>';
        //Display the date
        //Display the day info display name
        //Display the feast if any
        //Display the readings
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
        if (!array_key_exists('introit', $day_info)) {
            $day_info['daily_psalter'] = $calendar_to_use->get_monthly_psalter($current_date);
        }

        $output = '<div class="lectionary-readings">';
        $output .= '<div class="lectionary-date"><small>';
        $output .=  $current_date->format('m F Y');
        $output .= '</small></div>';
        $output .= '<a href=" ' . $entry_url . '?date=' . $current_date->format('Y-m-d') . '">';
        $output .= '<div class="lectionary-day">';
        $output .= $day_info['display'];
        $output .= '</div>';
        //Feast day button
        $feast_day = $calendar_to_use->get_festival($current_date);
        if ($feast_day != false) {
            $output .= '<div class="lectionary-feast">';
            $output .= $feast_day['display'];
            $output .= '</div>';
        }
        $output .= '</a>';
        $output .= '<div class="lectionary-readings">';

        if (count((array) $day_info['readings']) > 2) {

            //OT Reading
            $output .= '<div class="lectionary-reading">';
            //Bible icon
            $output .= $bible_icon;
            $output .= '<span>' . $day_info['readings'][2] . '</span>';
            $output .= '</div>';

            //Remove from readings array
            unset($day_info['readings'][2]);
        }

        //Output the other readings
        foreach ($day_info['readings'] as $reading) {
            $output .= '<div class="lectionary-reading">';
            //Bible icon
            $output .= $bible_icon;
            $output .= '<span>' . $reading . '</span>';
            $output .= '</div>';
        }

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    public static function lectionary_part($atts, $content, $tag)
    {
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
        if (!array_key_exists('introit', $day_info)) {
            $day_info['daily_psalter'] = $calendar_to_use->get_monthly_psalter($current_date);
        }

        $output .= '<script id="lection-data" type="application/json">' . \json_encode($day_info) . '</script>';

        //Feast day button
        if ($atts['show_feast_day']) {
            $feast_day = $calendar_to_use->get_festival($current_date);
            $output .= '<p><script id="festival-data" type="application/json">' . \json_encode($feast_day) . '</script>';
            $output .= '<a id="festival-loader" href="#">Load readings for ' . $feast_day['display'] . '</a> </p>';
        }



        switch ($tag) {
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

    public static function render_introit($readings_tag, $content, $day_info)
    {
        $output = '<div id="introit">';
        if (array_key_exists('introit', $day_info)) {
            $introit = $day_info['introit'];
            $output .= "<$readings_tag>Introit</$readings_tag>";
            $output .= $content;
            $output .= "<div>$introit</div>";
        } else if (array_key_exists('daily_psalter', $day_info)) {
            $daily_psalter = $day_info['daily_psalter'];
            $output .=  "<$readings_tag" . ' id="psalter-title"' . ">Psalms</$readings_tag>";
            $output .= '<div id="psalter-selection">';
            //matins
            $output .= '<p> <strong>Matins: </strong>';
            foreach ($daily_psalter['morning'] as $psalm) {
                //If it's just a number, add to it
                if (\is_numeric($psalm)) {
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
            foreach ($daily_psalter['evening'] as $psalm) {
                //If it's just a number, add to it
                if (\is_numeric($psalm)) {
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

    public static function render_lection($readings_tag, $content, $day_info)
    {
        $output = $content;

        $first_reading = $day_info['readings'][0];
        $second_reading = $day_info['readings'][1];

        //First Reading
        $output .= '<div id="first-reading">';
        $output .= "<$readings_tag>First Reading: $first_reading</$readings_tag>";
        $output .= '<p data-bible="' . $first_reading . '">Loading verse...</p>';
        $output .= '</div>';

        //Gradual, usually on Sundays or on Feasts
        $output .= '<div id="gradual">';
        if (array_key_exists('gradual', $day_info)) {
            $gradual = $day_info['gradual'];
            $output .= "<$readings_tag>Gradual</$readings_tag>";
            $output .= "<div>$gradual</div>";
        }
        $output .= '</div>';

        //Second Reading
        $output .= '<div id="second-reading">';
        $output .= "<$readings_tag>Second Reading: $second_reading</$readings_tag>";
        $output .= '<p data-bible="' . $second_reading . '">Loading verse...</p>';
        $output .= '</div>';

        return $output;
    }
    public static function render_collect($readings_tag, $content, $day_info)
    {
        $output = '';
        $output .= '<div id="collect">';
        if (array_key_exists('collect', $day_info)) {
            $collect = $day_info['collect'];
            $output .= "<$readings_tag>The Collect for the Day</$readings_tag>";
            $output .= $content;
            $output .= "<p>$collect</p>";
        }
        $output .= '</div>';
        return $output;
    }
}
