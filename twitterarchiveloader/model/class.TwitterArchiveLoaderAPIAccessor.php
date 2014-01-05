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
 * Works with ZIP archive data to find if user archive data exists and handle JSON tweet files 
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
		$this->archive_zip_location = Config::getInstance()->getValue('datadir_path') . '/twitterarchiveloader/' . $this->instance->network_username . '/';
		$this->list_of_json_files = array();
		$this->archive_file_to_process = '';
	}

	
    public function queryDataForInstance() {
    	/* The upload form stores uploaded files under THINKUP_CFG['datadir_path']/twitterarchiveloader/{username}
    	If the directory {username} doesn't exist under THINKUP_CFG['datadir_path']/twitterarchiveloader/ then
    	there's likely no data*/
    	
    	// check for the expected directory structure
    	$this->logger->logInfo($this->archive_zip_location, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	if(!is_dir($this->archive_zip_location)) {
    		$this->logger->logError("No directory at " . $this->archive_zip_location, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		return false;
    	}
    	// Is there a 'tweets' directory indicative of previous processing?
    	if(is_dir($this->archive_zip_location . "tweets/")) {
    		$this->logger->logInfo("Working with an already processed archive file", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		$files = $this->findJSONTweetsFile($this->archive_zip_location . "tweets/");
    		if(count($files) > 0) {
    			$this->list_of_json_files = $files;
    			$this->logger->logDebug("List of JSON files: " . implode(",", $this->list_of_json_files), __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    			return true;
    		}
    		else {
    			// If there are no eligible files under tweets/ then mark the directory as processed
    			$this->logger->logDebug("Setting tweets/ to processed ", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    			$this->setFileToProcessed($this->archive_zip_location . "tweets");
    			return false;
    		}
    	}
    	else {
    		$this->logger->logDebug("Don't have existing tweets/ ", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	}
    	
    	// Looking for a ZIP archive
    	$zipfiles = glob($this->archive_zip_location . "*.zip");
    	if(count($zipfiles) > 0) {
    		$this->logger->logInfo("Found a ZIP archive(s) at: " . $this->archive_zip_location, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		// deal with the zip files found
    		foreach ($zipfiles as $zipfile) {
    			$tweetFiles = $this->searchZipForJSONFiles($zipfile);
    		    if(count($tweetFiles) > 0) {
    		    	$this->logger->logDebug("There are " . count($tweetFiles) . " files to process", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		    	$this->archive_file_to_process = $zipfile;
    		    	if($this->extractFilesFromArchive($tweetFiles)) {
    		    		// Need to get the full path to the files for later use
    		    		$tweetFilesWithFullPath = array();
    		    		foreach ($tweetFiles as $tweetfile) {
    		    			$tweetFilesWithFullPath[] = $this->archive_zip_location . "tweets/" . $tweetfile;
    		   			}
    		   			$tmpArray = array_merge($this->list_of_json_files, $tweetFilesWithFullPath);
    		   			$this->list_of_json_files = $tmpArray;
    		   			$this->logger->logDebug("Extracted files from archive ", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		   			$this->setFileToProcessed($zipfile);
    	    		}
    			}
    			else {
    				// The ZIP archive doesn't have any relevant data, set it to 'bad' so we don't try to use it again
    				$this->setFileToBad($zipfile);
    			}
    		}
    		if(count($this->list_of_json_files) > 0) {
    			$this->logger->logDebug("Have a list of JSON tweet files to work with", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    			return true;
    		}
    		else {
    			$this->logger->logDebug("No JSON tweet files found in: " . implode(": ", $zipfiles), __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    			return false;
    		}
    	}
    	else {
    		$this->logger->logDebug("No eligible ZIP archives found at: " . $this->archive_zip_location, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		return false;
    	}
    }
    
    
	public function findJSONTweetsFile($path) {
		// given a path, search it for YYYY_MM.js files
		$this->logger->logDebug("Looking for files containing JSON tweets at " . $path, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
		$pattern = '/\/20[0-9]{2}_[0-9]{2}\.js$/';
		$matchingFiles = array();
		$directoryEntities = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($directoryEntities as $name) {
			$result = preg_match($pattern, $name);
			if($result == 1 && is_file($name)) {
				$matchingFiles[] = $name;
			}
		}
		return $matchingFiles;
	}

    public function setFileToProcessed($filename) {
    	$this->logger->logDebug("Setting file to processed: " . $filename, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	/* when the file has successfully processed mv filename to $filename.processed.timsetamp */
    	$this->setFileStatus($filename, ".processed");
    }
    
    public function setFileToBad($filename) {
    	$this->logger->logDebug("Setting file to bad: " . $filename, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	/* when the file has successfully processed mv filename to $filename.bad.timestamp */
    	$this->setFileStatus($filename, ".bad");
    }
    
    public function setFileStatus($filename, $status) {
    	$timestamp = date("YmdHis");
    	if(rename($filename, $filename . $status . "." . $timestamp)) {
    		return true;
    	}
    	else {
    		$this->logger->logDebug("Can't set file status on: " . $filename, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		return false;
    	}
    }
        
    public function searchZipForJSONFiles($zipfile) {
    	$this->logger->logInfo("Looking for JSON tweets files in " . $zipfile, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	// go through the zip archive and look for files matching the pattern YYYY_MM.js then return an array of matching filenames
    	$zip = new ZipArchive;
    	$matchingFiles = array();
    	if($zip->open($zipfile)) {
    		for ($i = 0; $i < $zip->numFiles; $i++) {
    			$filename = $zip->getNameIndex($i);
    			$pattern = '/\/20[0-9]{2}_[0-9]{2}\.js$|^20[0-9]{2}_[0-9]{2}\.js$/';
    			$result = preg_match($pattern, $filename);
    			if($result == 1) {
    				$matchingFiles[] = $filename;
    			}
    		}
    	}
    	$this->logger->logDebug("Count of matching files in the archive:  " . count($matchingFiles), __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	return $matchingFiles;
    }
    
	public function extractFilesFromArchive($matchingFiles) {
		$this->logger->logDebug("Extracting files from: " . $this->archive_file_to_process, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
		$zip = new ZipArchive;
		if($zip->open($this->archive_file_to_process)) {
			$zip->extractTo($this->archive_zip_location . "tweets/", $matchingFiles);
			$zip->close();
			$this->logger->logDebug("Finished extracting files from ZIP archive", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
			return true;
		}
		else {
			return false;
		}
	}
}
