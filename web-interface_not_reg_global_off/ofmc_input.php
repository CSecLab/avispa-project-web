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
@unlink ($opened_file.".if");
@unlink ($opened_file.".ofmc.atk");
@unlink ($opened_file.".ofmc.png");
@unlink ($opened_file.".ofmc.ps.gz");


// If after edition the user pressed the save button we
// have to save the contents of the textinputarea to the opened_file 
if ($save_file_x != "")
{
  $handle = fopen ($opened_file,"w");
  $textinputarea = implode("",(explode("\r",$textinputarea)));
  If (!fwrite($handle, stripcslashes($textinputarea), $file_length))
  {
    echo "Saving Operation failed\n";
    echo "Cannot write to file ".$opened_file;
  }
  fclose($handle);
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
            if (document.formopts.sessco.checked)
               document.forminput.sessco.value = 1;
            else
               document.forminput.sessco.value = 0;

            document.forminput.depth.value = document.formopts.depth.value;
            document.forminput.path.value  = document.formopts.path.value;
            break;
   case 2 :
            if (document.formopts.sessco.checked)
               document.formoutput.sessco.value = 1;
	    else
               document.formoutput.sessco.value = 0;

            document.formoutput.depth.value = document.formopts.depth.value;
            document.formoutput.path.value  = document.formopts.path.value;
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

    <form name="forminput" method="post" action="ofmc_input.php" onsubmit="UpdateOptions(1)">

	<input type="hidden" name="opened_file" value="<?=$opened_file?>">
	<input type="hidden" name="sessco" value=<?=$sessco?>>
	<input type="hidden" name="depth" value="<?=$depth?>">
	<input type="hidden" name="path" value="<?=$path?>">

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
          if (!readfile($opened_file))
            echo "File ".$opened_file." cannot be opened";
          ?>
          </textarea>
	  </font>

	  <br><br>

          <?php
	    if ($edit_file_x != "")
	    {
	      echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$opened_file."\">";
	      echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
	      echo "<input src=\"images/save_file.gif\" value=\"save_file\" name=\"save_file\" type=image>";
	    } else {
	      echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$opened_file."\">";
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
              echo"<img src=\"images/tools_buttons_ofmc.gif\" border=\"0\" usemap=\"#Map\">\n";
            else
              echo"<img src=\"images/tools_buttons_d.gif\" border=\"0\">\n";
          ?> 
          <map name="Map">
	  <area shape="rect" coords="113,46,187,67" href="hlpsl2if_input.php?opened_file=<? echo $opened_file ?>">
          <area shape="rect" coords="79,141,145,162" href="atse_input.php?opened_file=<? echo $opened_file ?>">
          <area shape="rect" coords="155,141,219,162" href="satmc_input.php?opened_file=<? echo $opened_file ?>">
          <area shape="rect" coords="229,141,293,162" href="ta4sp_input.php?opened_file=<? echo $opened_file ?>">
          </map>
	  <br>
          </td>
	  </form>

	  <!-- Execution -->
	  <form name="formoutput" method="post" action="ofmc_output.php" onsubmit="UpdateOptions(2)">
	    <input type="hidden" name="opened_file" value="<?=$opened_file?>">
	    <input type="hidden" name="sessco" value=<?=$sessco?>>
	    <input type="hidden" name="depth" value="<?=$depth?>">
	    <input type="hidden" name="path" value="<?=$path?>">

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
	  <form name="formopts" method="post" action="ofmc_input.php">
	  <td valign="top" align="center" width="33%" height="200">
          <img src="images/options.gif">
          <br><br>

	  <p align="center">
	  <table>
          <tr>
          <td>
	    <input type="checkbox" name="sessco" <? if ($sessco) echo "checked" ?>>
	  </td>
          <td>
	    <font face="Arial, Helvetica, sans-serif" size="2">
	    Session Compilation
	    </font>
	  </td>
          </tr>
          </table>

          <br>

	  <table>
	  <tr>
	  <td><font face="Arial, Helvetica, sans-serif" size="2">Depth:</font></td>
	  <td><input type="text" name="depth" value="<?=$depth?>" size="5" maxlength="5" ></td>
	  </tr>
          <tr>
	  <td><font face="Arial, Helvetica, sans-serif" size="2">Path:</font></td>
	  <td><input type="text" name="path" value="<?=$path?>" size="15" maxlength="25" ></td>
	  </tr>
          </table>
	  </p>

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
