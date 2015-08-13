<?php
if (!defined('_PS_VERSION_'))
	exit;

class BlockFooter extends Module{
	public function __construct()
	{
		$this->name = 'blockfooter';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Denis Puhalskiy';

		$this->bootstrap = true;
		parent::__construct();	

		$this->displayName = 'Ссылки в футере';
		$this->description = 'Размещение ссылок в футере';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install(){

		if (!parent::install() ||
			!$this->registerHook('footer'))
			return false;

		return true;
	}

	public function uninstall(){
		if (!parent::uninstall()) return false;

		return true;
	}
	public function getContent(){
		//print_r($_POST);
		//die;
		if(!empty($_POST) && isset($_POST['submitBlockCategories'])){
			Configuration::updateValue('FOOTER_1_CAT_BLOCK', (int)$_POST['FOOTER_1_CAT_BLOCK']);
			Configuration::updateValue('FOOTER_3_CAT_BLOCK', (int)$_POST['FOOTER_3_CAT_BLOCK']);
			Configuration::updateValue('FOOTER_4_CMS_BLOCK', (int)$_POST['FOOTER_4_CMS_BLOCK']);

			$categories = array();
			foreach($_POST['FOOTER_2_CAT_BLOCK'] as $id){
				$categories[] = (int)$id;
			}
			//print_r($categories);
			Configuration::updateValue('FOOTER_2_CAT_BLOCK', json_encode($categories));
		}
		$data['cat1'] = Configuration::get('FOOTER_1_CAT_BLOCK', '');
		$data['cat2'] = json_decode(Configuration::get('FOOTER_2_CAT_BLOCK', '[]'));
		$data['cat3'] = Configuration::get('FOOTER_3_CAT_BLOCK', '');
		$data['cms4'] = Configuration::get('FOOTER_4_CMS_BLOCK', '');
		//print '<br><br><br><br>';
		//print_r($data);

		$html = '<form id="module_form" class="defaultForm  form-horizontal" method="post">';
		$html .= '<div class="panel">';
		$html .= '<div class="panel-heading"><i class="icon-cogs"></i> Первый блок</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория первого блока</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_1_CAT_BLOCK" value="'.$data['cat1'].'"></div>';
		$html .= '</div>';
		$html .= '<div class="panel-footer"><button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right"><i class="process-icon-save"></i> Сохранить</button></div>';
		$html .= '</div>';

		$html .= '<div class="panel">';
		$html .= '<div class="panel-heading"><i class="icon-cogs"></i> Второй блок</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория 1-ая</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_2_CAT_BLOCK[]" value="'.$data['cat2'][0].'"></div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория 2-ая</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_2_CAT_BLOCK[]" value="'.$data['cat2'][1].'"></div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория 3-ая</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_2_CAT_BLOCK[]" value="'.$data['cat2'][2].'"></div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория 4-ая</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_2_CAT_BLOCK[]" value="'.$data['cat2'][3].'"><p class="help-block">Категория 4-ая с вложенностью (3 дочирних)</p></div>';
		$html .= '</div>';
		$html .= '<div class="panel-footer"><button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right"><i class="process-icon-save"></i> Сохранить</button></div>';
		$html .= '</div>';

		$html .= '<div class="panel">';
		$html .= '<div class="panel-heading"><i class="icon-cogs"></i> Третий блок</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_3_CAT_BLOCK" value="'.$data['cat3'].'"><p class="help-block">Категория с вложенностью (5 дочирних)</p></div>';
		$html .= '</div>';
		$html .= '<div class="panel-footer"><button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right"><i class="process-icon-save"></i> Сохранить</button></div>';
		$html .= '</div>';

		$html .= '<div class="panel">';
		$html .= '<div class="panel-heading"><i class="icon-cogs"></i> Четвертый блок</div>';
		$html .= '<div class="form-group">';
		$html .= '<label class="control-label col-lg-3 ">Категория первого блока</label>';
		$html .= '<div class="col-lg-9 "><input type="text" name="FOOTER_4_CMS_BLOCK" value="'.$data['cms4'].'"><p class="help-block">Категория внутренних страниц</p></div>';
		$html .= '</div>';
		$html .= '<div class="panel-footer"><button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right"><i class="process-icon-save"></i> Сохранить</button></div>';
		$html .= '</div>';

		$html .= '</form>';

		return $html;
	}

	public function hookFooter($params){
		$user_groups =  ($this->context->customer->isLogged() ? $this->context->customer->getGroups() : array(Configuration::get('PS_UNIDENTIFIED_GROUP')));

		$block1_id = Configuration::get('FOOTER_1_CAT_BLOCK', 0);
		$id_lang = (int)$this->context->language->id;
		if($block1_id != 0){
			$category1 = Category::getNestedCategories($block1_id, $id_lang, true, $user_groups);
			$category1 = $category1[$block1_id];
		}else{
			$category1 = null;
		}

		$block2_ids = json_decode(Configuration::get('FOOTER_2_CAT_BLOCK', '[]'));
		if(!empty($block2_ids)){
			$categories = array();
			foreach($block2_ids as $key => $id){
				if($id != 0){
					if($key == 3) $sql_limit = 'LIMIT 0, 4';
					else $sql_limit = '';
					$temp = Category::getNestedCategories($id, $id_lang, true, $user_groups, true,'','',$sql_limit);
					$categories2[] = $temp[$id];
				}
			}
		}else{
			$categories2 = array();
		}

		$block3_id = Configuration::get('FOOTER_3_CAT_BLOCK', 0);
		if($block3_id != 0){
			$sql_limit = 'LIMIT 0, 6';
			$category3 = Category::getNestedCategories($block3_id, $id_lang, true, $user_groups, true,'','',$sql_limit);
			$category3 = $category3[$block3_id];
		}else{
			$category3 = null;
		}

		$block4_id = Configuration::get('FOOTER_4_CMS_BLOCK', 0);
		if($block4_id != 0){
			$sql_limit = 'LIMIT 0, 10';
			$category4 = new CMSCategory((int)$block4_id, (int)$id_lang);
			if(count($category4)){
				$category['link'] = Tools::HtmlEntitiesUTF8($category4->getLink());
				$category['name'] = $category4->name;
				$pages = $this->getCMSPages($category4->id);
				foreach($pages as $page){
					$cms = new CMS($page['id_cms'], (int)$id_lang);
					$links = $cms->getLinks((int)$id_lang, array((int)$cms->id));
					$page['link'] = $links[0]['link'];
					$category['pages'][] = $page;
				}
				$category4 = $category;
			}else{
				$category4 = null;
			}
		}else{
			$category4 = null;
		}

		$this->smarty->assign('category1', $category1);
		$this->smarty->assign('categories2', $categories2);
		$this->smarty->assign('category3', $category3);
		$this->smarty->assign('category4', $category4);
		$this->smarty->assign('footer_category', new Category());
		$display = $this->display(__FILE__, 'footer.tpl');
		return $display;
	}

	private function getCMSPages($id_cms_category, $id_shop = false, $id_lang = false)
	{
		$id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		$sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms` c
			INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE c.`id_cms_category` = '.(int)$id_cms_category.'
			AND cs.`id_shop` = '.(int)$id_shop.'
			AND cl.`id_lang` = '.(int)$id_lang.'
			AND c.`active` = 1
			ORDER BY `position`';

		return Db::getInstance()->executeS($sql);
	}
}
?>

<!--
<div class="panel-footer">
	<button type="submit" value="1" id="module_form_submit_btn" name="submitBlockCategories" class="btn btn-default pull-right">
	<i class="process-icon-save"></i> Сохранить</button>
</div>

div class="form-group">
	<label for="BLOCK_CATEG_ROOT_CATEGORY" class="control-label col-lg-3 ">Главная категория</label>
	<div class="col-lg-9 ">
		<div class="radio ">
			<label><input type="radio" name="BLOCK_CATEG_ROOT_CATEGORY" id="home" value="0">Главная</label>
		</div>
		<div class="radio ">
			<label><input type="radio" name="BLOCK_CATEG_ROOT_CATEGORY" id="current" value="1" checked="checked">Текущий</label>
		</div>
	</div>

</div
<div class="col-lg-9 ">
	<p class="help-block">Установить максимальную длину подуровней, размещенной на этом блоке (0 = бесконечно).</p>
</div>
-->