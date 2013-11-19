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

	/* Need to create a constructor */

	
    public function queryDataForInstance($instance) {
    	/* The upload form stores uploaded files under THINKUP_CFG['datadir_path']/twitterarchiveloader/{username}
    	If the directory {username} doesn't exist under THINKUP_CFG['datadir_path']/twitterarchiveloader/ then
    	there's no data*/
    	// check for the directory
    	// check for the zip file
    	// check if the zip file has been extracted
    	// search for files YYYY_MM.js

    	// return true/false
    }
    
    public function unprocessedFileExists($instance) {
    	/* files which have a corresponding YYYY_MM.js.processed have already been processed */
    	// find files YYYY_MM.js which don't have a corresponding YYYY_MM.js.processed
    	
    	// return a file which needs to be processed
    }
    
    public function getJSONForFile($filename) {
    	/* Read the file and decode to JSON */
    	
    	// return array of JSON objects
    }
    
    public function setFileToProcessed($filename) {
    	/* when the file has successfully processed create a $filename.processed file */
    	// create empty $filename.processed file
    }
 

}
