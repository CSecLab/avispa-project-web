<?php
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

				<form name="forminput" method="post" action="basic.php" ENCTYPE="multipart/form-data">

					<table width="100%" border="2" align="center" bordercolor="#00008C"	cellpadding="3" cellspacing="0" bgcolor="#8E8FC0">
						<!-- Top Bar -->
						<tr>
							<td align="center" valign="middle" colspan=3 bgcolor="#00008C"><?php
							    // Deciding whether or not enable the basic mode buttom
							    // If a file is opened we should enable the basic button
							    // to allow the user to reset the interface to the basic mode
								if ( ((isset($_FILES['user_file']) && !empty($_FILES['user_file']['name'])) && ($_POST['load_user_x'] != "")) // user_file has a value and Load File was pressed
							      || ($_POST['test_file'] && ($_POST['load_test_x'] != ""))  // test_file has a value and Load File was pressed
							      || ($_POST['edit_file_x'] != "")                 // Edit file was pressed
							      || ($_POST['save_file_x'] != ""))                 // Save file was pressed
								{
									echo "<img src=\"images/topbar_basic.gif\" border=\"0\" usemap=\"#Map2\">\n";
									echo "<map name=\"Map2\">\n";
									echo "<area shape=\"rect\" coords=\"554,37,665,68\" href=\"basic.php\">\n";
									echo "<area shape=\"rect\" coords=\"672,37,785,68\" href=\"expert.php\">\n";
								}
								else
								{
									echo "<img src=\"images/topbar_basic_d.gif\" border=\"0\" usemap=\"#Map2\">\n";
									echo "<map name=\"Map2\">\n";
									echo "<area shape=\"rect\" coords=\"672,37,785,68\" href=\"expert.php\">\n";
								}
	      						?> </map>
							</td>
						</tr>

						<!-- Text Area -->
						<tr>
							<td valign="top" align="center" height="230" width="100%" colspan="3">
								<font size="2"><?php
								// Deciding the title for the textarea. If a file has been loaded by
								// the user the title will change from "Instructions" to "Protocol".
								if (   ((isset($_FILES['user_file']) && !empty($_FILES['user_file']['name'])) && ($_POST['load_user_x'] != "")) // user_file has a value and Load File was pressed
										|| ($_POST['test_file'] && ($_POST['load_test_x'] != "")) // test_file has a value and Load File was pressed
										|| ($_POST['edit_file_x'] != "")                 // Edit file was pressed
										|| ($_POST['save_file_x'] != ""))                 // Save file was pressed
									echo "<img align=\"left\" src=\"images/protocol.gif\">";
								else
									echo "<img align=\"left\" src=\"images/instructions.gif\">";
								?><br>
								<br>
								<textarea name="textinputarea"	rows="<?php echo($textarea_rows); ?>" cols="<?php echo($textarea_cols); ?>"
										<?php
										// Deciding if the textarea should be editable or not
										if ($_POST['edit_file_x'] == "")
											echo " readonly";
										// The ">" symmbol is the end of the textarea. Do not add space between
										// the ">" and the "<?php" or spaces will be added in the beginning of the textarea
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
												if (move_uploaded_file($_FILES['user_file']['tmp_name'], $avispaTool->getOpenedFile()))
												{
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
													if (!readfile("./avispa_basic_tool.txt"))
														echo "Help File cannot be opened";
													$_POST['opened_file'] = "";
												}
											}
										}
										?></textarea>
							</font> <br> <br> <!-- Text Area Buttons --> <?php
							// If we are in "edition mode" the edit button
							// should be disabled and the save button enabled
							if ($_POST['edit_file_x'] != "")
							{
								echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$avispaTool->getOpenedFile()."\">";
								echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
								echo "<input src=\"images/save_file.gif\" value=\"save_file\" name=\"save_file\" type=image>";
							} else {
								// If we have an opened file that is not been edited, the edit button
								// should be enabled and the save button should be disabled
								if ($_POST['opened_file'])
								{
									echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$avispaTool->getOpenedFile()."\">";
									echo "<input src=\"images/edit_file.gif\" value=\"edit_file\" name=\"edit_file\" type=image>";
									echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
								} else {
									// If there is no opened file both buttons should be disabled
									echo "<input type=\"hidden\" name=\"opened_file\" value=\"".$avispaTool->getOpenedFile()."\">";
									echo "<input src=\"images/edit_file_d.gif\" value=\"edit_file\" name=\"edit_file\" type=image disabled>";
									echo "<input src=\"images/save_file_d.gif\" value=\"save_file\" name=\"save_file\" type=image disabled>";
								}
							}
							?> <br>
							</td>
						</tr>

						<!-- Tools Selection -->
						<tr>
							<td valign="top" align="center" width="33%">
							
								<!-- Test File selection --> <img src="images/testfiles.gif"> <br>
								<br> <select name="test_file">
									<option value="">None</option>
									<?php
									// Execute a list command in the testfiles directory
									// and use the resulting list as selection options
									//exec ("cd ".$predefpath.";ls ".$extension,$tabres);
									$tabres = glob($avispaTool->getPredefpath()."*".$avispaTool->getExtension());
									foreach ($tabres as $res)
										echo "<option value=\"".basename($res)."\">".basename(substr($res,0,strrpos(($res),".")))."</option>";
									?>
							</select> <br> <br> <input src="images/load_file.gif"
								name="load_test" value="true" type=image>
							</td>
							</form>

							<!-- Help Center -->
							<form name="formoutput" method="post"
								action="basic_output.php?opened_file=<?php echo($avispaTool->getOpenedFile()); ?>">
								<td valign="center" align="center"><font
									face="Verdana, Arial, Helvetica, sans-serif" size="3"
									color="blue"> <b> <?php
									// If the user is editing a file
									if ($_POST['edit_file_x'] != "")
									{
										echo "Save the file after edition<br>";
										echo "for the changes to take effect<br>";
									} else {
										// If the user has an opened file
										if ($_POST['opened_file'])
										{
											echo "Edit the loaded file or<br>";
											echo "press execute<br>";
										} else {
											// If the user has no opened file
											echo "<br><br>";
											echo "Load a test or a user file<br>";
										}
									}
									?>
									</b>
								</font> <br> <br> <!-- Execute Button --> <?php
								// If file is been edited disable the execute button
								if ($_POST['edit_file_x'] != "")
									echo "<input src=\"images/execute_d.gif\" name=\"execute\" value=\"execute\"  type=image disabled>";
								else
									// If there is an opened file show the execute button
									if (!empty($_POST['opened_file']))
									echo "<input src=\"images/execute.gif\" name=\"execute\" value=\"execute\"  type=image>";
								?>
								</td>
							</form>

							<!-- Custom User Files -->
							<form name="formuser" method="post" action="basic.php"
								ENCTYPE="multipart/form-data">
								<td valign="top" align="center" height="200" width="33%"><img
									src="images/userfiles.gif"> <br> <br> <input type="hidden"
									name="MAX_FILE_SIZE" value="<?php echo ($avispaTool->getFileLength()); ?>"> <input
									type="file" name="user_file" accept="text/hlpsl"> <br> <br> <input
									src="images/load_file.gif" name="load_user" value="true"
									type=image>
								</td>
							</form>
						</tr>

						<!-- Bottom Bar -->
						<tr>
							<td align="center" valign="top" colspan=3 bgcolor="#00008C"><img
								src="images/bottombar.gif">
							</td>
						</tr>
					</table>
			
			</td>
		</tr>
	</table>
</body>
</html>
