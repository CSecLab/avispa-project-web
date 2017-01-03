<?
$predefpath="./testfiles/";
$extension="*.hlpsl";
$bindir="./bin/";
$tempdir="./tempdir/";
$hlpsl2if="hlpsl2if";
$ofmc="ofmc.sh";

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
if ($_POST['sessco']) $args = $args."-sessco ";
if ($_POST['depth'])  $args = $args."-d ".$_POST['depth']." ";
if ($_POST['path'])   $args = $args."-p ".$_POST['path']. " ";

if ($avispawebsetting == remote){

  $copy_hlpsl_remote = "scp -p".$ssh_options.$localwebinterfacepath.$_POST['opened_file']." ".
                                $remoteuser."@".$remoteip.":".$remotewebinterfacepath.$_POST['opened_file'];

  $hlpsl2if_cmd = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                        $remotewebinterfacepath.$bindir.$hlpsl2if." --all ".
                        $remotewebinterfacepath.$_POST['opened_file']." 2>&1";

  $copy_if_local = "scp -p".$ssh_options.$remoteuser."@".$remoteip.":".
                            $remotewebinterfacepath.$_POST['opened_file'].".if ".
                            $localwebinterfacepath.$_POST['opened_file'].".if";

  $remove_if_remote = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                        "rm ".$remotewebinterfacepath.$_POST['opened_file'].".if";

  $remove_hlpsl_remote = "ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                        "rm ".$remotewebinterfacepath.$_POST['opened_file'];

  $ofmc_cmd = "(ssh".$ssh_options.$remoteuser."@".$remoteip." ".
                     $remotewebinterfacepath.$bindir.$ofmc." ".
                     $remotewebinterfacepath.$_POST['opened_file'].".if ".$args.") > ".
                     $localwebinterfacepath.$_POST['opened_file'].".ofmc.atk";

}
elseif ($avispawebsetting == local)
{
  $hlpsl2if_cmd = $bindir.$hlpsl2if." --all ".$_POST['opened_file']." 2>&1";
  $ofmc_cmd = $bindir.$ofmc." ".$localwebinterfacepath.$_POST['opened_file'].".if".$args."> ".$localwebinterfacepath.$_POST['opened_file'].".ofmc.atk";
} 
else 
  echo "AVISPA_WEB_SETTING variable not properly set in config.ini<br>";


// Return the summary from a tool output file
function Summary($atk_file)
{

  // Open the output file
  $file = fopen ($atk_file, "r");
  if (!$file) {
    echo "Can not open ".$atk_file." file.\n";
    exit;
  }

  // Search for the ".ofmc.atk"SUMMARY keyword inside the file
  $found = false;
  while (!feof($file) && (!$found))
  {
    $line = trim(fgets ($file,1024));
    if ($line == "SUMMARY")
      $found = true;
  }

  // If SUMMARY was found return the result
  if ($found)
  {
    do
    {
      $line = trim(fgets ($file,1024));
    } while ($line=="");
    return $line;
  } else {
    //echo "Section SUMMARY not found in this file!\n";
    return "Error";
  }
}
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

    <form name="forminput" method="post" action="ofmc_input.php">

      <input type="hidden" name="opened_file" value="<?=$_POST['opened_file']?>">
      <input type="hidden" name="sessco" value=<?=$_POST['sessco']?>>
      <input type="hidden" name="depth" value="<?=$_POST['depth']?>">
      <input type="hidden" name="path" value="<?=$_POST['path']?>">


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
          if ($avispawebsetting == remote){
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
	  
          $translation_ok = file_exists($_POST['opened_file'].".if");
          
	  // If the translation was sucessuful show the file in the textarea,
          // otherwise print the error message from the HLPSL2IF
          if ($translation_ok) 
          {
            // Execute OFMC
            if ($debug) echo "OFMC: ".$ofmc_cmd."\n";
            exec($ofmc_cmd);

            // Show Attack file
	    if (!readfile($_POST['opened_file'].".ofmc.atk"))
              echo "File ".$_POST['opened_file'].".ofmc.atk"." cannot be opened";

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

	<!-- TextArea Buttons -->
	<a href=# onclick="window.open('showfile.php?file=<? echo $_POST['opened_file'] ?>&tool=ofmc','view_hlpsl','width=1000,height=600,left=12,top=84,menubar=0,status=0,location=0,toolbar=0,scrollbars=0,resizable=1');return(false)"><img src="images/view_hlpsl.gif" border="0"></a>
	
	<?php
          // Deciding if the IF, MSC and PS Buttons should be enabled or disabled 
          if ($translation_ok)
	  {
	    // IF Button
	    echo "<a href=# onclick=\"window.open('showfile.php?file=".$_POST['opened_file'].".if&tool=ofmc','view_if','width=1000,height=600,left=12,top=84,menubar=0,status=0,location=0,toolbar=0,scrollbars=0,resizable=0');return(false)\"><img src=\"images/view_if.gif\" border=\"0\"></a>\n"; 
	    
	    // MSC and PS Buttons
	    $ofmc_summary = Summary($_POST['opened_file'].".ofmc.atk");
	    if ($ofmc_summary=="UNSAFE")
	    {
	      echo "<a href=# onclick=\"window.open('showfile.php?image=".$_POST['opened_file'].".ofmc.atk&tool=ofmc','ofmc_msc','width=1000,height=600,left=12,top=84,menubar=0,status=0,location=0,toolbar=0,scrollbars=1,resizable=0');return(false)\"><img src=\"images/view_msc.gif\" border=\"0\"></a>\n";
	      
	      echo "<a href=# onclick=\"window.open('showfile.php?ps=".$_POST['opened_file'].".ofmc.atk&tool=ofmc','ofmc_ps','width=500,height=300,left=262,top=234,menubar=0,status=0,location=0,toolbar=0,scrollbars=1,resizable=0');return(false)\"><img src=\"images/view_ps.gif\" border=\"0\"></a>\n";
	      
	    } else {
	      echo "<img src=\"images/view_msc_d.gif\" border=\"0\">\n";
	      echo "<img src=\"images/view_ps_d.gif\" border=\"0\">\n";
	    }
	  } else {
	    echo "<img src=\"images/view_if_d.gif\" border=\"0\">\n";
            echo "<img src=\"images/view_msc_d.gif\" border=\"0\">\n";
	    echo "<img src=\"images/view_ps_d.gif\" border=\"0\">\n";
	  }
	?>
          
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
	    echo "<img src=\"images/trace_ofmc.gif\" border=\"0\" usemap=\"#Map\">\n";
	  else
	    echo "<img src=\"images/trace_hlpsl2if_error.gif\" border=\"0\">\n";
	?>
	<map name="Map">
         <area shape="rect" coords="79,141,145,162" href="atse_input.php?opened_file=<? echo $_POST['opened_file'] ?>">
         <area shape="rect" coords="155,141,219,162" href="satmc_input.php?opened_file=<? echo $_POST['opened_file'] ?>">
         <area shape="rect" coords="229,141,293,162" href="ta4sp_input.php?opened_file=<? echo $_POST['opened_file'] ?>">
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
