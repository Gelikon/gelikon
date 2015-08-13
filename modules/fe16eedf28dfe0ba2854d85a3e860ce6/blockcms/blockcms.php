<?php

class blockcms extends Module
{	
	private $_html = '';
	private $_postErrors = array();
	
	public  $cmsID;
	
 	function __construct()
 	{
 	 	$this->name = 'blockcms';
 	 	$this->version = '0.3';
 	 	$this->tab = '[mod-id]';
		
		parent::__construct();
		
		$this->displayName = $this->l('Block News CMS');
		$this->description = $this->l('Convert your static content (CMS) into a News Block');
 	}

	function install()
	{
	 	if (!parent::install() OR !$this->registerHook('leftColumn') 
	 		OR !Configuration::updateValue('PS_CMS_TITLE', 'News')
	 		OR !Configuration::updateValue('PS_CMS_LIMIT', 5)
	 		OR !Configuration::updateValue('PS_CMS_BRIEF', 1)
	 		OR !Configuration::updateValue('PS_CMS_LENGTH', 50)
	 		OR !Configuration::updateValue('PS_CMS_IGNORE', NULL)
	 		OR !Configuration::updateValue('PS_CMS_PAGE', 10)
	 		)
	 		return false;
	 	return true;
	}
	
	public function uninstall()
	{
		if (!Configuration::deleteByName('PS_CMS_TITLE')
		    OR !Configuration::deleteByName('PS_CMS_LIMIT')
		    OR !Configuration::deleteByName('PS_CMS_BRIEF')
		    OR !Configuration::deleteByName('PS_CMS_LENGTH')
		    OR !Configuration::deleteByName('PS_CMS_IGNORE')
		    OR !Configuration::deleteByName('PS_CMS_PAGE')
		    OR !parent::uninstall())
		return false;
	}
	
	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['cmstitle']))
				$this->_postErrors[] = '<p>'.$this->l('Please insert Header Title').'</p>';
			if (empty($_POST['cmslimit']))
				$this->_postErrors[] = '<p>'.$this->l('Please insert News Limit').'</p>';
			if (!isset($_POST['cmsbrief']))
				$this->_postErrors[] = '<p>'.$this->l('Please enable/disable News Brief').'</p>';
			if (empty($_POST['cmslength']))
				$this->_postErrors[] = '<p>'.$this->l('Please insert News Brief Length').'</p>';
			if (!ctype_digit($_POST['cmslength']) || !ctype_digit($_POST['cmslimit']) || !ctype_digit($_POST['cmspage']))
				$this->_postErrors[] = '<p>'.$this->l('Please insert number only for News Limit / Brief Length / News Per Page').'</p>';
		}
	}
	private function _postProcess()
	{
		Configuration::updateValue('PS_CMS_TITLE', $_POST['cmstitle']);
		Configuration::updateValue('PS_CMS_LIMIT', $_POST['cmslimit']);
		Configuration::updateValue('PS_CMS_BRIEF', $_POST['cmsbrief']);
		Configuration::updateValue('PS_CMS_LENGTH', $_POST['cmslength']);
		Configuration::updateValue('PS_CMS_IGNORE', $_POST['cmsignore']);
		Configuration::updateValue('PS_CMS_PAGE', $_POST['cmspage']);
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings Saved').'</div>';
	}
	

	public function _displayForm()
	{
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="../img/admin/cog.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Header Title').'</label>
				<div class="margin-form"><input type="text" name="cmstitle" id="cmstitle" value="'.Configuration::get('PS_CMS_TITLE').'" size="50">
					<p class="clear">'.$this->l('header title for this block').'</p>
				</div>
				<label>'.$this->l('News Limit').'</label>
				<div class="margin-form"><input type="text" name="cmslimit" id="cmslimit" value="'.Configuration::get('PS_CMS_LIMIT').'" size="5">
					<p class="clear">'.$this->l('how many cms to shown on the news block').'</p>
				</div>
				<label>'.$this->l('Show Brief').'</label>
				<div class="margin-form">
					<input type="radio" name="cmsbrief" id="text_list_on" value="1" '.(Configuration::get('PS_CMS_BRIEF')==1 ? 'checked="checked" ' : '').'/>
					<label class="t" for="text_list_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="cmsbrief" id="text_list_off" value="0" '.(Configuration::get('PS_CMS_BRIEF')==0 ? 'checked="checked" ' : '').'/>
					<label class="t" for="text_list_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('enable/disable news brief').'</p>
				</div>
				<label>'.$this->l('Brief Length').'</label>
				<div class="margin-form"><input type="text" name="cmslength" id="cmslength" value="'.Configuration::get('PS_CMS_LENGTH').'" size="5">
					<p class="clear">'.$this->l('how many character shown on the news brief').'</p>
				</div>
				<label>'.$this->l('Ignored').'</label>
				<div class="margin-form"><input type="text" name="cmsignore" id="cmsignore" value="'.Configuration::get('PS_CMS_IGNORE').'" size="50">
					<p class="clear">'.$this->l('ignore list will not shown on the news block/page.<br>insert cms-id and use , (comma) for multiple cms. ex: 1,2,3,4').'</p>
				</div>
				<label>'.$this->l('News Per Page').'</label>
				<div class="margin-form"><input type="text" name="cmspage" id="cmspage" value="'.Configuration::get('PS_CMS_PAGE').'" size="5">
					<p class="clear">'.$this->l('how many cms to shown on the news page').'</p>
				</div>
				<center><input type="submit" name="btnSubmit" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset><br />
			<fieldset><legend><img src="../img/admin/comment.gif" alt="" title="" />'.$this->l('Notes').'</legend>
			<p>any question, idea and sugestions just contact <a href="http://www.prestashop.com/forums/member/20611/caparuni/" target="_blank"><u>me</u></a> or post it on this <a href="http://bit.ly/dlkUnH" target="_blank"><u>module offical forum</u></a><br>
			if you like this module please put some <a href="http://bit.ly/donate-caparuni" target="_blank"><u>donation</u></a> for me to keep making  free modules </p>
			<p>Check my other modules: <br  />
			<a href="http://bit.ly/bFnc5u" target="_blank"><u>Product CMS</u></a> / 
			<a href="http://bit.ly/95mtGe" target="_blank"><u>Sexy Bookmarks</u></a> / 
			<a href="http://bit.ly/aHcBGv" target="_blank"><u>AddThis Block</u></a> / 
			<a href="http://bit.ly/cCd5BV" target="_blank"><u>Block YM+</u></a></p>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST) || isset($_GET['delete']) )
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
				$this->_html .= '<br><p><a href="javascript:history.back(1)" class="button">'.$this->l('back').'</a></p>';
		}
		else

		$this->_displayForm();
		return $this->_html;
	}	

	public function hookLeftColumn($params)
	{
		global $smarty, $cookie;
		
		$config = Configuration::getMultiple(array('PS_CMS_TITLE', 'PS_CMS_LIMIT', 'PS_CMS_BRIEF', 'PS_CMS_LENGTH', 'PS_CMS_IGNORE'));
		
		$_cms = Db::getInstance()->ExecuteS('
		SELECT c.id_cms, cl.link_rewrite, cl.meta_title, cl.content
		FROM '._DB_PREFIX_.'cms c
		LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = '.intval($cookie->id_lang).')
		'.(!empty($config['PS_CMS_IGNORE']) ? 'WHERE c.id_cms NOT IN ('.$config['PS_CMS_IGNORE'].')' : '').'
		ORDER BY c.id_cms DESC LIMIT '.$config['PS_CMS_LIMIT']);
		
		foreach($_cms as $cms)
		{
			$news[] = array('id' => $cms['id_cms'],
					'title' => $cms['meta_title'],
					'brief' => trim(substr(strip_tags($cms['content']), 0, intval($config['PS_CMS_LENGTH']))),
					'rewrite'=> $cms['link_rewrite']
					);
		}
		$smarty->assign('news', $news);
		$smarty->assign('brief', $config['PS_CMS_BRIEF']);
		$smarty->assign('title', $config['PS_CMS_TITLE']);

		return $this->display(__FILE__, 'blockcms.tpl');
	}

	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	
}
?>
