<?
// Pre-defined variables/constants
$predefpath="./testfiles/";
$tempdir="./tempdir/";
$extension="*.hlpsl";
$file_length = 51200; // 50 Kbytes

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

  <form name="forminput" method="post" action="expertPROVA.php" ENCTYPE="multipart/form-data">

  <table width="100%" border="2" align="center" bordercolor="#00008C" cellpadding="3" cellspacing="0" bgcolor="#8E8FC0">
  <!-- Top Bar -->
  <tr>
  <td align="center" valign="middle" colspan=3 bgcolor="#00008C">
    <img src="images/topbar_expert_d.gif" border="0" usemap="#Map2">
    <map name="Map2">
      <area shape="rect" coords="554,37,665,68" href="basic.php">
    </map>
  </td>
  </tr>


  <!-- Text Area Buttons -->
  <?php
    // If we are in "edition mode" the edit button  
    // should be disabled and the save button enabled  
    if ($edit_file_x != "")
    {
      echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$opened_file."\">";
      echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
      echo "<input src=\"images/save_file.gif\" value=\"save_file\" name=\"save_file\" type=image>";
    } else {
      // If we have an opened file that is not been edited, the edit button 
      // should be enabled and the save button should be disabled 
      if ($opened_file)
      {
        echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$opened_file."\">";
        echo "<input src=\"images/edit_file.gif\" value=\"edit_file\" name=\"edit_file\" type=image>";
        echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
      } else {
        // If there is no opened file both buttons should be disabled
        echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$opened_file."\">";
        echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
        echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
      }
    }
  ?>
  <br>
  </td>
  </tr>
  
  <!-- File selection -->
  <td valign="top" align="center" height="200"  width="33%">
  <img src="images/files.gif">
  <br>
  
  <!-- Test Files -->
  <select name="test_file">
    <option value="">casa</option>
    <?php
      exec ("cd ".$predefpath.";ls ".$extension,$tabres);
      foreach ($tabres as $res)
      { 
        echo "<option value=\"".$res."\">".substr($res,0,strrpos($res,"."))."</option>";
      }
    ?>
  </select>
  <?php echo $_POST['test_file'];  ?>

  <br><br>
  <input src="images/load_file.gif" name="load_test" value="true"  type=image>
  <br><br>

  <!-- Custom User Files -->
  <input type="hidden" name="MAX_FILE_SIZE" value="<? echo $file_length ?>">
  <input type="file" name="user_file" accept="text/hlpsl">
  <br><br>
  <input src="images/load_file.gif" name="load_user" value="true" type=image>
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
