<?php

/**
 * * Finds path, relative to the given root folder, of all files and directories in the given directory and its sub-directories non recursively.
 * * Will return an array of the form
 * * array(
 * *   'files' => [],
 * *   'dirs'  => [],
 * * )
 * * @author sreekumar
 * * @param string $root
 * * @result array
 * */
function read_all_files($root = '.'){
	  $files  = array('files'=>array(), 'dirs'=>array());
	  $directories  = array();
	  $last_letter  = $root[strlen($root)-1];
	  $root  = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR;
	     
	  $directories[]  = $root;
	       
	  while (sizeof($directories)) {
		$dir  = array_pop($directories);
			if ($handle = opendir($dir)) {
			      while (false !== ($file = readdir($handle))) {
					if ($file == '.' || $file == '..') {
						continue;
					}
					$file  = $dir.$file;
					if (is_dir($file)) {
						$directory_path = $file.DIRECTORY_SEPARATOR;
						array_push($directories, $directory_path);
						$files['dirs'][]  = $directory_path;
					} elseif (is_file($file)) {
						$files['files'][$file]  = base64_encode(urlencode(file_get_contents($file)));
				    	}
				}
				    closedir($handle);
			}
		}
	       
	return $files;
}
?>
