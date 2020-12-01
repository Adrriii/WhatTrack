<?php
session_start();
require_once '../config.php';
require_once 'DataManager.php';
require_once 'Database.php';

require_once 'web/Page.php';

Page::$dm = new DataManager();

require_once 'web/Head.php';
require_once 'web/Nav.php';
require_once 'web/Footer.php';
require_once 'web/Index.php';
require_once 'web/Profile.php';