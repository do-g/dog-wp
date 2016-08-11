<?php

require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');

define('DOG__MT_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__MT_PLUGIN_DIR . 'plugin.class.php');

Dog_Media_Taxonomy::init();