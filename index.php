<?php
/**
 * Index file
 *
 * This file includes all 'include files', loads modules
 * and gets output of default module.
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-base
 */
if(version_compare(phpversion(), '5.4.0')==-1)
	die("You are running an old version of PHP, php 5.4 required.");

if(trim(ini_get("safe_mode")))
	die('You cannot use EPESI with PHP safe mode turned on - please disable it. Please notice this feature is deprecated since PHP 5.3 and will be removed in PHP 6.0.');

define('_VALID_ACCESS',1);
require_once('include/data_dir.php');
if(!file_exists(DATA_DIR.'/config.php')) {
	header('Location: setup.php');
	exit();
}

if(!is_writable(DATA_DIR))
	die('Cannot write into "'.DATA_DIR.'" directory. Please fix privileges.');

// require_once('include/include_path.php');
require_once('include/config.php');
require_once('include/maintenance_mode.php');
require_once('include/error.php');
require_once('include/misc.php');
require_once('include/database.php');
require_once('include/variables.php');
if(epesi_requires_update()) {
    header('Location: update.php');
    exit();
}
$tables = DB::MetaTables();
if(!in_array('modules',$tables) || !in_array('variables',$tables) || !in_array('session',$tables))
	die('Database structure you are using is apparently out of date or damaged. If you didn\'t perform application update recently you should try to restore the database. Otherwise, please refer to EPESI documentation in order to perform database update.');

ob_start();

if(IPHONE) {
	if(!isset($_GET['force_epesi'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="robots" content="NOINDEX, NOARCHIVE">
	<meta id="viewport" name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
	<title><?php print(EPESI);?></title>
	<link rel="stylesheet" href="libs/UiUIKit/stylesheets/iphone.css" />
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png" />
	<script type="text/javascript" charset="utf-8">
		window.onload = function() {
		  setTimeout(function(){window.scrollTo(0, 1);}, 100);
		}
	</script>
</head>

<body>
<div id="header">
		<h1><?php print(EPESI);?></h1>
</div>

Please choose <?php print(EPESI);?> version:<ul>
<li><a href="mobile.php" class="white button">mobile</a><br>
<li><a href="index.php?force_epesi=1" class="green button">desktop</a>
</ul>

</body>
</html>
<?php
		exit();
	}
} elseif(detect_mobile_device()) {
	header('Location: mobile.php');
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

		<head profile="http://www.w3.org/2005/11/profile">
		<link rel="icon" type="image/png" href="images/favicon.png" />
		<link rel="apple-touch-icon" href="images/apple-favicon.png" />
		<title><?php print(EPESI);?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
        <meta name="robots" content="NOINDEX, NOARCHIVE">
			<link rel="stylesheet" href="libs/bootstrap/css/bootstrap.min.css">
			<?php
		ini_set('include_path', 'libs/minify' . PATH_SEPARATOR . '.' . PATH_SEPARATOR . 'libs' . PATH_SEPARATOR . ini_get('include_path'));
		require_once('Minify/Build.php');
		$jquery = DEBUG_JS ? 'libs/jquery-1.11.3.js' : 'libs/jquery-1.11.3.min.js';
		$jquery_migrate = DEBUG_JS ? 'libs/jquery-migrate-1.2.1.js' : 'libs/jquery-migrate-1.2.1.min.js';
		$jses = array('libs/prototype.js', $jquery, $jquery_migrate, 'libs/jquery.clonePosition.js', 'libs/jquery-ui-1.10.1.custom.min.js', 'libs/HistoryKeeper.js','include/epesi.js');
	if(!DEBUG_JS) {
		$jsses_build = new Minify_Build($jses);
		$jsses_src = $jsses_build->uri('serve.php?' . http_build_query(array('f' => array_values($jses))));
		echo("<script type='text/javascript' src='$jsses_src'></script>");
	} else {
		foreach($jses as $js)
			print("<script type='text/javascript' src='$js'></script>");
	}
	$csses = array('libs/jquery-ui-1.10.1.custom.min.css', 'libs/select2/css/select2.css');
	$csses_build = new Minify_Build($csses);
	$csses_src = $csses_build->uri('serve.php?'.http_build_query(array('f'=>array_values($csses))));
?>
		<link type="text/css" href="<?php print($csses_src)?>" rel="stylesheet"></link>
		<link type="text/css" href="libs/font-awesome/css/font-awesome.css" rel="stylesheet"/>
			<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600&amp;subset=latin-ext,latin" rel="stylesheet" type="text/css">
			<script type="text/javascript" src="libs/bootstrap/js/bootstrap.js"></script>
			<script type="text/javascript" src="libs/select2/js/select2.js"></script>

            <style type="text/css">

                #epesiStatus {
                    background-color: white;
                    position: fixed;
                    left: 50%;
                    top: 30%;
                    margin-left: -180px;
                    width: 360px;
                    text-align: center;
                    vertical-align: middle;
                    z-index: 2002;
                    overflow: hidden;
                    padding-top: 20px;
                }

                #epesiStatus .text {
                    font-size: 22px;
                }

                #epesiStatus .spinner {
                    margin: 20px auto 20px;
                    text-align: center;
                }

                #epesiStatus .spinner > div {
                    width: 18px;
                    height: 18px;
                    background-color: #333;

                    border-radius: 100%;
                    display: inline-block;
                    -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;
                    animation: sk-bouncedelay 1.4s infinite ease-in-out both;
                }

                #epesiStatus .spinner .bounce1 {
                    -webkit-animation-delay: -0.32s;
                    animation-delay: -0.32s;
                }

                #epesiStatus .spinner .bounce2 {
                    -webkit-animation-delay: -0.16s;
                    animation-delay: -0.16s;
                }

                @-webkit-keyframes sk-bouncedelay {
                    0%, 80%, 100% { -webkit-transform: scale(0) }
                    40% { -webkit-transform: scale(1.0) }
                }

                @keyframes sk-bouncedelay {
                    0%, 80%, 100% {
                        -webkit-transform: scale(0);
                        transform: scale(0);
                    } 40% {
                          -webkit-transform: scale(1.0);
                          transform: scale(1.0);
                      }
                }

            </style>
		<?php print(TRACKING_CODE); ?>
	</head>
	<body <?php if (DIRECTION_RTL) print('class="epesi_rtl"'); ?> >

		<div id="body_content">
			<div class="container-fluid" id="main_content" style="display:none;"></div>
			<div id="debug_content" style="padding-top:97px;display:none;">
				<div class="button" onclick="jq('#error_box').html('');jq('#debug_content').hide();">Hide</div>
				<div id="debug"></div>
				<div id="error_box"></div>
			</div>

            <div id="epesiStatus" class="panel panel-default">
                <img src="images/epesi_logo_RGB_Solid.png">
                <div class="text" style="display: none">{{ starting_message }}</div>
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>
		</div>
        <?php
        /*
         * init_js file allows only num_of_clients sessions. If there is image
         * with empty src="" browser will load index.php file, so we cannot
         * include init_js file directly because num_of_clients request will
         * reset our history and restart EPESI.
         *
         * Check here if request accepts html. If it does we can assume that
         * this is request for page and include init_js file which is faster.
         * If there is not 'html' in accept use script with src property.
         */
        if(isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'html') !== false) { ?>
		<script type="text/javascript"><?php require_once 'init_js.php'; ?></script>
        <?php } else { ?>
		<script type="text/javascript" src="init_js.php?<?php print(http_build_query($_GET));?>"></script>
        <?php } ?>
        <noscript>Please enable JavaScript in your browser and let <?php print(EPESI);?> work!</noscript>
		<?php if(IPHONE) { ?>
		<script type="text/javascript">var iphone=true;</script>
		<?php } ?>
        <script type="application/javascript">
            (function() {
                var isBootstrapEvent = false;
                if (window.jQuery) {
                    var all = jQuery('*');
                    jQuery.each(['hide.bs.dropdown',
                        'hide.bs.collapse',
                        'hide.bs.modal',
                        'hide.bs.tooltip'], function(index, eventName) {
                        all.on(eventName, function( event ) {
                            isBootstrapEvent = true;
                        });
                    });
                }
                var originalHide = Element.hide;
                Element.addMethods({
                    hide: function(element) {
                        if(isBootstrapEvent) {
                            isBootstrapEvent = false;
                            return element;
                        }
                        return originalHide(element);
                    }
                });
            })();

        </script>
	</body>
</html>
<?php
$content = ob_get_contents();
ob_end_clean();

require_once('libs/minify/HTTP/Encoder.php');
$he = new HTTP_Encoder(array('content' => $content));
if (MINIFY_ENCODE)
	$he->encode();
$he->sendAll();
?>
