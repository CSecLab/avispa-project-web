<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once('./include/config.php');

// After a sucessful run, the user can return to the previous window and change
// the HLPSL. In case he introduces an error in the file, a new IF will not be
// generated. If the interface does not erase old files, we can present an old
// IF as the result of a new translation. That's why we need to erase old IFs,
// as well as other file types, each time we enter in the input module.
$avispaTool->unlink($_POST['opened_file'].".if");
$avispaTool->unlink($_POST['opened_file'].".atse.atk");
$avispaTool->unlink($_POST['opened_file'].".ofmc.atk");
$avispaTool->unlink($_POST['opened_file'].".satmc.atk");
$avispaTool->unlink($_POST['opened_file'].".ta4sp.atk");

// If after edition the user pressed the save button we
// have to save the contents of the textinputarea to a
// temp_file and set this tempfile as our opened_file
if ($_POST['save_file_x'] != "")
{
/*** SANITIZATION ***
 * 
 *  When file has been edited by the user.
 */
	// Save file after edition.
	$_POST['opened_file'] = $avispaTool->saveOpenedFile($_POST['textinputarea']);
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
					<form name="forminput" method="post" action="expert.php" ENCTYPE="multipart/form-data">

						<table width="100%" border="2" align="center" bordercolor="#00008C" cellpadding="3" cellspacing="0" bgcolor="#8E8FC0">
						
						<!-- Top Bar -->
							<tr>
								<td align="center" valign="middle" colspan=3 bgcolor="#00008C">
									<img src="images/topbar_expert_d.gif" border="0" usemap="#Map2" />
								    <map name="Map2">
								      <area shape="rect" coords="554,37,665,68" href="basic.php">
								    </map>
								</td>
							</tr>

							  <!-- Text Area -->
							<tr>
								<td valign="top" align="center" height="230" width="100%" colspan="3">
									<font size="2">
									<?php
							    		// Deciding the title for the textarea. If a file has been loaded by   
							    		// the user the title will change from "Instructions" to "Protocol".
							    		if (   ((isset($_FILES['user_file']) && !empty($_FILES['user_file']['name'])) && ($_POST['load_user_x'] != "")) // user_file has a value and Load File was pressed 
							        		|| ($_POST['test_file'] && ($_POST['load_test_x'] != "")) // test_file has a value and Load File was pressed
							        		|| ($_POST['edit_file_x'] != "")                 // Edit file was pressed
							        		|| ($_POST['save_file_x'] != ""))                // Save file was pressed
							      			echo "<img align=\"left\" src=\"images/protocol.gif\" />";
							    		else
							      			echo "<img align=\"left\" src=\"images/instructions.gif\" />";
							  		?>
							
							  		<br><br>
							
							  		<textarea name="textinputarea"	rows="<?php echo($textarea_rows); ?>" cols="<?php echo($textarea_cols); ?>"
							  		<?php
							    		// Deciding if the textarea should be editable or not
							    		if ($_POST['edit_file_x'] == "")
							      			echo " readonly";
									    // The ">" symmbol is the end of the textarea. Do not add space between 
									    // the ">" and the "<?" or spaces will be added in the beginning of the textarea
							  		?>><?php
									  	// If the user has choosen his own custom file and pressed the
									    // corresponding load buttom we have to load the file in the textarea
									    if ( (isset($_FILES['user_file']) && !empty($_FILES['user_file']['name'])) && ($_POST['load_user_x'] != ""))
									    {
									    	// Check file extension
									    	if($avispaTool->checkFileExtension($_FILES['user_file']['name']))
									    	{
									    	
									    		$avispaTool->createNewOpenedFile();
									    	
									    		// Try to move the file to the working file
									    		// If the move operation fail, print some debugging info
									    		if (@move_uploaded_file($_FILES['user_file']['tmp_name'], $avispaTool->getOpenedFile()))
									    		{
/*** SANITIZATION ***
 *
 *  When custom file has been uploaded by the user.
 */
									    			$_POST['opened_file'] = $avispaTool->sanitizeFile($avispaTool->getOpenedFile());
									    			// Show the file in the textarea
									    			if (!@readfile($avispaTool->getOpenedFile()))
									    				echo "File ".$avispaTool->getOpenedFile()." sucessfuly uploaded but it cannot be opened";
									    		}
									    		else
									    		{
									    			$_POST['opened_file'] = "";
									    			// Print debug info and exit
									    			echo "File move operation was not sucessful.\n\n";
									    			echo "Here is some more debugging info:\n";
									    			print_r($_FILES);
									    			exit;
									    		}
									    	}
									    	else
									    	{
									    		// Print debug info and exit
									    		echo "File ".$_FILES['user_file']['name']." (extension) not allowed.\n\n";
									    		echo "Only HLPSL files are allowed.\n";
									    		//exit;
									    	}
									    } 
									    else
										{
											// If the user has changed the test file selection and pressed
											// the corresponding load button we should load the file in the textarea
											if ($_POST['test_file'] && ($_POST['load_test_x'] != ""))
											{
												// Copy TestFile from predefined path to tempdir
												$avispaTool->copyTestFile();
/*** SANITIZATION ***
 *
 *  When a test file has been chosen by the user.
 */
												$_POST['opened_file'] = $avispaTool->sanitizeFile($avispaTool->getOpenedFile());
													
												if (!@readfile($avispaTool->getOpenedFile()))
													echo "Test File ".basename($avispaTool->getTestFile())." cannot be opened";
											}
											else
											{
												// If the form was resubmited by any other mean than by
												// pressing one of the load buttons, for instance by pressing the
												// Edit button, we should show the file in the textarea.
												if ($_POST['opened_file'] && ($_POST['load_test_x'] == "") && ($_POST['load_user_x'] == ""))
												{
													if (!@readfile($avispaTool->getOpenedFile()))
														echo "File ".$avispaTool->getOpenedFile()." cannot be opened";
									
												} else {
													// No valid selections were made, print an usage message
													if (!@readfile("./avispa_basic_tool.txt"))
														echo "Help File cannot be opened";
													$_POST['opened_file'] = "";
												}
											}
										}
									?></textarea>
							  </font>
							  <br><br>

							  <!-- Text Area Buttons -->
							  <?php
							  	// If we are in "edition mode" the edit button  
							    // should be disabled and the save button enabled  
							    if ($_POST['edit_file_x'] != "")
							    {
							      echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$avispaTool->getOpenedFile()."\">";
							      echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
							      echo "<input src=\"images/save_file.gif\" value=\"save_file\" name=\"save_file\" type=image>";
							    }
							    else
							    {
							      // If we have an opened file that is not been edited, the edit button 
							      // should be enabled and the save button should be disabled 
							      if ($_POST['opened_file'])
							      {
							        echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$avispaTool->getOpenedFile()."\">";
							        echo "<input src=\"images/edit_file.gif\" value=\"edit_file\" name=\"edit_file\" type=image>";
							        echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
							      }
							      else
							      {
							        // If there is no opened file both buttons should be disabled
							        echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$avispaTool->getOpenedFile()."\">";
							        echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
							        echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
							      }
							    }
							  ?>
							  <br>
							</td>
						</tr>
  
						<!-- Tools Selection -->
						<tr>
							<td valign="top" align="center" width="33%">
								<img src="images/tools.gif">
								<br>  
								  <?php
								    if ($_POST['opened_file'] && ($_POST['edit_file_x'] == ""))
								      echo"<img src=\"images/tools_buttons.gif\" border=\"0\" usemap=\"#Map\">\n";
								    else
								      echo"<img src=\"images/tools_buttons_d.gif\" border=\"0\" usemap=\"#Map\">\n";
								  ?>
								<map name="Map">
								<?php
								    if ($_POST['opened_file'] && ($_POST['edit_file_x'] == ""))
								    {
								      echo "<area shape=\"rect\" coords=\"113,46,187,67\" href=\"hlpsl2if_input.php?opened_file=".$avispaTool->getOpenedFile()."\">";
								      echo "<area shape=\"rect\" coords=\"7,141,71,164\" href=\"ofmc_input.php?opened_file=".$avispaTool->getOpenedFile()."\">";
								      echo "<area shape=\"rect\" coords=\"79,141,145,162\" href=\"atse_input.php?opened_file=".$avispaTool->getOpenedFile()."\">";
								      echo "<area shape=\"rect\" coords=\"155,141,219,162\" href=\"satmc_input.php?opened_file=".$avispaTool->getOpenedFile()."\">";
								      echo "<area shape=\"rect\" coords=\"229,141,293,162\" href=\"ta4sp_input.php?opened_file=".$avispaTool->getOpenedFile()."\">";
								    }
								?>
								</map>
								<br>
							</td>
						
							<!-- Help Center -->
							<td valign="center" align="center">
								<font face="Verdana, Arial, Helvetica, sans-serif" size="3" color="blue">
									<b>
									  <?php
									    if ($_POST['edit_file_x'] != "")
									    {
									      echo "Save the file after edition<br>";
									      echo "for the changes to take effect<br>";
									    } else {
									      // If the user has an opened file
									      if ($_POST['opened_file'])
									      {
									        echo "Edit the loaded file or<br>";
									        echo "choose a tool<br>";
									      } else {
									        echo "Load a test or a user file<br>";
									      }
									    }
									  ?>
									</b>
								</font>
							</td>
						
							<!-- File selection -->
							<td valign="top" align="center" height="200"  width="33%">
								<img src="images/files.gif" />
								<br>
						  
							<!-- Test Files -->
								<select name="test_file">
						  			<option value="">None</option>
									    <?php
									      //exec ("cd ".$predefpath.";ls ".$extension,$tabres);
										$tabres = glob($avispaTool->getPredefpath()."*".$avispaTool->getExtension());
										foreach ($tabres as $res)
											echo "<option value=\"".basename($res)."\">".basename(substr($res,0,strrpos(($res),".")))."</option>";
										?>
								</select>
								<br><br>
								<input src="images/load_file.gif" name="load_test" value="true"  type=image />
								<br><br>
						
							<!-- Custom User Files -->
								<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo ($avispaTool->getFileLength()); ?>">
								<input type="file" name="user_file" accept="text/hlpsl">
								<br><br>
								<input src="images/load_file.gif" name="load_user" value="true" type=image />
							</td>
						</tr>

						<!-- Bottom Bar -->
						<tr>
							<td align="center" valign="top" colspan=3 bgcolor="#00008C">
					  			<img src="images/bottombar.gif" />
							</td>
						</tr>
					</table>
				</form>
			</td>
		</tr>
	</table>
</body>
</html>
