<?php

namespace Lutherald;
//if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');
class Calendar{
    protected $year; 



    public function __construct($year){
        $this->year = $year; 

        
    }

    function get_easter_datetime($year) {
        $base = new \DateTime("$year-03-21");
        $days = easter_days($year);
    
        return $base->add(new \DateInterval("P{$days}D"));
    }

    function isDateBetweenDates(\DateTime $date, \DateTime $startDate, \DateTime $endDate) {
        return $date > $startDate && $date < $endDate;
    }

    public function get_lent_start(){
        $easter = $this->get_easter_datetime($this->year+1);

        return date_sub($easter,date_interval_create_from_date_string("46 days"));
    }

    public function get_easter(){
        return $this->get_easter_datetime($this->year+1);
    }

    public function get_advent_sunday($week){
        //Christmas is the starting point
        $advent_date = date_create($this->year.'-12-25');

        //There are only 4 Sundays in advent!
        if($week > 4){
            return null; 
        }

        //How many last Sundays we have to go
        //e.g. Advent 4 just takes 1 last Sunday, Advent 1 takes 4 Sundays
        $iterations = 5-$week; 
        for($i=0; $i<$iterations; $i++ ){
            $advent_date->modify('last Sunday');
        }
        return $advent_date;
    }

    public function get_epiphany(){
        return date_create(($this->year+1).'-1-6');
    }

    public function epiphany_sunday($week){
        $epiphany_date = get_epiphany();

        
    }

  

   

}