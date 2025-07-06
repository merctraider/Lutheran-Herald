<?php

namespace Lutherald;

if (!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class Widget_Calendar extends \WP_Widget
{
    //Construct
    function __construct()
    {
        parent::__construct(
            //ID
            'lutherald_Widget_Calendar',

            //Widget Name
            __('Church Calendar'),

            array(
                'description' => __('Displays the Church Calendar by month')
            )
        );
    }
    public function widget($args, $instance)
    {
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

        // Today
        $today = date('Y-m-j', time());

        // For H3 title
        $html_title = date('Y / m', $timestamp);


        //Get settings
        $entry_url = isset($instance['entry_url']) ? $instance['entry_url'] : 'resources/daily-bible-reading';

        //Draw Calendar

        // Create prev & next month link     mktime(hour,minute,second,month,day,year)
        $prev = date('Y-m', mktime(0, 0, 0, date('m', $timestamp) - 1, 1, date('Y', $timestamp)));
        $next = date('Y-m', mktime(0, 0, 0, date('m', $timestamp) + 1, 1, date('Y', $timestamp)));

        // Number of days in the month
        $day_count = date('t', $timestamp);

        // 0:Sun 1:Mon 2:Tue ...
        $str = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));

        $weeks = array();
        $week = '';

        // Add empty cell
        $week .= str_repeat('<td></td>', $str);

        for ($day = 1; $day <= $day_count; $day++, $str++) {

            $date = $current_date . '-' . $day;
            $day_info = $calendar_to_use->retrieve_day_info($date);
            
            //If the day info is false, the calendar is next year
            if(!$day_info){
                
                $calendar_to_use =  $this_year;
                $day_info = $calendar_to_use->retrieve_day_info($date);
            }
            
            $feast_day = $calendar_to_use->get_festival($date);

            $day_display = $day_info['display'];
            $color = $day_info['color'];

            if ($today == $date) {
                $week .= '<td class="today '.$color .'"><p>' . $day . '</p>';
            } else {
                $week .= '<td class="'.$color.'"><p>' . $day . '</p>';
            }
            //Cell contents
            $week .= '<p><a rel="nofollow" href="' . $entry_url . '?date=' . $date . '">' . $day_display . '</a></p>';

            if($feast_day != false){
                $week .= '<p><a rel="nofollow" href='. $entry_url . '?date=' . $date . '>'  . $feast_day['display'] . '</a></p>';
            }

            $week .= '</td>';

            // End of the week OR End of the month
            if ($str % 7 == 6 || $day == $day_count) {

                if ($day == $day_count) {
                    // Add empty cell
                    $week .= str_repeat('<td></td>', 6 - ($str % 7));
                }

                $weeks[] = '<tr>' . $week . '</tr>';

                // Prepare for new week
                $week = '';
            } 
        }

        ?>
            <div class="container">
                <h3><a rel="nofollow" href="?date=<?php echo $prev; ?>">&lt;</a> <?php echo $html_title; ?> <a rel="nofollow" href="?date=<?php echo $next; ?>">&gt;</a></h3>
                <table class="table table-bordered tlh-calendar">
                    <tr>
                        <th>S</th>
                        <th>M</th>
                        <th>T</th>
                        <th>W</th>
                        <th>T</th>
                        <th>F</th>
                        <th>S</th>
                    </tr>
                    <?php
                    foreach ($weeks as $week) {
                        echo $week;
                    }
                    ?>
                </table>
            </div>
<?php
    }

    public function form($instance)
    {
        $entry_url = isset($instance['entry_url']) ? $instance['entry_url'] : 'resources/daily-bible-reading';
        $entry_url_field = new \Field($this->get_field_name('entry_url'), 'Calendar Base Link URL', $entry_url);
        $entry_url_field->draw_text_field();
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['entry_url'] = $new_instance['entry_url'];
        return $instance;
    }
}
