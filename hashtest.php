<?php
$ctx = hash_init('sha1',HASH_HMAC,"mobilid123");
hash_update($ctx, 'francis');
hash_update($ctx, 'inventory');
hash_update($ctx, 'sha1');
echo hash_final($ctx);
echo "<br>Lang:";
echo $_SERVER['HTTP_ACCEPT_LANGUAGE'];
echo ":";
$langpref=empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])?'':$_SERVER['HTTP_ACCEPT_LANGUAGE'];
print_r($langpref);
$langpref=preg_replace("/;([^,]*)/i","",$langpref);
print_r($langpref);
$langpref=str_replace("-","_",$langpref);
print_r($langpref);
$langlist=preg_split("/[;,]/",$langpref);
print_r($langlist);
?>