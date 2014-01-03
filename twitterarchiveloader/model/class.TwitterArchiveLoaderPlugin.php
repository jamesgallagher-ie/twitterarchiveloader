<?php
/**
 *
 * webapp/plugins/twitterarchiveloader/model/class.TwitterArchiveLoaderPlugin.php
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

class TwitterArchiveLoaderPlugin extends Plugin implements CrawlerPlugin, DashboardPlugin, PostDetailPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'twitterarchiveloader';
        $this->instance;
        $this->classname = 'TwitterArchiveLoaderPlugin';
        
    }

    public function activate() {

    }

    public function deactivate() {

    }

    public function renderConfiguration($owner) {
        $controller = new TwitterArchiveLoaderPluginConfigurationController($owner);
        return $controller->go();
    }

    public function crawl() {
    	// Get all Twitter instances
    	$config = Config::getInstance();
    	$logger = Logger::getInstance();
    	$instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
    	$owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
    	$plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
    	$options = $plugin_option_dao->getOptionsHash('twitter', true);
    	
    	$instances = $instance_dao->getAllInstances();
    	foreach ($instances as $instance) {
    		$this->instance = $instance;
    		try {
    			$logger->setUsername($instance->network_username);
    			$logger->logUserSuccess("Starting to collect data for ".$instance->network_username." from Twitter Archive Loader.",
    					__METHOD__.','.__LINE__);
    			// use the existing Twitter plugin functionality for converting JSON data to true tweets; the way I've implemented this doesn't make me proud :)
    			$tokens = $owner_instance_dao->getOAuthTokens($instance->id);
    			$oauth_token = $tokens['oauth_access_token'];
    			$oauth_token_secret = $tokens['oauth_access_token_secret'];
    			$oauth_consumer_key = $options['oauth_consumer_key']->option_value;
    			$oauth_consumer_secret = $options['oauth_consumer_secret']->option_value;
    			$archive_limit = $options['archive_limit']->option_value;
    			$num_twitter_errors = 100;
    			$api = new CrawlerTwitterAPIAccessorOAuth($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret, $archive_limit, $num_twitter_errors);
    			$tc = new TwitterCrawler($instance, $api);
	    		// Is there data for this instance?
	    		$crawler = new TwitterArchiveLoaderCrawler($instance);
	    			while($crawler->moreData()) {
	    				
	    				$fileusertweets = $crawler->fetchUserArchiveTweets();
	    				/* This is a horrible hack to resolve the difference between the key for avatar returned by the Twitter API and
	    				 * an archive JSON file (profile_image_url_https versus profile_image_url)
	    				 */
	    				$search = 'profile_image_url_https';
	    				$replace = 'profile_image_url';
	    				$usertweets = str_replace($search, $replace, $fileusertweets);
	    				$tweets = $api->parseJSONTweets($usertweets);
	    				$logger->logDebug("Tweets array: " . count($tweets), __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
	    				$logger->logDebug("Now have " . count($tweets) . " tweets to process", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
	    				$post_dao = DAOFactory::getDAO('PostDAO');
	    				$new_username = false;
	    				$forearchcount = 0;
	    				foreach ($tweets as $tweet) {
	    					var_dump($tweet);
	    					$logger->logInfo($tweet['post_id']. " being processed at loop " . $forearchcount, "TwitterArchiveLoaderPlugin");
	    					$tweet['network'] = 'twitter';
	    					// check if the tweet belongs to the instance user, that is; the id on the tweet is the same as the instance id
	    					if ($tweet['user_id'] == $instance->network_user_id) {
		    					$forearchcount = $forearchcount + 1;	    				
		    					$inserted_post_key = $post_dao->addPost($tweet, $this->user, $this->logger);	    					
		    					if ( $inserted_post_key !== false) {
		    						$logger->logInfo($tweet['post_id']. " was inserted", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
		    						$this->instance->total_posts_in_system = $this->instance->total_posts_in_system + 1;
		    						//expand and insert links contained in tweet
		    						URLProcessor::processPostURLs($tweet['post_text'], $tweet['post_id'], 'twitter', $logger);
		    					}
		    					if ($tweet['post_id'] > $this->instance->last_post_id) {
		    						$logger->logInfo($tweet['post_id']. " has become the last_post_id", __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
		    						$this->instance->last_post_id = $tweet['post_id'];
		    					}
	    					}
	    					else {
	    						$logger->logInfo("This tweet doesn't belong to this user " . $tweet['post_id'], __CLASS__ . "." . __FUNCTION__ . "." . __LINE__);
	    					}
	    				}
	    				$crawler->setLastTweetsFileProcessedStatus(true);
	    			}
    			}
    			
    		catch (Exception $e) {
                $logger->logUserError(get_class($e) ." while crawling ".$instance->network_username." from Twitter Archive loader: ".
                $e->getMessage(), __METHOD__.','.__LINE__);
            }
    	}

    }

    public function getDashboardMenuItems($instance) {

    }

    public function getPostDetailMenuItems($post) {

    }
    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return "";
    }
}
