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
@unlink ($_GET['opened_file'].".ta4sp.atk");
@unlink ($_GET['opened_file'].".ta4sp.png");
@unlink ($_GET['opened_file'].".ta4sp.ps.gz");


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
  $_POST['two_agents_only'] = 1;
  $_POST['under_approx'] = 0;
  $_POST['over_approx'] = 1;
  $_POST['bound'] = 0;
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
            if (document.formopts.two_agents_only.checked)
               document.forminput.two_agents_only.value = 1;
            else
               document.forminput.two_agents_only.value = 0;

            if (document.formopts.over_approx.checked)
               document.forminput.over_approx.value = 1;
	    else
	       document.forminput.over_approx.value = 0;

	    if (document.formopts.under_approx.checked)
               document.forminput.under_approx.value = 1;
	    else
	       document.forminput.under_approx.value = 0;

            document.forminput.bound.value = document.formopts.bound.value;
            break;

   case 2 :
            if (document.formopts.two_agents_only.checked)
               document.formoutput.two_agents_only.value = 1;
	    else
	       document.formoutput.two_agents_only.value = 0;

            if (document.formopts.over_approx.checked)
               document.formoutput.over_approx.value = 1;
	    else
	       document.formoutput.over_approx.value = 0;

	    if (document.formopts.under_approx.checked)
               document.formoutput.under_approx.value = 1;
	    else
	       document.formoutput.under_approx.value = 0;

            document.formoutput.bound.value = document.formopts.bound.value;
            break;

   default: alert ("UpdateOptions: unknown value!");
  }
}

function UpdateRadio(id)
{
  switch (id) {
   case 1 :
            document.formopts.bound.disabled = false;
	    document.formopts.over_approx.checked = false;
	    document.formopts.bound.value = 1;
	    break;

   case 2 :
            document.formopts.bound.value = 0;
	    document.formopts.bound.disabled = true;
	    document.formopts.under_approx.checked = false;
	    break;

   default: alert ("UpdateRadio: unknown value!");
  }
}

</script>
</head>

<body bgcolor="#FFFFFF" text="#000000">

<table width="1000" border="0" align="center" cellspacing="0" cellpadding="0">
<tr>
<td valign="top">

    <form name="forminput" method="post" action="ta4sp_input.php" onsubmit="UpdateOptions(1)">

	<input type="hidden" name="opened_file" value="<?php echo $_GET['opened_file']?>">
	<input type="hidden" name="changing" value=1>
        <input type="hidden" name="two_agents_only" value=<?php echo $_POST['two_agents_only']?>>
	<input type="hidden" name="under_approx" value=<?php echo $_POST['under_approx']?>>
	<input type="hidden" name="over_approx" value=<?php echo $_POST['over_approx']?>>
	<input type="hidden" name="bound" value="<?php echo $_POST['bound']?>">

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

          <textarea name="textinputarea" rows="<?php echo $textarea_rows?>" cols="<?php echo $textarea_cols?>" wrap="off"
	  <?php
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
              echo"<img src=\"images/tools_buttons_ta4sp.gif\" border=\"0\" usemap=\"#Map\">\n";
            else
              echo"<img src=\"images/tools_buttons_d.gif\" border=\"0\">\n";
          ?> 
          <map name="Map">
	    <area shape="rect" coords="113,46,187,67" href="hlpsl2if_input.php?opened_file=<?php echo $_GET['opened_file'] ?>">
	    <area shape="rect" coords="7,141,71,164" href="ofmc_input.php?opened_file=<?php echo $_GET['opened_file'] ?>">
            <area shape="rect" coords="79,141,145,162" href="atse_input.php?opened_file=<?php echo $_GET['opened_file'] ?>">
            <area shape="rect" coords="155,141,219,162" href="satmc_input.php?opened_file=<?php echo $_GET['opened_file'] ?>">
          </map>
	  <br>
          </td>
	  </form>

	  <!-- Execution -->
	  <form name="formoutput" method="post" action="ta4sp_output.php" onsubmit="UpdateOptions(2)">
	    <input type="hidden" name="opened_file" value="<?php echo $_GET['opened_file']?>">
	    <input type="hidden" name="changing" value=1>
            <input type="hidden" name="two_agents_only" value=<?php echo $_POST['two_agents_only']?>>
	    <input type="hidden" name="under_approx" value=<?php echo $_POST['under_approx']?>>
	    <input type="hidden" name="over_approx" value=<?php echo $_POST['over_approx']?>>
	    <input type="hidden" name="bound" value="<?php echo $_POST['bound']?>">

	    <td valign="center" align="center">
	    <br><br>
	    <font face="Verdana, Arial, Helvetica, sans-serif" size="3" color="blue">
	    <b>
	    <?php
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
	  <form name="formopts" method="post" action="ta4sp_input.php">
	  <td valign="top" align="center" width="33%" height="200">
          <img src="images/options.gif">
          <br>

	  <p align="center">
	  <table>
          <tr>
          <td align="center">
          <center><font face="Arial, Helvetica, sans-serif" size="2" color="darkblue"><b>Verification Model</b></font></center>
          <input type="checkbox" name="two_agents_only" <?php if ($_POST['two_agents_only']) echo "checked" ?>>
          <font face="Arial, Helvetica, sans-serif" size="2">Two Agent Only<br></font>
          <br><br>
          </td>
          </tr>
	  <tr>
	  <td align="left">
          <center><font face="Arial, Helvetica, sans-serif" size="2" color="darkblue"><b>Intruder Knowledge</b></font></center>
          <input type="radio" name="over_approx" <?php if ($_POST['over_approx']) echo "checked" ?> onchange="UpdateRadio(2)">
	  <font face="Arial, Helvetica, sans-serif" size="2">Over-Approximation</font><br>
          <input type="radio" name="under_approx" <?php if ($_POST['under_approx']) echo "checked" ?> onchange="UpdateRadio(1)">
	  <font face="Arial, Helvetica, sans-serif" size="2">Under-Approximation</font><br>
          </td>
	  </tr>
	  <tr>
	  <td align="center">
	  <font face="Arial, Helvetica, sans-serif" size="2">Level</font>
	  <input type="text" name="bound" value="<?php echo $_POST['bound']?>" size="2" maxlength="5" <?php if (!$_POST['bound']) echo "disabled" ?>>
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
