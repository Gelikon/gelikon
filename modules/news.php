<?php

include(dirname(__FILE__).'/config/config.inc.php');

//will be initialized bellow...
if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
	$rewrited_url = null;

include(dirname(__FILE__).'/init.php');

$config = Configuration::getMultiple(array('PS_CMS_TITLE', 'PS_CMS_LIMIT', 'PS_CMS_BRIEF', 'PS_CMS_LENGTH', 'PS_CMS_IGNORE', 'PS_CMS_PAGE'));

$all_rows = Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'cms c '.(!empty($config['PS_CMS_IGNORE']) ? 'WHERE c.id_cms NOT IN ('.$config['PS_CMS_IGNORE'].')' : '') );

$page = $_GET['page'];
if(is_null($page)) $page = 0;
$page_offset = $page * $config['PS_CMS_PAGE'];

$_cms = Db::getInstance()->ExecuteS('
	SELECT c.id_cms, cl.link_rewrite, cl.meta_title, cl.content
	FROM '._DB_PREFIX_.'cms c
	LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = '.intval($cookie->id_lang).')
	'.(!empty($config['PS_CMS_IGNORE']) ? 'WHERE c.id_cms NOT IN ('.$config['PS_CMS_IGNORE'].')' : '').'
	ORDER BY c.id_cms DESC LIMIT '.$page_offset.','.$config['PS_CMS_PAGE']  );

if(empty($_cms)) header('Location: news.php');

foreach($_cms as $cms)
{
	$news[] = array('id' => $cms['id_cms'],
			'title' => $cms['meta_title'],
			'brief' => trim(substr(strip_tags($cms['content']), 0, 6*intval($config['PS_CMS_LENGTH']) )),
			'rewrite'=> $cms['link_rewrite']
			);
}
include(dirname(__FILE__).'/header.php');
$smarty->assign('news', $news);
$smarty->assign('brief', $config['PS_CMS_BRIEF']);
$smarty->assign('title', $config['PS_CMS_TITLE']);

if($all_rows > $config['PS_CMS_PAGE'] ) 
{
    $smarty->assign('pagin', 1);
    $smarty->assign('next', $page + 1);
    $smarty->assign('prev', $page - 1);
    $smarty->assign('max', ceil($all_rows/$config['PS_CMS_PAGE']) ) ;
}

$smarty->display(_PS_MODULE_DIR_.'blockcms/news.tpl');
include(dirname(__FILE__).'/footer.php');



?>
