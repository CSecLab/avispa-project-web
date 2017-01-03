<?php
$tempdir="./tempdir/";
$predefpath="./testfiles/";
$extension="*.hlpsl";
$file_length = 51200; // 50 Kbytes

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

// After a sucessful run, the user can return to the previous window and change 
// the HLPSL. In case he introduces an error in the file, a new IF will not be 
// generated. If the interface does not erase old files, we can present an old
// IF as the result of a new translation. That's why we need to erase old IFs,
// as well as other file types, each time we enter in the input module.
@unlink ($_GET['opened_file'].".if");

// If after edition the user pressed the save button we
// have to save the contents of the textinputarea to a 
// temp_file and set this tempfile as our opened_file 
if ($_POST['save_file_x'] != "")
{
  $handle = fopen ($_GET['opened_file'],"w");
  $textinputarea = implode("",(explode("\r",$textinputarea)));
  If (!fwrite($handle, stripcslashes($textinputarea), $file_length))
  {
    echo "Saving Operation failed\n";
    echo "Cannot write to file ".$_GET['opened_file'];
  }
  fclose($handle);
}

// If the page is been loaded for the first time
// set the default values for the tool options
if (!$changing)
{
  $_POST['types'] = 1;
  $_POST['inits'] = 1;
  $_POST['rules'] = 1;
  $_POST['goals'] = 1;
}
?>

<html>
<head>
<title>AVISPA Web Tool</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript">

function UpdateOptions(id)
{
  switch (id) {
   case 1 :
            if (document.formopts.types.checked)
               document.forminput.types.value = 1;
            else
               document.forminput.types.value = 0;

            if (document.formopts.inits.checked)
               document.forminput.inits.value = 1;
            else
               document.forminput.inits.value = 0;

            if (document.formopts.rules.checked)
               document.forminput.rules.value = 1;
            else
               document.forminput.rules.value = 0;

            if (document.formopts.goals.checked)
               document.forminput.goals.value = 1;
            else
               document.forminput.goals.value = 0;
            break;
   case 2 :
            if (document.formopts.types.checked)
               document.formoutput.types.value = 1;
            else
               document.formoutput.types.value = 0;

            if (document.formopts.inits.checked)
               document.formoutput.inits.value = 1;
            else
               document.formoutput.inits.value = 0;

            if (document.formopts.rules.checked)
               document.formoutput.rules.value = 1;
            else
               document.formoutput.rules.value = 0;

            if (document.formopts.goals.checked)
               document.formoutput.goals.value = 1;
            else
               document.formoutput.goals.value = 0;
            break;
   default: alert ("UpdateOptions: unknown value!");
  }
}

</script>
</head>

<body bgcolor="#FFFFFF" text="#000000">

<table width="1000" border="0" align="center" cellspacing="0" cellpadding="0">
<tr>
<td valign="top">

    <form name="forminput" method="post" action="hlpsl2if_input.php" onsubmit="UpdateOptions(1)">

    <input type="hidden" name="opened_file" value="<?=$_GET['opened_file']?>">
    <input type="hidden" name="changing" value=1>
    <input type="hidden" name="types" value=<?=$_POST['types']?>>
    <input type="hidden" name="inits" value=<?=$_POST['inits']?>>
    <input type="hidden" name="rules" value=<?=$_POST['rules']?>>
    <input type="hidden" name="goals" value=<?=$_POST['goals']?>>

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

    <!-- Text Area -->
    <tr>
    <td valign="top" align="center" height="230" width="100%" colspan="3">
      <font size="2">
      <img align="left" src="images/protocol.gif">
      <br><br>

      <textarea name="textinputarea" rows="<?=$textarea_rows?>" cols="<?=$textarea_cols?>" wrap="off"
      <?
        // Deciding if the textarea should be editable or not
        if ($edit_file_x == "")
          echo " readonly";
      ?>
      ><?php
        // Show the opened file in the textarea
        if(!file_exists($tempdir.basename($_GET['opened_file'])) || !readfile($tempdir.basename($_GET['opened_file'])))
          echo "File ".$_GET['opened_file']." cannot be opened";
      ?>
      </textarea>
      </font>

      <br><br>

      <?php
        if ($edit_file_x != "")
        {
          echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$_GET['opened_file']."\">";
          echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
          echo "<input src=\"images/save_file.gif\" value=\"save_file\" name=\"save_file\" type=image>";
        } else {
          echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$_GET['opened_file']."\">";
          echo "<input src=\"images/edit_file.gif\" value=\"edit_file\" name=\"edit_file\" type=image>";
          echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
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
      <?php
       if ($edit_file_x == "")
        echo"<img src=\"images/tools_buttons_hlpsl2if.gif\" border=\"0\" usemap=\"#Map\">\n";
       else
        echo"<img src=\"images/tools_buttons_d.gif\" border=\"0\">\n";
      ?> 
      
      <map name="Map">
        <area shape="rect" coords="7,141,71,164" href="ofmc_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
        <area shape="rect" coords="79,141,145,162" href="atse_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
        <area shape="rect" coords="155,141,219,162" href="satmc_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
        <area shape="rect" coords="229,141,293,162" href="ta4sp_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
      </map>
      <br>
    </td>

  </form>

  <form name="formoutput" method="post" action="hlpsl2if_output.php" onsubmit="UpdateOptions(2)">
    <input type="hidden" name="opened_file" value="<?=$_GET['opened_file']?>">
    <input type="hidden" name="changing" value=1>
    <input type="hidden" name="types" value=<?=$_POST['types']?>>
    <input type="hidden" name="inits" value=<?=$_POST['inits']?>>
    <input type="hidden" name="rules" value=<?=$_POST['rules']?>>
    <input type="hidden" name="goals" value=<?=$_POST['goals']?>>

    <!-- Execution -->
    <td valign="center" align="center">
      <br><br>
      <font face="Verdana, Arial, Helvetica, sans-serif" size="3" color="blue">
      <b>
      <?
        if ($edit_file_x != "")
        {
          echo "Save the file after edition<br>";
          echo "for the changes to take effect<br>";
        } else {
          echo "Choose tool options and<br>";
          echo "press execute<br>";
        }
      ?>
      </b>
      </font>
      <br><br>
      
      <!-- Execute Button -->
      <?php
        // If file is been edited disable the execute button
        if ($edit_file_x != "")
          echo "<input src=\"images/execute_d.gif\" name=\"execute\" value=\"execute\"  type=image disabled>";
        else
          echo "<input src=\"images/execute.gif\" name=\"execute\" value=\"execute\"  type=image>";  
      ?>
    </td>
  </form>

  <!-- Tool Options -->
  <form name="formopts" method="post" action="hlpsl2if_input.php">
  <td valign="top" align="center" width="33%" height="200">
    <img src="images/options.gif">
    <br><br>
    <p align="center">
    <font face="Arial, Helvetica, sans-serif" size="2">
    <table>
    <tr>
      <td><input type="checkbox" name="types" <? if ($_POST['types']) echo "checked" ?>></td>
      <td><font size="2">Types section<br></font></td>
    </tr>
    <tr>
      <td><input type="checkbox" name="inits" <? if ($_POST['inits']) echo "checked" ?>></td>
      <td><font size="2">Inits section<br></font></td>
    </tr>
    <tr>
      <td><input type="checkbox" name="rules" <? if ($_POST['rules']) echo "checked" ?>></td>
      <td><font size="2">Rules section<br></font></td>
    </tr>
    <tr>
      <td><input type="checkbox" name="goals" <? if ($_POST['goals']) echo "checked" ?>></td>
      <td><font size="2">Goals section<br></font></td>
    </tr>
    </table>
  </td>
  </form>
</tr>

<!-- Bottom Bar -->
<tr>  
  <td align="center" valign="top" colspan=3 bgcolor="#00008C">
  <img src="images/bottombar.gif">
  </td>
</tr>
</table>

</td>
</tr>
</table>

</body>
</html>
