<?php
/**
 *
 * webapp/plugins/twitterarchiveloader/controller/class.TwitterArchiveLoaderPluginConfigurationController.php
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

class TwitterArchiveLoaderPluginConfigurationController extends PluginConfigurationController {

    public function __construct($owner) {
        parent::__construct($owner, 'twitterarchiveloader');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/twitterarchiveloader/view/account.index.tpl');
        // Get the path to the data dir.
        $datadir_path = Config::getInstance()->getValue('datadir_path');
        $twitterArchiveDataDir = $datadir_path . 'twitterarchiveloader';
        
        // Need the Owner instance details
        $id = DAOFactory::getDAO('InstanceDAO');
        $owner_instances = $id->getByOwnerAndNetwork($this->owner, 'twitter');
        $this->addToView('owner_instances', $owner_instances);
        
        // Working with the view
        $pluginMessage = 'Hello! This plugin allows you to choose an export from Twitter, for a user, for import to ThinkUp';
        $this->addToView('message', $pluginMessage);
        $this->view_mgr->addHelp('twitterarchiveloader', 'contribute/developers/plugins/buildplugin');

        /** set option fields **/
        // Not sure what to do with the next two lines
        $plugin = new TwitterArchiveLoaderPlugin();
        $this->addToView('is_configured', $plugin->isConfigured());
        try {
        	if (isset($_FILES['tweet_archive'])) {
        		self::mutexLock();
        		/* upload tweet_archive file */
        		if ($_FILES['tweet_archive']['error']) {
        			if ($_FILES['tweet_archive']['error'] == UPLOAD_ERR_INI_SIZE) {
        				throw new Exception("Upload of Twitter Archive failed. The file is too large." .
        						"You may need to increase the upload_max_filesize in php.ini.");
        			} else if ($_FILES['tweet_archive']['error'] == UPLOAD_ERR_NO_FILE) {
        				throw new Exception("No file uploaded. Please select a Twitter Archive file to upload");
        			} else {
        				throw new Exception("Twitter Archive file upload failed.");
        			}
        		} else {
        			// get the twitter user from the form
        			if ($_POST['twitter_username']) {
	        			// store the file
	        			$path_for_archive = $twitterArchiveDataDir . $_POST['twitter_username'];
	        			$path_for_file = $path_for_archive . basename($_FILES['tweet_archive']['name']);
	        			if (!file_exists($path_for_archive)) {
	        				mkdir($path_for_archive, 0777, true);
	        			}
	        			// now move the file from it's temporary location
	        			if (move_uploaded_file($_FILES['tweet_archive']['tmp_name'], $path_for_file)) {
	        				$this->addSuccessMessage("Data Import Successfull!");
	        				return $this->generateView();
	        			} else {
	        				throw new Exception("Twitter Archive file storage failed.");
	        			}
        			} else {
        				throw new Exception("Twitter Archive file storage failed.");
        			}
        		}
        		self::mutexLock(true);
        	} else {
        		/* load default form */
        		return $this->generateView();
        	}
        } catch (Exception  $e) {
            $this->addErrorMessage($e->getMessage());
            return $this->generateView();
        }

        //return $this->generateView();
    }

    public function saveAccessTokens() {

    }

}

