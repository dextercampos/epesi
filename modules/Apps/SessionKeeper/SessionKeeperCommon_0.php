<?php
/**
 * Keep epesi logged in.
 * @author pbukowski@telaxus.com
 * @copyright pbukowski@telaxus.com
 * @license SPL
 * @version 0.1
 * @package apps-sessionkeeper
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Apps_SessionKeeperCommon extends ModuleCommon {
	public static function user_settings(){
		$time = ini_get("session.gc_maxlifetime");
		$def = array('default'=>'default server time ('.($time/60).' minutes)');
		if($time<1800)
			$def['1800']='30 minutes';
		if($time<3600)
			$def['3600']='1 hour';
		if($time<7200)
			$def['7200']='2 hours';
		if($time<14400)
			$def['14400']='4 hours';
		if($time<28800)
			$def['28800']='8 hours';
		return array('Session'=>array(
			array('name'=>'time','label'=>'Keep session at least','type'=>'select','values'=>$def,'default'=>'default','reload'=>true)
			));
	}

}
if(Acl::is_user()) {
	$time = Base_User_SettingsCommon::get('Apps/SessionKeeper','time');
	if($time!='default') {
		load_js('modules/Apps/SessionKeeper/sk.js');
		$sys_time = ini_get("session.gc_maxlifetime");
		$x_time = $time-$sys_time;
		$interval = $sys_time/2;
		if($x_time<$interval)
			$interval = $x_time;
		eval_js_once('SessionKeeper.maxtime='.$x_time.';'.
			'SessionKeeper.interval='.($sys_time/2).';'.
			'SessionKeeper.load()');
	}
}
?>
