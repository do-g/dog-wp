<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__UP_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__UP_PLUGIN_DIR . 'plugin.class.php');

Dog_Updater::init();