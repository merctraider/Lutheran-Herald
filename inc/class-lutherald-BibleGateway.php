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
        $content = self::fetch_url($url);
        if (preg_match('/<meta property="og:description" content="([^\/]+)"/', $content, $matches)){
            $verse = $matches[1];
            return $verse;
        }
        return false;      
        
    }
}