#!/usr/local/bin/php
<?php
$file=file_get_contents($argv[1]);
$file=str_replace(chr(13).chr(10),chr(10),$file);
$file=iconv('CP1251','utf-8',$file);
file_put_contents($argv[1],$file);
?>