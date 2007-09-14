<?php
/**
 * @author Kuba Slawinski <kslawinski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 0.9
 * @package apps-gallery
 * @license SPL
 */
 defined("_VALID_ACCESS") || die('Direct access forbidden');

class Apps_GalleryCommon extends ModuleCommon {
	public static function menu() {
		return array('Gallery'=>array());
	}
	
	public static function applet_caption() {
		return "Image";
	}

	public static function applet_info() {
		return "Displays image from your gallery"; //here can be associative array
	}
	
	public function _applet_settings() {
		$dir = $this->get_data_dir().Base_UserCommon::get_my_user_id().'/';
		$images = array();
		if(file_exists($dir) && is_dir($dir)) {
			$images_tmp = glob_tree($dir,'*.{'.sql_regcase('jpg,png,gif').'}',GLOB_BRACE);
			$def = null;
			foreach($images_tmp as $f) {
				if(!isset($def)) $def = $f;
				$x = substr($f,strlen($dir));
				$images[$f] = $x;
			}
		}
		$ret = array();
		if($images) {
			$ret[] = array('name'=>'image','label'=>'Choose image','type'=>'select','values'=>$images,'default'=>$def,'rule'=>array(array('message'=>'Field required', 'type'=>'required')));
			$ret[] = array('name'=>'size','label'=>'Maximal size','type'=>'select','values'=>array(100=>'100x100',200=>'200x200',300=>'300x300'),'default'=>200,'rule'=>array(array('message'=>'Field required', 'type'=>'required')));
		} else {
			$ret[] = array('name'=>'no_img','type'=>'static','label'=>'','values'=>Base_LangCommon::ts($this->get_type(),'No images in your gallery'));
		}
		return $ret;
	}

	public static function applet_settings() {
		return self::Instance()->_applet_settings();
	}

}
?>