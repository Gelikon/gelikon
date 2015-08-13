<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/import.php');

$import = new Import();
$import->ajaxImport($_POST['position']);