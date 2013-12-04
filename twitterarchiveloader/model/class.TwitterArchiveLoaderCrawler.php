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
 * Description of what this class does
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
    	if(count($this->api_accessor->list_of_json_files) > 0) {
    		return true;
    	}
    	else {
	    	if($this->api_accessor->queryDataForInstance()) {
    			return true;
    		}
    		else {
    			return false;
    		}
    	}
    }
    
    public function fetchUserTweets() {
    	$json = array();
    	$filename = $this->api_accessor->list_of_json_files[0];
    	var_dump($filename);
    	if(is_file($filename) && is_readable($filename)) {
    		$filecontents = file_get_contents($filename);
    		preg_match('/\[.*\]/s', $filecontents, $matches);
    		if(count($matches) > 0) {
    			$json = JSONDecoder::decode($matches[0], true);
    			$this->last_tweets_file_processed = $filename;
    		}
    	}
    	else {
    		// we have an error
    	}
    	return $json;
    }
    
    public function setLastTweetsFileProcessedStatus($status) {
    	if($status) {
    		$this->api_accessor->setFileToProcessed($this->last_tweets_file_processed);
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
