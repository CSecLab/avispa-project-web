<?php

// Detecting user's browser to define apropriate element sizes. 
// Each browser has its own way to arrange the interface elements on the page.
require_once('./include/browser.php');
$br = new Browser;
//echo $br->Name;
if (  (($br->Name == "Mozilla") && ($br->Version < "1.7")) 
    || ($br->Name == "MSIE") 
    || ($br->Name == "Konqueror") )
{
  $textarea_cols = 118;
  $textarea_rows = 35;
} else {
  $textarea_cols = 136;
  $textarea_rows = 40;
}

// Adopt a smaller window for Postscript download
if ($_GET['ps'])
{
  $table_width=470;
  $table_height=280;
} else {
  $table_width=970;
  $table_height=580;
}

// Return the next non-empty/non-comment/non-extrainfo line of the file
function nextLine($file)
{
  do {
    if (!feof($file))
      $line = trim(fgets ($file,1024));
    else
      $line = 'EOF';
  } while (($line=="") || (substr($line, 0, 1)=="%") || (substr($line, 0, 1)=="&"));
  return $line;
}

// Return the message in LaTeX compatible mode i.e.
// adding slash simbols before "{", "}", and "_"
function filterMsg($msg) {
  return addcslashes($msg, "{}_");
}


// Generate an LaTeX MSC from an attack trace
function generateMSC ($atk_file)
{

  // Open the attack trace file
  $handle_atk = fopen ($atk_file, "r");
  if (!$handle_atk) {
    echo "Can not open ".$atk_file." file.\n";
    exit;
  }

  // Search for the ATTACK TRACE keyword inside the file
  $found = false;
  while (!feof($handle_atk) && (!$found))
  {
    $line = trim(fgets ($handle_atk,1024));
    if ($line == "ATTACK TRACE")
      $found = true;
  }

  // If ATTACK TRACE was found
  if ($found)
  {
    $num_message = 0;
    $finish = false;

    // Process each message exchange
    // storing the trace information
    while (!$finish)
    {
      $line = nextLine($handle_atk);

      $arrow_pos   = strpos($line, "->");
      $twodots_pos = strpos($line, ":");

      if (($arrow_pos) && ($twodots_pos))
      {

	$send = trim(substr($line, 0, $arrow_pos));
	//echo "send: ".$send." ";
	$recv = trim(substr($line, $arrow_pos+2, $twodots_pos-$arrow_pos-2));
	//echo "recv: ".$recv." ";
	$mess = trim(substr($line, $twodots_pos+1, strlen($line)-$twodots_pos-1));
	
        // The message can contain extra information after a white space.
        // This information is ignored for the MSC graphic
        if (strpos($mess," "))
	  $mess = trim(substr($mess, 0, strpos($mess," ")));
	//echo "mess: ".$mess."<br>";

	$agent_send[$num_message] = $send;
	$agent_recv[$num_message] = $recv;
        $message[$num_message]    = $mess;
	$num_message++;
      } else {
        $finish = true;
      }
    }

    // ================
    // LaTex Generation
    // ================

    $j = 0;
    //$agents[$j] = $agent_send[$j++]; [RC]replaced by the following two lines:
    $agents[$j] = $agent_send[$j];
    $j++;
    $agent_color = array ("red","blue","darkgreen","darkgray","magenta","darkcyan","darkyellow");

    // Create a list of the different instances playing the protocol
    for ($i = 0; $i < $num_message; $i++)
    {
      //echo "=".$agent_send[$i]."->".$agent_recv[$i].":".$message[$i]."<br>";

      if (!in_array($agent_send[$i], $agents))
         $agents[$j++] = $agent_send[$i];
      if (!in_array($agent_recv[$i], $agents))
         $agents[$j++] = $agent_recv[$i];
    }


    // Take the base name of the tex file from the attack file name
    $latex_file = substr($atk_file,0,strrpos($atk_file,".")).".tex";

    // Open the latex file for writing
    $handle_tex = fopen ($latex_file, "w");
    if (!$handle_tex) {
      echo "Can not open ".$latex_file." file.\n";
      exit;
    }

    // Create LaTeX file
    $content = "\documentclass[landscape]{article}\n";
    $content .= "\usepackage{msc}\n";
    $content .= "\usepackage{pslatex}\n\n";

    $content .= "\begin{document}\n";
    $content .= "\pagestyle{empty}\n\n";
    $content .= "\sffamily\n";
    $content .= "\\footnotesize\n\n";

    $content .= "\definecolor{darkgreen}{rgb}{0.30,0.60,0.00}\n";
    $content .= "\definecolor{darkgray}{rgb}{0.30,0.30,0.30}\n";
    $content .= "\definecolor{darkyellow}{rgb}{0.80,0.80,0.00}\n";
    $content .= "\definecolor{darkcyan}{rgb}{0.00,0.50,0.50}\n\n";

    $content .= "\enlargethispage{100cm}\n\n";

    $content .= "\begin{msc}{ATTACK TRACE}\n\n";

    $content .= "\setlength{\levelheight}{0.65cm}\n";
    $content .= "\setlength{\instdist}{3.2cm}\n\n";

    $k = 0;
    foreach ($agents as $ag) 
    {
      $content .= "\declinst{a".$k."}{\\textcolor{".$agent_color[$k]."}{Agent}}{\\textcolor{".$agent_color[$k++]."}{".$ag."}}\n";
    }

    foreach ($message as $key => $m )
    {
      $agent_send_id = array_search($agent_send[$key],$agents);
      $agent_recv_id = array_search($agent_recv[$key],$agents);
      $color = $agent_color[$agent_send_id];
      $content .= "\mess{\\textcolor{".$color."}{".filterMsg($m)."}}{a".$agent_send_id."}{a".$agent_recv_id."}\n";
      $content .= "\\nextlevel\n";
    }

    $content .= "\end{msc}\n";
    $content .= "\end{document}\n";

    if (!fwrite($handle_tex, $content)) {
     echo "Cannot write to file ".$latex_file;
     exit;
    }

    fclose($handle_tex);

  } else {
    echo "Section ATTACK TRACE not found in this file!\n";
    exit;
  }

  fclose ($handle_atk);
}
  
  
// Generate Message Sequence Chart in PNG format
function generatePNG ($atk_file)
{

  // Pre-defined variables/constants
  $bindir="./bin/";
  $tempdir="./tempdir/";
    
  // Parse paths.cfg file
  $config = parse_ini_file("config.ini");

  $avispawebsetting = $config[AVISPA_WEB_SETTING];
  $localwebinterfacepath = $config[AVISPA_WEB_LOCAL_PATH];
  $remotewebinterfacepath = $config[AVISPA_WEB_REMOTE_PATH];
  $remoteuser = $config[AVISPA_WEB_REMOTE_USER];
  $remoteip = $config[AVISPA_WEB_REMOTE_IP];
  $ssh_options= $config[SSH_OPTIONS];  

  // Generate a LaTeX MSC
  generateMSC ($atk_file);
    
  $base_file = substr($atk_file,strrpos($atk_file,"/")+1,strrpos($atk_file,".")-strrpos($atk_file,"/")-1);
  $temp_base_file = $tempdir.$base_file;
    
  // Generate PNG image from a LaTeX MSC
  if ($avispawebsetting == remote)
  {
    $copy_tex_remote = "scp -p".$ssh_options.$localwebinterfacepath.$temp_base_file.".tex ".
                                $remoteuser."@".$remoteip.":".$remotewebinterfacepath.$temp_base_file.".tex";

    // Only the base_file is passed since the script msctopng will enter in the appropriate directory
    $msctopng_cmd = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                          $remotewebinterfacepath.$bindir."msctopng ".
                          $remotewebinterfacepath.$tempdir." ".
                          $base_file.".tex ".$base_file.".dvi ".$base_file.".ps ".$base_file.".log ".$base_file.".aux";

    $copy_png_local = "scp -p".$ssh_options.$remoteuser."@".$remoteip.":".
                               $remotewebinterfacepath.$temp_base_file.".png ".
                               $localwebinterfacepath.$temp_base_file.".png";

    //echo "copy_tex_remote: ".$copy_tex_remote."<br>\n";
    //echo "msctopng_cmd: ".$msctopng_cmd."<br>\n";
    //echo "copy_png_local: ".$copy_png_local."<br>\n"; 
    exec($copy_tex_remote);
    exec($msctopng_cmd);
    exec($copy_png_local);

  }
  elseif ($avispawebsetting == local)
  {
    $msctopng_cmd = $bindir."msctopng ".$localwebinterfacepath.$tempdir." ".
                    $base_file.".tex ".$base_file.".dvi ".$base_file.".ps ".$base_file.".log ".$base_file.".aux";
    exec($msctopng_cmd);
  } 
  else 
    echo "AVISPA_WEB_SETTING variable not properly set in config.ini<br>";
}
  
// Generate a Message Sequence Chart in PS format
function generatePS ($atk_file)
{
  // Pre-defined variables/constants
  $bindir="./bin/";
  $tempdir="./tempdir/";
    
  // Parse paths.cfg file
  $config = parse_ini_file("config.ini");

  $avispawebsetting = $config[AVISPA_WEB_SETTING];
  $localwebinterfacepath = $config[AVISPA_WEB_LOCAL_PATH];
  $remotewebinterfacepath = $config[AVISPA_WEB_REMOTE_PATH];
  $remoteuser = $config[AVISPA_WEB_REMOTE_USER];
  $remoteip = $config[AVISPA_WEB_REMOTE_IP];
  $ssh_options= $config[SSH_OPTIONS];
 
  // Generate a LaTeX MSC
  generateMSC ($atk_file);
    
  $base_file = substr($atk_file,strrpos($atk_file,"/")+1,strrpos($atk_file,".")-strrpos($atk_file,"/")-1);
  $temp_base_file = $tempdir.$base_file;

  // Generate PNG image from a LaTeX MSC
  if ($avispawebsetting==remote)
  {
    $copy_tex_remote = "scp -p".$ssh_options.$localwebinterfacepath.$temp_base_file.".tex ".
                                $remoteuser."@".$remoteip.":".$remotewebinterfacepath.$temp_base_file.".tex";

    // we provide only the base_file since the script msctops will enter in the appropriate directory
    $msctops_cmd = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                         $remotewebinterfacepath.$bindir."msctops ".
                         $remotewebinterfacepath.$tempdir." ".
                         $base_file.".tex ".$base_file.".dvi ".$base_file.".ps ".$base_file.".log ".$base_file.".aux";

    $copy_psgz_local = "scp -p".$ssh_options.$remoteuser."@".$remoteip.":".
                                $remotewebinterfacepath.$temp_base_file.".ps.gz ".
                                $localwebinterfacepath.$temp_base_file.".ps.gz";

    //echo "copy_tex_remote: ".$copy_tex_remote."<br><br>\n";
    //echo "msctops_cmd: ".$msctops_cmd."<br><br>\n";
    //echo "copy_psgz_local:\n".$copy_psgz_local."<br><br>\n";
    exec($copy_tex_remote);
    exec($msctops_cmd);
    exec($copy_psgz_local);
  }
  elseif ($avispawebsetting == local)
  {
    $msctops_cmd = $bindir."msctops ".$localwebinterfacepath.$tempdir." ".
                   $base_file.".tex ".$base_file.".dvi ".$base_file.".ps ".$base_file.".log ".$base_file.".aux";
    
    //echo "msctops_cmd:\n".$msctops_cmd."<br><br>\n";
    exec($msctops_cmd);
  } 
  else 
    echo "AVISPA_WEB_SETTING variable not properly set in config.ini<br>";
}
  
?>

<!-- HTML -->
<html>
<head>
<?php

  // Requesting visualization of a file
  if ($_GET['file'])
  {
   // If the last dot character is the first one of the path that means
   // the file has no dot and is in fact an HLPSL file
   if (strrpos($_GET['file'],".") == 0)
     $extension = "hlpsl";
   else
     $extension = substr($_GET['file'],strrpos($_GET['file'],".")+1, strlen($_GET['file']));

   switch ($extension)
   {
     case "hlpsl":
	  switch ($_GET['tool'])
	  {
            case "ofmc" : echo "<title>OFMC HLPSL</title>\n"; break;
	    case "atse" : echo "<title>CL-AtSe HLPSL</title>\n"; break;
	    case "satmc": echo "<title>SATMC HLPSL</title>\n"; break;
            case "ta4sp": echo "<title>TA4SP HLPSL</title>\n"; break;
	    default: echo "<title>HLPSL</title>\n"; break;
          }
	  break;

     case "if":
	  switch ($_GET['tool'])
	  {
            case "ofmc" : echo "<title>OFMC IF</title>\n"; break;
            case "atse" : echo "<title>CL-AtSe IF</title>\n"; break;
            case "satmc": echo "<title>SATMC IF</title>\n"; break;
            case "ta4sp": echo "<title>TA4SP IF</title>\n"; break;
            default: echo "<title>IF</title>\n"; break;
          }
	  break;

     case "atk":
	  switch ($_GET['tool'])
	  { 
            case "ofmc" : echo "<title>OFMC Output</title>\n"; break;
            case "atse" : echo "<title>CL-AtSe Output</title>\n"; break;
            case "satmc": echo "<title>SATMC Output</title>\n"; break;
            case "ta4sp": echo "<title>TA4SP Output</title>\n"; break;
            default: echo "<title>Output</title>\n"; break;
          }
          break;

      default: echo "<title>General View</title>\n";
   }
  }

  // Requesting MSC generation
  if ($_GET['image'])
  {

    $extension = substr($_GET['image'],strrpos($_GET['image'],".")+1, strlen($_GET['file']));

    switch ($_GET['tool'])
    {
      case "ofmc":  echo "<title>OFMC MSC</title>\n";    break;
      case "atse":  echo "<title>CL-AtSe MSC</title>\n"; break;
      case "satmc": echo "<title>SATMC MSC</title>\n";   break;
      case "ta4sp": echo "<title>TA4SP MSC</title>\n";   break;
      default: echo "<title>General MSC</title>\n";
    }
  }

  // Requesting PDF generation
  if ($_GET['ps'])
  {
    switch ($_GET['tool'])
    {
      case "ofmc":  echo "<title>OFMC Postscript Download</title>\n";  break;
      case "atse":  echo "<title>CL-ATSE Postscript Download</title>\n";  break;
      case "satmc": echo "<title>SATMC Postscript Download</title>\n";  break;
      case "ta4sp": echo "<title>TA4SP Postscript Download</title>\n";  break;
      default: echo "<title>Postscript Download</title>\n";
    }
  }

?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>


<body text="#000000" bgcolor="#8E8FC0">
<table width="<? echo $table_width ?>" height="<? echo $table_height ?>" align="center" border="2" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
<tr>
<td valign="middle" align="center">

<?php
  if ($_GET['file'])
  {
    echo "<textarea name=\"showfile\" rows=\"".$textarea_rows."\" cols=\"".$textarea_cols."\" wrap=\"off\" readonly>\n";
    $tempdir="./tempdir/";
    @readfile ($tempdir.basename($_GET['file']));
    echo "</textarea>\n";
  }
  if ($_GET['image'])
  {
    generatePNG ($_GET['image']);
    $base = substr($_GET['image'],0,strrpos($_GET['image'],"."));
    echo "<img src=".$base.".png".">\n";
  }
  
  if ($_GET['ps'])
  {
    generatePS ($_GET['ps']);
    $base = substr($_GET['ps'],0,strrpos($_GET['ps'],"."));
    
    if (file_exists($base.".ps.gz"))
    {
      echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\" color=\"darkblue\">\n";
      echo "<b>AVISPA Web Tool</b>\n";
      echo "<br><br>\n";
      echo "A postscript version of the Message Sequence Chart was generated.<br>\n";
      echo "<br>\n";
      echo "</font>\n";
      echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"3\" color=\"blue\">\n";
      echo "<a href=\"".$base.".ps.gz\">Download Postscript file</a>\n";
      echo "</font>\n";
    } else {
      echo "<font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\" color=\"darkblue\">\n";
      echo "<b>AVISPA Web Tool</b>\n";
      echo "<br><br>\n";
      echo "A postscript version of the Message Sequence Chart could not be generated. \n";
      echo "Please report the error to the AVISPA Project support.<br>\n";
      echo "<br>\n";
      echo "Thank you\n";
      echo "</font>\n";
    }
    
  }
?>

</td>
</tr>
</table>

</body>
</html>
