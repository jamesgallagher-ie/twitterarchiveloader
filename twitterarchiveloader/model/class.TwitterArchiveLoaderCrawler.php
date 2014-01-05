<?php
/**
 *
 * webapp/plugins/twitterarchiveloader/model/class.TwitterArchiveLoaderCrawler.php
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
 * Checks if there are more tweets available from the Twitter archive, fetches available tweets from the archive and marks archive files processed
 *
 * Copyright (c) 2013 James Gallagher
 *
 * @author James Gallagher james@jamesgallagher.ie
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 James Gallagher
 */

class TwitterArchiveLoaderCrawler {
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
     * @var Logger
     */
    var $logger;
    /**
     *
     * @var TwitterArchiveLoaderAPIAccessor
     */
    var $api_accessor;
    /**
     *
     * @param Instance $instance
     * @return $1Crawler
     */
    
    public function __construct($instance) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->api_accessor = new TwitterArchiveLoaderAPIAccessor($instance);
        $this->logger->setUsername($instance->network_username);
        $this->user_dao = DAOFactory::getDAO('UserDAO');
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $this->twitter_options = $plugin_option_dao->getOptionsHash('twitterarchiveloader');
        $this->last_tweets_files_processed;
    }
    
    
    public function moreData() {
    	$this->logger->logUserInfo("Checking for moreData for " . $this->instance->network_username, __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	$this->logger->logDebug("moreData with " . count($this->api_accessor->list_of_json_files) . " files", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	if(count($this->api_accessor->list_of_json_files) > 0) {
    		return true;
    	}
    	else {
    		$this->logger->logDebug("Calling queryDataForInstance to check if there are files available",__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
	    	if($this->api_accessor->queryDataForInstance()) {
    			return true;
    		}
    		else {
    			$this->logger->logInfo("No more data available",__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    			return false;
    		}
    	}
    }
    
    public function fetchUserArchiveTweets() {
    	$this->logger->logDebug("Fetching User Archive Tweets ",__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	$json = array();
    	$filename = $this->api_accessor->list_of_json_files[0];
    	$this->logger->logDebug("Attempting to load: " . $filename,__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	if(is_file($filename) && is_readable($filename)) {
    		$filecontents = file_get_contents($filename);
    		preg_match('/\[.*\]/s', $filecontents, $matches);
    		if(count($matches) > 0) {
    			$this->logger->logDebug("Fetched User Archive Tweets with: " . $filename,__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    			$json = $matches[0];
    			$this->last_tweets_file_processed = $filename;
    			//The following line produces a lot of data in the log, uncomment for greater debug detail.
    			//$this->logger->logDebug("JSON is " . $json,__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		}
    	}
    	else {
    		$this->logger->logDebug("Problem finding or reading: " . $filename,__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	}
    	return $json;
    }
    
    public function setLastTweetsFileProcessedStatus($status) {
    	$this->logger->logDebug("Executing setLastTweetsFileProcessedStatus",__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    	if($status) {
    		$this->api_accessor->setFileToProcessed($this->last_tweets_file_processed);
    		$this->logger->logDebug("Last file processed is: " . $this->last_tweets_file_processed,__CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
    		// now remove this file from the list of json files
    		for($i = 0; $i <= count($this->api_accessor->list_of_json_files); $i++) {
    			if($this->api_accessor->list_of_json_files[$i] == $this->last_tweets_file_processed) {
    				unset($this->api_accessor->list_of_json_files[$i]);
    				$this->api_accessor->list_of_json_files = array_values($this->api_accessor->list_of_json_files);
    			}
    		}
    	}
    }



}
