<?php 
require_once("lib/import.php");

echo (new Profile($_REQUEST["userId"]))->getPage();