<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @license SPL
 * @package epesi-utils
 * @subpackage common-data
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_CommonData extends Module {
	private $lang;

	public function construct() {
		$this->lang = $this->init_module('Base/Lang');
	}

	/**
	 * For internal use only.
	 */
	public function admin() {
		$this->browse();
	}

	public function admin_array($name) {
		$this->browse($name);
	}

	/**
	 * For internal use only.
	 */
	public function edit($parent,$key=null){
		if ($this->is_back()) return false;

		$id = Utils_CommonDataCommon::get_id($parent);
		if (!$id) {
			print($this->lang->t('No such array'));
			return false;
		}

		$f = & $this->init_module('Libs/QuickForm',null,'edit');
		$f->addElement('header', null, $this->lang->t((($key===null)?'New':'Edit').' node'));
		$f->add_table('utils_commondata_tree',array(
						array('name'=>'akey','label'=>$this->lang->t('Key'),
							'rule'=>array('type'=>'callback','param'=>array($parent,$key),
									'func'=>array($this,'check_key'),
									'message'=>$this->lang->t('Specified key already exists'))),
						array('name'=>'value','label'=>$this->lang->t('Value'))
						));
		if($key!==null) {
			$value=Utils_CommonDataCommon::get_value($parent.'/'.$key);
			$f->setDefaults(array('akey'=>$key,'value'=>$value));
		}

		if ($f->validate()) {
			$submited = $f->exportValues();
			if($key!==null)
				Utils_CommonDataCommon::rename_key($parent,$key,$submited['akey']);
			Utils_CommonDataCommon::set_value($parent.'/'.$submited['akey'],$submited['value']);
			return false;
		}
		Base_ActionBarCommon::add('save','Save',$f->get_submit_form_href());
		Base_ActionBarCommon::add('back','Cancel',$this->create_back_href());
		$f->display();
		return true;
	}
	
	public function check_key($new_key,$arr) {
		if($arr[1]==$new_key) return true;
		return Utils_CommonDataCommon::get_id($arr[0].'/'.$new_key)===false;
	}
	
	/**
	 * For internal use only.
	 */
	public function browse($name='',$root=true){
		if($this->is_back()) return false;
		
		$gb = & $this->init_module('Utils/GenericBrowser',null,'browse');

		$gb->set_table_columns(array(
						array('name'=>'Key','width'=>20, 'order'=>'akey'),
						array('name'=>'Value','width'=>20, 'order'=>'value')
					));

		$ret = Utils_CommonDataCommon::get_array($name);
		foreach($ret as $k=>$v) {
			$gb_row = $gb->get_new_row();
			$gb_row->add_data($k,$v);
			$gb_row->add_action($this->create_callback_href(array($this,'browse'),array($name.'/'.$k,false)),'View');
			$gb_row->add_action($this->create_callback_href(array($this,'edit'),array($name,$k)),'Edit');
			$gb_row->add_action($this->create_confirm_callback_href($this->lang->t('Delete array').' \''.Epesi::escapeJS($name.'/'.$k,false).'\'?',array('Utils_CommonData','remove_array'), array($name.'/'.$k)),'Delete');
		}
		$this->display_module($gb);
		Base_ActionBarCommon::add('add','Add array',$this->create_callback_href(array($this,'edit'),$name));
		if(!$root)
			Base_ActionBarCommon::add('back','Back',$this->create_back_href());
		return true;
	}
	
	/**
	 * For internal use only.
	 */
	public static function remove_array($name){
		Utils_CommonDataCommon::remove($name);
	}

}

?>