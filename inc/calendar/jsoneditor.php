<?php 

$json = file_get_contents(dirname(__FILE__) ."/moveable_feasts.json");

$array = json_decode($json, true);

$season_names = []; 
foreach ($array as $season_name => $weeks){
    $season_names[] = $season_name;
}
?> 

<form method="post">


<input type="submit">
</form>