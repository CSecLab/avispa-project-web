<?php

// Detecting user's browser to define apropriate element sizes. 
// Each browser has its own way to arrange the interface elements on the page.
require_once('browser.php');

$br = new Browser;
//echo $br->Name;
if (  (($br->Name == "Mozilla") && ($br->Version < "1.7")) 
    || ($br->Name == "MSIE") 
    || ($br->Name == "Konqueror") )
{
  $textarea_cols = 112; 
  $textarea_rows = 12; 
} 
else 
{
  $textarea_cols = 130;
  $textarea_rows = 14;
}


require_once('avispa_web_tool.php');

$avispaTool = new AVISPAWebTool($_POST);

$predefpath		= $avispaTool->getPredefpath();
$tempdir 		= $avispaTool->getTempdir();
$extension 		= $avispaTool->getExtension();
$file_length 	= $avispaTool->getFileLength();

?>