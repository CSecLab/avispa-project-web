<?php

class AVISPAWebTool
{
	// Pre-defined variables/constants
	private $predefpath		= "./testfiles/";
	private $tempdir		= "./tempdir/";
	private $extension		= "*.hlpsl";
	private $file_length	= 51200; // 50 Kbytes
	
	private $allowed_extensions = array(".hlpsl");
	
	// Dinamic variables
	private $_opened_file;
	private $_test_file;
	private $_text_input_area;
	
	// Constructor
	public function __construct($_post)
	{
		// Initialize and sanitize
		if(isset($_post['opened_file']))
		{
			// Sanitize filename
			$this->_opened_file = $this->sanitizeFileName($_post['opened_file']);
			$this->_opened_file = $this->tempdir.$this->_opened_file;
			//echo("[DEBUG] _opened_file = ".$this->_opened_file."<br />");
		}
		
		// Initialize and sanitize		
		if(isset($_post['test_file']))
		{
			$this->_test_file = $this->sanitizeFileName($_post['test_file']);
			//echo("[DEBUG] sanitized(_test_file) = ".$this->_test_file."<br />");
			$this->_test_file = $this->predefpath.$this->_test_file;
			//echo("[DEBUG] _test_file = ".$this->_test_file."<br />");
			
		}
		
		// Initialize and sanitize		
		if(isset($_post['_text_input_area']))
		{
			$this->_text_input_area = $this->sanitizeFileContent($_post['_text_input_area']);
		}
	}

	/**
	 * Return the predefpath for TestFiles
	 */
	public function getPredefpath() {
		return $this->predefpath;
	}
	/**
	 * Return the tempdir for UserCustomFiles
	 */
	public function getTempdir() 	{
		return $this->tempdir;
	}
	/**
	 * Return the extension allowed for Files
	 */
	public function getExtension() 	{
		return $this->extension;
	}
	/**
	 * Return the max File Size allowed for Files
	 */
	public function getFileLength() {
		return $this->file_length;
	}
	
	public function getOpenedFile() {
		return $this->_opened_file;
	}
	
	public function getTestFile() {
		return $this->_test_file;
	}

	// Copy the testfile in a new temporary file within tempdir
	public function copyTestFile()
	{	
		$this->createNewOpenedFile();
		
		if (!@copy($this->_test_file, $this->_opened_file))
		{
			echo "Test File copying operation failed! ";
			$this->unlink($this->_opened_file);
			$this->_opened_file = "";
		}
		
		return $this->_opened_file;
	}
	
	// Create e a new temporary file in tempdir
	public function createNewOpenedFile($prefix = "workfile")
	{
		$this->_opened_file = $this->tempdir.basename(tempnam($tempdir, $prefix));
		
		// Show a warning if tempnam could not create the file in tempdir
		if (substr($this->_opened_file,2, 7) != "tempdir")
		{
			echo "Warning: ".$this->tempdir." not accessible\n";
			echo "Warning: temporary file will be created in the system's default temporary directory\n\n";
		}
		 
		return $this->_opened_file;
	}
	
	// Verify the file extension for the provided file.
	public function checkFileExtension($file)
	{
		$check = false;
		
		if(in_array($this->calcFileExtension($file), $this->allowed_extensions ))
			return true;
		
		return $check;
	}
	
	public function unlink($file)
	{
		return @unlink($file);
	}

	private function sanitizeFileName($input)
	{
		$sanitized_input = $input;

		// Returns filename component of path
		$sanitized_input = basename($sanitized_input);

		return $sanitized_input;
	}

	private function sanitizeFileContent($input)
	{
		$sanitized_input = $input;

		// Strip HTML and PHP tags from a string
		$sanitized_input = strip_tags($sanitized_input);
		
		// Convert special characters to HTML entities
		//$sanitized_input = htmlspecialchars($sanitized_input);

		// Un-quotes a quoted string
		//$sanitized_input = stripslashes($sanitized_input);

		return $sanitized_input;
	}
	
	public function sanitizeFile($file)
	{
		if(file_exists($this->tempdir.basename($file)))
		{
			// Sanitize content
			$cont = $this->sanitizeFileContent(@file_get_contents($this->tempdir.basename($file)));
			if(empty($cont) || strlen($cont) < 10)
				$this->_opened_file = "";
			else 
				@file_put_contents($this->_opened_file, $cont);
		}
		return $this->_opened_file;
	}
	
	/**
	 * 
	 * @param $opened_file
	 * @param $text_input_area
	 * 
	 * This function save the changes to the HLPSL Spec. made by the user.
	 */
	public function saveOpenedFile($text_input_area)
	{
		if(file_exists($this->_opened_file))
		{
			$handle = fopen ($this->_opened_file,"w");		
			$textinputarea = implode("",(explode("\r",$this->sanitizeFileContent($text_input_area))))." ";
			if (!fwrite($handle, stripcslashes($textinputarea), $this->file_length) || strlen($textinputarea) < 10)
			{
				//echo "Saving Operation failed\n";
				//echo "Cannot write to file ".$this->_opened_file;
				// Not able to write content or too short.
				$this->_opened_file = "";
			}
			fclose($handle);
		}
		else
		{
			// File does not exist.
			$this->_opened_file = "";
		}
		
		return $this->_opened_file;
	}
	
	private function calcFileExtension($file)
	{
		return substr($file,strrpos($file,"."), strlen($file));
	}
}


?>
