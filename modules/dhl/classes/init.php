<?php 

if (!in_array('PrestoChangeoModule', get_declared_classes()))
	require_once(dirname(__FILE__).'/PrestoChangeoModule.php');

if (!in_array('PrestoChangeoContext', get_declared_classes()))
	require_once(dirname(__FILE__).'/PrestoChangeoContext.php');

if (!in_array('PrestoChangeoCarrierModule', get_declared_classes()))
	require_once(dirname(__FILE__).'/PrestoChangeoCarrierModule.php');