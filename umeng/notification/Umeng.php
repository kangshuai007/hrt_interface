<?php
require_once(dirname(__FILE__) . '/' . 'android/AndroidBroadcast.php'); //Android广播
require_once(dirname(__FILE__) . '/' . 'android/AndroidFilecast.php'); //Android文件播
require_once(dirname(__FILE__) . '/' . 'android/AndroidGroupcast.php');//Android组播
require_once(dirname(__FILE__) . '/' . 'android/AndroidUnicast.php');//Android单播
require_once(dirname(__FILE__) . '/' . 'android/AndroidCustomizedcast.php');//Android自定义播
require_once(dirname(__FILE__) . '/' . 'ios/IOSBroadcast.php');//IOS广播
require_once(dirname(__FILE__) . '/' . 'ios/IOSFilecast.php');//IOS文件播
require_once(dirname(__FILE__) . '/' . 'ios/IOSGroupcast.php');//IOS组播
require_once(dirname(__FILE__) . '/' . 'ios/IOSUnicast.php');//IOS单播
require_once(dirname(__FILE__) . '/' . 'ios/IOSCustomizedcast.php');//IOS自定义播

class Umeng {
	protected $appkey           = UAPPKEY; 
	protected $appMasterSecret     = USECRETKEY;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;
	
	protected $aliasType = NULL;
	protected $alias = NULL;
	protected $ticker = NULL;
	protected $title = NULL;
	protected $text = NULL;
	protected $anchor = NULL;
	protected $alert =null;
	
	protected $type =null;

	function __construct($config,$extra) {
		$this->timestamp = strval(time());
		$this->aliasType = isset($config['aliasType'])?$config['aliasType']:NULL;
		$this->ticker = isset($config['ticker'])?$config['ticker']:NULL;
		$this->title = isset($config['title'])?$config['title']:NULL;
		$this->text = isset($config['text'])?$config['text']:NULL;
		$this->alert = isset($config['alert'])?$config['alert']:NULL;
		$this->alias = isset($config['alias'])?$config['alias']:NULL;
		$this->type = isset($config['type'])?$config['type']:NULL;
	}
	function sendAndroidCustomizedcast() {
		try {
			$customizedcast = new AndroidCustomizedcast();
			$customizedcast->setAppMasterSecret($this->appMasterSecret);
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set your alias here, and use comma to split them if there are multiple alias.
			// And if you have many alias, you can also upload a file containing these alias, then 
			// use file_id to send customized notification.
			$customizedcast->setPredefinedKeyValue("alias",            $this->alias);
			// Set your alias_type here
			$customizedcast->setPredefinedKeyValue("alias_type",       $this->aliasType);
			$customizedcast->setPredefinedKeyValue("ticker",           $this->ticker);
			$customizedcast->setPredefinedKeyValue("title",            $this->title);
			$customizedcast->setPredefinedKeyValue("text",             $this->text);
			
			$customizedcast->setExtraField("type",$this->type);
			
			$customizedcast->setPredefinedKeyValue("after_open",       "go_app");
			//print("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
			// print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			// print("Caught exception: " . $e->getMessage());
		}
	}

	function sendIOSCustomizedcast() {
		try {
			$customizedcast = new IOSCustomizedcast();
			$customizedcast->setAppMasterSecret($this->appMasterSecret);
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);

			// Set your alias here, and use comma to split them if there are multiple alias.
			// And if you have many alias, you can also upload a file containing these alias, then 
			// use file_id to send customized notification.
			$customizedcast->setPredefinedKeyValue("alias", $this->alias);
			// Set your alias_type here
			$customizedcast->setPredefinedKeyValue("alias_type", $this->aliasType);
			$customizedcast->setPredefinedKeyValue("alert", $this->alert);
			$customizedcast->setPredefinedKeyValue("badge", 0);
			$customizedcast->setPredefinedKeyValue("sound", "chime");
			
			$customizedcast->setCustomizedField("type",$this->type);
			
			// Set 'production_mode' to 'true' if your app is under production mode
			$customizedcast->setPredefinedKeyValue("production_mode", "false");
			print("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
			print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			print("Caught exception: " . $e->getMessage());
		}
	}
}

// Set your appkey and master secret here
// $demo = new Demo("your appkey", "your app master secret");
// $demo->sendAndroidUnicast();
/* these methods are all available, just fill in some fields and do the test
 * $demo->sendAndroidBroadcast();
 * $demo->sendAndroidFilecast();
 * $demo->sendAndroidGroupcast();
 * $demo->sendAndroidCustomizedcast();
 * $demo->sendAndroidCustomizedcastFileId();
 *
 * $demo->sendIOSBroadcast();
 * $demo->sendIOSUnicast();
 * $demo->sendIOSFilecast();
 * $demo->sendIOSGroupcast();
 * $demo->sendIOSCustomizedcast();
 */