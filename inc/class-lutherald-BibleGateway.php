<?php
namespace Lutherald;
if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class BibleGateway{

    public static $version = 'NKJV'; 

    public static function fetch_url($url){
        //Initialise the curl
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $output = curl_exec($ch);
        \curl_close($ch);
        return $output;
    }

    public static function get_verse($lookup){
        $lookup = \urlencode($lookup);
        
        $ver = self::$version;
        $url = "http://www.biblegateway.com/passage/?search=$lookup&version=$ver";
        /*
        $content = self::fetch_url($url);
        if (preg_match('/<meta property="og:description" content="([^\/]+)"/', $content, $matches)){
            $verse = $matches[1];
            return $verse;
        }*/
        require_once  plugin_dir_path(__FILE__) .'simple_html_dom.php';
        $content = file_get_html($url);
        $output = '';
        $passage_html = $content->find('div.passage-text', 0);
        foreach($passage_html->find('p') as $verse){
            foreach($verse->find('sup') as $footnote){
                $footnote->innertext = '';
            }

            $output .= $verse->plaintext;
        }
        return $output;
        
        return false;      
        
    }

    public static function get_devotions($date){
        $timestamp = $date->format('Y-m-d');
        $url = 'http://eldona.org/wp-json/wp/v2/posts?before='. $timestamp . 'T23:59:00';
        $devotions_json = file_get_contents($url);
        
        if($devotions_json == null || $devotions_json === ''){
            return false; 
        }
        
        $devotions_arr = json_decode($devotions_json, true);
        foreach ($devotions_arr as $entry){
            $entry_date = strtotime($entry['date']);
            $entry_date = date('Y-m-d', $entry_date); 

            if($timestamp == $entry_date){
                $content = $entry['content']['rendered'];
                //Find the devotional content
                $content_array = explode("<strong>Devotion</strong>", $content);
                $devotion = $content_array[1];
                //Strip the first spacing
                $html = str_get_html($devotion);
                $html->find('div._1mf span', 0)->innertext = '';

                $output = '';

                foreach($html->find('div._1mf span') as $paragraph){
                    $p = $paragraph->plaintext;
                    if(strlen($p) > 6)
                    {
                        $output .= "<p>$p</p>";
                    }
                   
                }

                return $output;
            }
        }
        return false;
        
    }
}