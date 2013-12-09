<?php
/**
 *
 * webapp/plugins/twitterarchiveloader/model/class.TwitterArchiveLoaderAPIAccessor.php
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * TwitterArchiveLoader (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2013 James Gallagher
 *
 * @author James Gallagher james@jamesgallagher.ie
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 James Gallagher
 */

class TwitterArchiveLoaderAPIAccessor{


	public function __construct($instance) {
		$this->instance = $instance;
		$this->logger = Logger::getInstance();
		$this->logger->setUsername($instance->network_username);
		$this->archive_zip_location = Config::getInstance()->getValue('datadir_path') . '/twitterarchiveloader/' . $instance->network_username . '/';
		$this->list_of_json_files = array();
		$this->archive_file_to_process = '';
	}

	
    public function queryDataForInstance() {
    	/* The upload form stores uploaded files under THINKUP_CFG['datadir_path']/twitterarchiveloader/{username}
    	If the directory {username} doesn't exist under THINKUP_CFG['datadir_path']/twitterarchiveloader/ then
    	there's likely no data*/
    	// check for the directory
    	$this->logger->logInfo($this->archive_zip_location,"TwitterArchiveLoaderAPIAccessor");
    	if(!is_dir($this->archive_zip_location)) {
    		$this->logger->logError("No directory at " . $this->archive_zip_location, "TwitterArchiveLoaderAPIAccessor");
    		return false;
    	}
    	// check for the zip file - ideally it will be called tweets.zip. If it has already been processed then it will be tweets.zip.processed
    	if(!(file_exists($this->archive_zip_location."tweets.zip") || file_exists($this->archive_zip_location."tweets.zip.processed"))) {
    		// search for zip files
    		$zipfiles = glob($this->archive_zip_location."*.zip");
//     		if(count($zipfiles) = 0) {
//     			return false;
//     		}
//     		else {
//     			if(!searchZipFilesForJSON($zipfiles)) {
//     				return false;
//     			}
//     		}
    		return false;
    	}
    	else {
    		$this->logger->logInfo("Working with archive file","TwitterArchiveLoaderAPIAccessor");
    		// We have a tweets.zip or tweets.zip.processed, check which
    		if(file_exists($this->archive_zip_location."tweets.zip.processed")) {
    			// a previous run has already processed this archive, so look for the extracted files under 'tweets' (from extractFilesFromArchive())
    			if(is_dir($this->archive_zip_location . "tweets/")) {
    				//$files = array();
    				$this->logger->logInfo("Working with an already processed archive file","TwitterArchiveLoaderAPIAccessor");
    				$files = $this->findJSONTweetsFile($this->archive_zip_location . "tweets/");
    				if(count($files) > 0) {
    					$this->list_of_json_files = $files;
    					//$this->logger->logInfo("files: " . implode("\n", $files),"TwitterArchiveLoaderAPIAccessor");
    					return true;
    				}
    				
    			} else {
    				if(rename($this->archive_zip_location."tweets.zip.processed", $this->archive_zip_location."tweets.zip")) {
    					$this->queryDataForInstance($this->instance);
    				}
    			}
    			
    		}
    		elseif(file_exists($this->archive_zip_location."tweets.zip")) {
    			// a zip file just waiting to be processed!
    		    // Extract the files which match the pattern YYYY_MM.js
    		    $this->archive_file_to_process = $this->archive_zip_location."tweets.zip";
    		    	$tweetFiles = $this->searchZipForJSONFiles($this->archive_zip_location."tweets.zip");
    		    	if(count($tweetFiles) > 0) {
    		    		$this->logger->logDebug("There are " . count($tweetFiles) . " files to process", "TwitterArchiveLoaderAPIAccessor");
    		    		if($this->extractFilesFromArchive($tweetFiles)) {
    		    			// Need to get the full path to the files for later use
    		    			$tweetFilesWithFullPath = array();
    		    			foreach ($tweetFiles as $tweetfile) {
    		    				$tweetFilesWithFullPath[] = $this->archive_zip_location . "tweets/" . $tweetfile;
    		    			}
    		    			$this->list_of_json_files = $tweetFilesWithFullPath;
    		    			$this->logger->logDebug("Extracted files from archive", "TwitterArchiveLoaderAPIAccessor");
    		    			$this->setFileToProcessed($this->archive_zip_location."tweets.zip");
    		    			return true;
    		    		}
    		    		else {
    		    			return false;
    		    		}
    		    	}
    		}
    		else {
    			// something has gone quite wrong
    			return false;
    		}
    	}
    }
    
    
	public function findJSONTweetsFile($path) {
		// given a path, search it for YYYY_MM.js files
	   $pattern = '/20[0-9]{2}_[0-9]{2}\.js$/';
	   $matchingFiles = array();
	   $directoryEntities = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
	   foreach ($directoryEntities as $name) {
	   	$result = preg_match($pattern, $name);
	   	if($result == 1 && is_file($name)) {
	   		$matchingFiles[] = $name;
	   	}
	   }
	   //var_dump($matchingFiles);
	   return $matchingFiles;	 
	}
    
    
    
    public function setFileToProcessed($filename) {
    	/* when the file has successfully processed mv filename to $filename.processed file */
    	if(rename($filename, $filename . ".processed")) {
    		return true;
    	}
    	else {
    		return false;
    	}
    }
        
    public function searchZipForJSONFiles($zipfile) {
    	// go through the zip archive and look for files matching the pattern YYYY_MM.js then return an array of matching filenames
    	$zip = new ZipArchive;
    	$matchingFiles = array();
    	//if($zip->open($this->archive_path . $this->archive_file_name)) {
    	if($zip->open($zipfile)) {
    		for ($i = 0; $i < $zip->numFiles; $i++) {
    			$filename = $zip->getNameIndex($i);
    			$pattern = '/20[0-9]{2}_[0-9]{2}\.js$/';
    			$result = preg_match($pattern, $filename);
    			if($result == 1) {
    				$matchingFiles[] = $filename;
    			}
    		}
    	}
    	return $matchingFiles;
    }
    
	public function extractFilesFromArchive($matchingFiles) {
		$this->logger->logInfo($this->archive_path . $this->archive_file_name,"TwitterArchiveLoaderAPIAccessor");
		$zip = new ZipArchive;
		if($zip->open($this->archive_file_to_process)) {
				$zip->extractTo($this->archive_zip_location . "tweets/", $matchingFiles);
			$zip->close();
			return true;
		}
		else {
			return false;
		}
	}
 

}
