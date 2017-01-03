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
@unlink ($_GET['opened_file'].".satmc.atk");
@unlink ($_GET['opened_file'].".satmc.png");
@unlink ($_GET['opened_file'].".satmc.ps.gz");


// If after edition the user pressed the save button we
// have to save the contents of the textinputarea to the opened_file 
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

// Defining default values for fields
if (!$_POST['changing'])
{
  $_POST['depth'] = 11;
  $_POST['ct']    = 1;
  $_POST['solver']= "chaff";
  $_POST['enc']   = "gp-efa";
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
            if (document.formopts.mutex.checked)
               document.forminput.mutex.value = 1;
            else
	       document.forminput.mutex.value = 0;

	    if (document.formopts.ct.checked)
               document.forminput.ct.value = 1;
	    else
	       document.forminput.ct.value = 0;

            if (document.formopts.oi.checked)
               document.forminput.oi.value = 1;
	    else
	       document.forminput.oi.value = 0;

	    document.forminput.solver.value  = document.formopts.solver.value;
            document.forminput.depth.value = document.formopts.depth.value;
            break;
   case 2 :
            if (document.formopts.mutex.checked)
               document.formoutput.mutex.value = 1;
	    else
	       document.formoutput.mutex.value = 0;

	    if (document.formopts.ct.checked)
               document.formoutput.ct.value = 1;
	    else
	       document.formoutput.ct.value = 0;

            if (document.formopts.oi.checked)
               document.formoutput.oi.value = 1;
	    else
	       document.formoutput.oi.value = 0;

	    document.formoutput.solver.value  = document.formopts.solver.value;
            document.formoutput.depth.value = document.formopts.depth.value;
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

    <form name="forminput" method="post" action="satmc_input.php" onsubmit="UpdateOptions(1)">

	<input type="hidden" name="opened_file" value="<?=$_GET['opened_file']?>">
	<input type="hidden" name="changing" value=1>
	<input type="hidden" name="solver" value="<?=$_POST['solver']?>">
	<input type="hidden" name="depth" value="<?=$_POST['depth']?>">
	<input type="hidden" name="mutex" value=<?=$_POST['mutex']?>>
	<input type="hidden" name="ct" value=<?=$_POST['ct']?>>
	<input type="hidden" name="oi" value=<?=$_POST['oi']?>>

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
	  if ($_POST['edit_file_x'] == "")
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
	    if ($_POST['edit_file_x'] != "")
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
            if ($_POST['edit_file_x'] == "")
              echo"<img src=\"images/tools_buttons_satmc.gif\" border=\"0\" usemap=\"#Map\">\n";
            else
              echo"<img src=\"images/tools_buttons_d.gif\" border=\"0\">\n";
          ?> 
          <map name="Map">
	    <area shape="rect" coords="113,46,187,67" href="hlpsl2if_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
	    <area shape="rect" coords="7,141,71,164" href="ofmc_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
            <area shape="rect" coords="79,141,145,162" href="atse_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
            <area shape="rect" coords="229,141,293,162" href="ta4sp_input.php?opened_file=<? echo $_GET['opened_file'] ?>">
          </map>
	  <br>
          </td>
	  </form>

	  <!-- Execution -->
	  <form name="formoutput" method="post" action="satmc_output.php" onsubmit="UpdateOptions(2)">
	    <input type="hidden" name="opened_file" value="<?=$_GET['opened_file']?>">
	    <input type="hidden" name="changing" value=1>
	    <input type="hidden" name="solver" value="<?=$_POST['solver']?>">
	    <input type="hidden" name="depth" value="<?=$_POST['depth']?>">
	    <input type="hidden" name="mutex" value=<?=$_POST['mutex']?>>
	    <input type="hidden" name="ct" value=<?=$_POST['ct']?>>
	    <input type="hidden" name="oi" value=<?=$_POST['oi']?>>

	    <td valign="center" align="center">
	    <br><br>
	    <font face="Verdana, Arial, Helvetica, sans-serif" size="3" color="blue">
	    <b>
	    <?
	    if ($_POST['edit_file_x'] != "")
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
              if ($_POST['edit_file_x'] != "")
                echo "<input src=\"images/execute_d.gif\" name=\"execute\" value=\"execute\"  type=image disabled>";
              else
                echo "<input src=\"images/execute.gif\" name=\"execute\" value=\"execute\"  type=image>";  
              ?>
	    </td>
	  </form>

	  <!-- Tool Options -->
	  <form name="formopts" method="post" action="satmc_input.php">
	  <td valign="top" align="center" width="33%" height="200">
          <img src="images/options.gif">
          <br>

	  <p align="center">
	  <table>

          <tr>
          <td align="right">
	  <font face="Arial, Helvetica, sans-serif" size="2">
          Solver:
	  </font>
	  </td>
          <td>
	   <select  name="solver" size=1>
             <option value="chaff" <? if($_POST['solver'] == "chaff") echo "selected" ?>>zChaff
             <option value="mchaff" <? if($_POST['solver'] == "mchaff") echo "selected" ?>>mChaff
             <option value="sim" <? if($_POST['solver'] == "sim") echo "selected" ?>>SIM
             <option value="sato" <? if($_POST['solver'] == "sato") echo "selected" ?>>SATO
           </select>
	  </td>
          </tr>
	  <tr>
          <td align="right">
	  <font face="Arial, Helvetica, sans-serif" size="2">
          Depth:
	  </font>
	  </td>
          <td>
	  <input type="text" name="depth" value="<?=$_POST['depth']?>" size="5" maxlength="5" >
	  </td>
          </tr>
          </table>

          <br>

	  <table>
          <tr>
          <td>
	    <input type="checkbox" name="mutex" <? if ($_POST['mutex']) echo "checked" ?>>
	  </td>
          <td>
	  <font face="Arial, Helvetica, sans-serif" size="2">
	  Abstraction/Refinement
	  </font>
	  </td>
          </tr>
	  <tr>
          <td>
	    <input type="checkbox" name="ct" <? if ($_POST['ct']) echo "checked" ?>>
	  </td>
          <td>
	  <font face="Arial, Helvetica, sans-serif" size="2">
	  Compound Types
	  </font>
	  </td>
          </tr>
	  <tr>
          <td>
	  <input type="checkbox" name="oi" <? if ($_POST['oi']) echo "checked" ?>>
	  </td>
          <td>
	  <font face="Arial, Helvetica, sans-serif" size="2">
	  Optimized Intruder
	  </font>
	  </td>
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
