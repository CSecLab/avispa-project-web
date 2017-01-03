<?php

$file="ISO1.hlpsl";
$filename = substr($file,0,strrpos($file,"."));
$extension = substr($file,strrpos($file,"."), strlen($file));
echo("file = $file "."<br />");
echo("filename = $filename "."<br />");
echo("extension = $extension "."<br />");

?>