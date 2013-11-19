<?php
/**
 *
 * webapp/plugins/twitterarchiveloader/tests/TestOfTwitterArchiveLoaderPluginConfigurationController.php
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

require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitterarchiveloader/controller/class.TwitterArchiveLoaderPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitterarchiveloader/model/class.TwitterArchiveLoaderCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitterarchiveloader/model/class.TwitterArchiveLoaderPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitterarchiveloader/tests/classes/mock.TwitterArchiveLoaderAPIAccessor.php';

class TestOfTwitterArchiveLoaderPluginConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitterarchiveloader', 'TwitterArchiveLoaderPlugin');
        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new TwitterArchiveLoaderConfigurationController(null, 'twitterarchiveloader');
        $this->assertNotNull($controller);
        $this->assertIsA($controller, 'TwitterArchiveLoaderConfigurationController');
    }

}
