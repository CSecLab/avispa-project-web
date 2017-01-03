<?
$predefpath="./testfiles/";
$extension="*.hlpsl";
$bindir="./bin/";
$tempdir="./tempdir/";
$hlpsl2if="hlpsl2if";

$debug=false;

// Parse paths.cfg file
$config = parse_ini_file("config.ini");

$avispawebsetting = $config[AVISPA_WEB_SETTING];
$localwebinterfacepath = $config[AVISPA_WEB_LOCAL_PATH];
$remotewebinterfacepath = $config[AVISPA_WEB_REMOTE_PATH];
$remoteuser = $config[AVISPA_WEB_REMOTE_USER];
$remoteip = $config[AVISPA_WEB_REMOTE_IP];
$ssh_options= $config[SSH_OPTIONS];

// Detecting user's browser to define apropriate element sizes. 
// Each browser has its own way to arrange the interface elements on the page.
require_once('./include/browser.php');
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


$args=" ";
if ($types) $args = $args."--types ";
if ($inits) $args = $args."--init ";
if ($rules) $args = $args."--rules ";
if ($goals) $args = $args."--goals ";


if ($avispawebsetting == remote)
{
  $copy_hlpsl_remote = "scp -p".$ssh_options.$localwebinterfacepath.$opened_file." ".
                             $remoteuser."@".$remoteip.":".
                             $remotewebinterfacepath.$opened_file;

  $hlpsl2if_cmd = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                        $remotewebinterfacepath.$bindir.$hlpsl2if.$args.
                        $remotewebinterfacepath.$opened_file." 2>&1";

  $copy_if_local = "scp -p".$ssh_options.$remoteuser."@".$remoteip.":".
                          $remotewebinterfacepath.$opened_file.".if ".
                          $localwebinterfacepath.$opened_file.".if";

  $remove_if_remote = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                        "rm ".$remotewebinterfacepath.$opened_file.".if";

  $remove_hlpsl_remote = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                        "rm ".$remotewebinterfacepath.$opened_file;
}
elseif ($avispawebsetting == local)
{
  $hlpsl2if_cmd = $bindir.$hlpsl2if." ".$args.$opened_file." 2>&1";
}
else 
  echo "AVISPA_WEB_SETTING variable not properly set in config.ini<br>";


?>

<html>
<head>
<title>AVISPA Web Tool</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#FFFFFF" text="#000000">

<table width="1000" border="0" align="center" cellspacing="0" cellpadding="0">
<tr>
<td valign="top">

  <form name="forminput" method="post" action="hlpsl2if_input.php">

    <input type="hidden" name="opened_file" value="<?=$opened_file?>">
    <input type="hidden" name="changing" value=1>
    <input type="hidden" name="types" value=<?=$types?>>
    <input type="hidden" name="inits" value=<?=$inits?>>
    <input type="hidden" name="rules" value=<?=$rules?>>
    <input type="hidden" name="goals" value=<?=$goals?>>

    <table width="100%" border="2" align="center" bordercolor="#00008C" cellpadding="3" cellspacing="0" bgcolor="#8E8FC0">
    <tr>
      <!-- Top Bar -->
      <td align="center" valign="middle" colspan=3 bgcolor="#00008C">
      <img src="images/topbar_expert.gif" border="0" usemap="#Map2">

      <map name="Map2">
        <area shape="rect" coords="554,37,665,68" href="basic.php">
        <area shape="rect" coords="672,37,785,68" href="expert.php">
      </map>
      </td>
    </tr>

    <tr>
      <!-- Analyse Output -->
      <td valign="top" align="center" height="230" width="100%" colspan="3">
      <font size="2">
      <img align="left" src="images/output.gif">

      <br><br>

      <textarea name="textfieldinput" rows="<?=$textarea_rows?>" cols="<?=$textarea_cols?>" wrap="off" readonly
      ><?php
        // Call the tools on the workfile
      
        if($avispawebsetting == remote)
        {         
	  // Copy HLPSL file to remote machine 
          if($debug) echo "COPY_HLPSL_REMOTE: ".$copy_hlpsl_remote."\n";
          exec($copy_hlpsl_remote);
            
          // Execute HLPSL2IF remotly
          if($debug) echo "HLPSL2IF: ".$hlpsl2if_cmd."\n";
          $hlpsl2if_output = shell_exec($hlpsl2if_cmd);

          // For some unknown reason the message "Could not create directory '//.ssh'"
          // is been shown at the beginnig of the output. The code below will separate 
          // the first line from the others in the hlpsl_output and the final value for
          // hlpsl_output will not contain the first line   
          $hlpsl2if_array = explode("\n", $hlpsl2if_output, 2);
          $hlpsl2if_output = $hlpsl2if_array[1];
            
          // Copy remote IF file to local machine
          if($debug) echo "COPY_IF_LOCAL: ".$copy_if_local."\n";
          exec($copy_if_local); 
	}
	else
	{
          // Execute HLPSL2IF locally
          if ($debug) echo "HLPSL2IF: ".$hlpsl2if_cmd."\n";
	  $hlpsl2if_output = shell_exec($hlpsl2if_cmd);
	}
        
        $translation_ok = file_exists($opened_file.".if");

        // If the translation was sucessuful show the file in the textarea,
        // otherwise print the error message from the HLPSL2IF
        if ($translation_ok) 
        {
          readfile ($opened_file.".if");
          
          // Show IF file
	  if (!readfile($opened_file.".if"))
            echo "File ".$opened_file.".if cannot be opened";

          if($avispawebsetting==remote)
          {
            // Remove remote HLPSL file 
            if($debug) echo "REMOVE_HLPSL_REMOTE: ".$remove_hlpsl_remote."\n";
            exec($remove_hlpsl_remote);

            // Remove remote IF file 
            if($debug) echo "REMOVE_IF_REMOTE: ".$remove_if_remote."\n";
            exec($remove_if_remote);
          }
        } else {
          //Show output of the HLPSL2IF Translator
          echo "AVISPA Web Tool\n";
          echo "===============\n";
          echo "\n";
          echo "The HLPSL2IF translator is reporting the following error:\n\n";
          echo $hlpsl2if_output;

          if($avispawebsetting==remote)
          {
            // Remove remote HLPSL file 
            if($debug) echo "REMOVE_HLPSL_REMOTE: ".$remove_hlpsl_remote."\n";
            exec($remove_hlpsl_remote);
          }
        }
    ?>
      </textarea>
      </font>
      <br><br>

      <a href=# onclick="window.open('showfile.php?file=<? echo $opened_file ?>','view_hlspl','width=1000,height=600,left=12,top=84,menubar=0,status=0,location=0,toolbar=0,scrollbars=0,resizable=1');return(false)"><img src="images/view_hlpsl.gif" border="0"></a>
      <br>      
      </td>
    </tr>

    <tr>
      <!-- Tools Selection -->
      <td valign="top" align="center" width="33%">
      <img src="images/tools.gif">
      <br>
      <? 
        if ($translation_ok)
          echo "<img src=\"images/trace_hlpsl2if.gif\" border=\"0\" usemap=\"#Map\">\n";
        else
          echo "<img src=\"images/trace_hlpsl2if_error.gif\" border=\"0\">\n";
      ?>
      <map name="Map">
        <area shape="rect" coords="7,141,71,164" href="ofmc_input.php?opened_file=<? echo $opened_file ?>">
        <area shape="rect" coords="79,141,145,162" href="atse_input.php?opened_file=<? echo $opened_file ?>">
        <area shape="rect" coords="155,141,219,162" href="satmc_input.php?opened_file=<? echo $opened_file ?>">
        <area shape="rect" coords="229,141,293,162" href="ta4sp_input.php?opened_file=<? echo $opened_file ?>">
      </map>
      <br>
      </td>

      <!-- Results -->
      <td valign="center" align="center">
      <font face="Verdana, Arial, Helvetica, sans-serif" size="3" color="blue">
      <b>
      <?php
        if ($translation_ok)
	{
	  echo "Choose another tool or<br>\n";
	  echo "Return to a previous step<br>\n";
	} else {
	  echo "Go back and correct<br>\n";
	  echo "your specification file<br>\n";
	}
      ?>
      </b>
      </font>  
      </td>

      <!-- Options -->
      <td valign="top" align="center" width="33%" height="200">
      <img src="images/return.gif">
      <br><br><br>
      <a href="expert.php"><img src="images/file_selection.gif" border="0"></a>
      <br><br>
      <?
        if ($translation_ok)
          echo "<input type=\"image\" src=\"images/tool_options.gif\" name=\"tool_opts\" value=\"tool_opts\">\n";
        else
          echo "<img src=\"images/tool_options_d.gif\">\n";
      ?>
      </td>
    </tr>

    <!-- Bottom Bar -->
    <tr>
      <td align="center" valign="top" colspan=3 bgcolor="#00008C">
      <img src="images/bottombar.gif">
      </td>
    </tr>
  </table>
</form>

</td>
</tr>
</table>

</body>
</html>
