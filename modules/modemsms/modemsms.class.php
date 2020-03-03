<?php
/**
* smsformodem 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 15:02:06 [Feb 18, 2020])
*/
//
//
class modemsms extends module {
/**
* modemsms
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="modemsms";
  $this->title="Управление модемом";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 if (IsSet($this->command)) {
  $p["command"]=$this->command;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  global $command;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
  if (isset($command)) {
   $this->command=$command;
  }
//$this->checkModem();
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $out['COMMAND']=$this->command;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
//DebMes($out);
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='modems' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_modems') {
   $this->search_modems($out);
  }
  if ($this->view_mode=='edit_modems') {
   $this->edit_modems($out, $this->id);
  }
  if ($this->view_mode=='delete_modems') {
   $this->delete_modems($this->id);
   $this->redirect("?data_source=modems");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='modems_params') {
  if ($this->view_mode=='' || $this->view_mode=='search_modems_params') {
   $this->search_modems_params($out);
  }
  if ($this->view_mode=='edit_modems_params') {
   $this->edit_modems_params($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* modems search
*
* @access public
*/
 function search_modems(&$out) {
  require(DIR_MODULES.$this->name.'/modems_search.inc.php');
 }
/**
* modems edit/add
*
* @access public
*/
 function edit_modems(&$out, $id) {
  require(DIR_MODULES.$this->name.'/modems_edit.inc.php');
 }
/**
* modems delete record
*
* @access public
*/
 function delete_modems($id) {
  $rec=SQLSelectOne("SELECT * FROM modems WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM modems WHERE ID='".$rec['ID']."'");
 }
/**
* modems_params search
*
* @access public
*/
 function search_modems_params(&$out) {
  require(DIR_MODULES.$this->name.'/modems_params_search.inc.php');
 }
/**
* modems_params edit/add
*
* @access public
*/
 function edit_modems_params(&$out, $id) {
  require(DIR_MODULES.$this->name.'/modems_params_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
   $table='modems_params';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }
 function processCycle() {
  //to-do
 }
 function getSms() {
//        include_once '3rdparty/Router.php';
//        $router = new Router;
//        $router->setAddress($modem['URL']);

 }
 function checkModem() {
//  $modemlist=SQLSelect("SELECT * FROM modemsms_devices WHERE CHECK_NEXT<=NOW()");
  $modemlist=SQLSelect("SELECT * FROM modems");
  $total=count($modemlist);
  for($i=0;$i<$total;$i++) {
   $modem=$modemlist[$i];
   $prec=SQLSelect("SELECT * FROM modems_params WHERE DEVICE_ID=".$modem['ID']);
//	DebMes($modem);
   if ($modem['TYPE'] == 'huawei') {
	include_once '3rdparty/Router.php';
	$router = new Router;
	$router->setAddress($modem['IP']);
	$status = $router->getStatus();
	$network = $router->getNetwork();
	$smsCount = $router->getSmsCount();
	$traff = $router->getTrafficStats();
	$month = $router->getMonthStats();
	$modemTotal=(object)array_merge((array)$status,(array)$network,(array)$smsCount,(array)$traff,(array)$month);
//DebMes($modemTotal);
	foreach ($modemTotal as $key => $value) {
		$new=1;
		foreach ($prec as $line => $param) {
			if ($key == $param['TITLE']) {
				//такой параметр найден. проверяем, изменилось ли значение.
				if ($value != $param['VALUE']) {
        	                        $param['VALUE'] = $value;
					$param['UPDATED'] = date('Y-m-d H:i:s');
	                                SQLUpdate('modems_params', $param);
//					DebMes('update '.$key);
				}
				$new=0;
			}
		}
		if ($new && ($value != '')) {
			// не было у нас еще такого параметра, добавляем
                        $rec_par['DEVICE_ID'] = $modem['ID'];
                        $rec_par['TITLE'] = $key;
//                        $rec_par['NOTE'] = 'Описание';
	                $rec_par['VALUE'] = $value;
	                $rec_par['UPDATED'] = date('Y-m-d H:i:s');
                        SQLInsert('modems_params', $rec_par);
		}
	}


   } else if ($modem['TYPE'] == 'zte') {
//	DebMes('zte');
	include_once('3rdparty/Zte.php');
        $zte = new ZTE_WEB;
        $zte->setAddress($modem['IP']);
        $params = $zte->get_params();
        $smsparams = $zte->get_sms_params();
	$netparams = $zte->get_net_params();
	$modemTotal=(object)array_merge((array)$smsparams,(array)$params,(array)$netparams);
        foreach ($modemTotal as $key => $value) {
                $new=1;
                foreach ($prec as $line => $param) {
                        if ($key == $param['TITLE']) {
                                //такой параметр найден. проверяем, изменилось ли значение.
                                if ($value != $param['VALUE']) {
                                        $param['VALUE'] = $value;
                                        $param['UPDATED'] = date('Y-m-d H:i:s');
                                        SQLUpdate('modems_params', $param);
//                                      DebMes('update '.$key);
                                }
                                $new=0;
                        }
                }
                if ($new && ($value != '')) {
                        // не было у нас еще такого параметра, добавляем
                        $rec_par['DEVICE_ID'] = $modem['ID'];
                        $rec_par['TITLE'] = $key;
//                        $rec_par['NOTE'] = 'Описание';
                        $rec_par['VALUE'] = $value;
                        $rec_par['UPDATED'] = date('Y-m-d H:i:s');
                        SQLInsert('modems_params', $rec_par);
                }
        }


   }
  }
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS modems');
  SQLExec('DROP TABLE IF EXISTS modems_params');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
modems - 
modems_params - 
*/
  $data = <<<EOD
 modems: ID int(10) unsigned NOT NULL auto_increment
 modems: TITLE varchar(100) NOT NULL DEFAULT ''
 modems: TYPE varchar(255) NOT NULL DEFAULT ''
 modems: IP varchar(255) NOT NULL DEFAULT ''
 modems: CHECK_LATEST datetime DEFAULT NULL
 modems: CHECK_NEXT datetime DEFAULT NULL
 modems: INTERVAL int(10) unsigned DEFAULT NULL

 modems_params: ID int(10) unsigned NOT NULL auto_increment
 modems_params: TITLE varchar(100) NOT NULL DEFAULT ''
 modems_params: VALUE varchar(255) NOT NULL DEFAULT ''
 modems_params: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 modems_params: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 modems_params: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 modems_params: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 modems_params: UPDATED datetime
 
 modems_sms: ID int(10) unsigned NOT NULL auto_increment
 modems_sms: SMSTAT int(10) DEFAULT NULL
 modems_sms: IND int(10) DEFAULT NULL
 modems_sms: PHONE varchar(255) NOT NULL DEFAULT ''
 modems_sms: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 modems_sms: CONTENT LONGTEXT NOT NULL DEFAULT ''
 modems_sms: DATE datetime

EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDE4LCAyMDIwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
