<?php

class Template {
	// Base path of the template
	private static $m_basePath;
	// Associative array as $varName => $value
	private $m_vars;
	// Associative array as $tagName => $content
	private $m_tags;
	// Relative file path for the template.
	private $m_filePath;
	// Associative array of namespace ['C'] => array values
	private static $m_namespaces;
	
	/**
	 * CTOR 
	 * 
	 * @param $str_file File relative path / file name
	 */
	public function __construct($str_file) {
		$this->m_vars = array();
		$this->m_tags = array();
		$this->m_filePath = $str_file;
	}
	
	/**
	 * Assign a value to a template var. 
	 * 
	 * @param $str_name Variable's name. Must be string.
	 * @param $value Variable's value
	 */
	public function setVar($str_name, $value) {
		$this->m_vars[$str_name] = $value;		
	}
	
	/**
	 * Assign value to multiple vars
	 * 
	 * @param $vars Associative array as $name => $value
	 */
	public function setVars(array $vars) {
		foreach($vars as $name => $value) {
			$this->setVar($name, $value);
		}	
	}
	
	/**
	 * Adds to the current template
	 * 
	 * @param $str_tagName Tag name.
	 * @param $str_content Either a string with the content or
	 * 		with a Template object.
	 */
	public function addToTag($str_tagName, $str_content) {
		$content = $str_content;
		
		if($str_content instanceof Template) {
			$content = $str_content->render();
		}
		
		if(!isset($this->m_tags[$str_tagName])) {
			$this->m_tags[$str_tagName] = $content;
		} else {
			$this->m_tags[$str_tagName] .= $content;
		}
	}
	
	/**
	 * Add multiple tags to template.
	 * 
	 * @param $tags Associative array as $name => $content
	 * @note $content may be either a template object or a string.
	 */
	public function addToTags(array $tags) {
		foreach($tags as $name => $content) {
			$this->addToTag($name, $content);
		}
	}
	
	/**
	 * Renders the template
	 * 
	 * @returns Parsed content string.
	 */
	public function render() {
		$content = "";
		
		$fPath = self::$m_basePath . "/" . $this->m_filePath;
		$hFile = fopen($fPath, "r");
		
		if ($hFile) {
			while (($sLine = fgets($hFile)) !== false) {
				$sLineCopy = $sLine;
						
				// Replace the variables
				foreach($this->m_vars as $name => $value) {
					$sLineCopy = str_replace("{".$name."}", $value, $sLineCopy);
				}
				
				// Replace the tags.
				foreach($this->m_tags as $name => $value) {
					$sLineCopy = str_replace("<!-- TAG ".$name." -->", $value, $sLineCopy);
				}
				
				// Replace all namespaces 
				foreach(self::$m_namespaces as $key => $value) {
					$matches = array();
					preg_match_all("/{".$value."\.([0-9a-zA-Z\-_]+)}/", $sLineCopy, $matches);
					
					foreach($matches[1] as $match) {
						if(isset($value[$match])) {
							$sLineCopy = str_replace("{".$value.".".$match."}", 
								$value[$match], $sLineCopy);
						}
					}
				}
				
				$content .= $sLineCopy;
			}

			fclose($hFile);
		} else {
			die(__FILE__ . " : Cannot open " . $fPath );
		} 
		
		return $content;
	}
	
	/**
	 * Add a namespace with values.
	 * 
	 * @param $str_name Namespace name
	 * @param $values Associative array of values.
	 */ 
	public static function addNamespace($str_name, array &$values) {
		if(strlen($str_name) < 1) {
			die(__METHOD__ . ": namespace name '".$str_name."' is either invalid or empty.");
		}
		
		self::$m_namespaces[$str_name] = $values;
	}
	
	/**
	 * Sets the base path for the templates. 
	 * 
	 * @param $str_basePath 
	 */
	public static function setBasePath($str_basePath)  {
		if(!is_string($str_basePath)) {
			die( __METHOD__ . " [". __LINE__ . ":] Base path is not a string");
		}
		
		self::$m_basePath = $str_basePath;
	}
}

?>