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
function getModemParams (&$out, $id,$page) {

   $count=SQLSelectOne("SELECT count(*) as count FROM modems_params WHERE DEVICE_ID='".$id."'");
   $count=$count['count'];
   $pagesCount=(int)($count/10);
   $prevpage=$nextpage=array();
   for ($i=0;$i<=$pagesCount;$i++) {
     $pages[$i]['NUM'] = $i+1;
     if ($i+1 == $page) {
	if ($i>0) $prevpage['NUM']=$i;
	if ($i<$pagesCount) $nextpage['NUM']=$i+2;
	$pages[$i]['SELECTED']=$i;
     }
   }


   $properties=SQLSelect("SELECT * FROM modems_params WHERE DEVICE_ID='".$id."' ORDER BY UPDATED DESC LIMIT ".(($page-1)*10).",10");

   $total=count($properties);
   if (!$total)    $this->checkModem(1);

//DebMes($this->mode);
   for($i=0;$i<$total;$i++) {
//    if ($properties[$i]['ID']==$new_id) continue;
//DebMes(${'title'.$properties[$i]['ID']});
    if ($this->mode=='update') {
      global ${'title'.$properties[$i]['ID']};
      $properties[$i]['TITLE']=trim(${'title'.$properties[$i]['ID']});
      global ${'note'.$properties[$i]['ID']};
      $properties[$i]['NOTE']=trim(${'note'.$properties[$i]['ID']});
      global ${'value'.$properties[$i]['ID']};
      $properties[$i]['VALUE']=trim(${'value'.$properties[$i]['ID']});
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      global ${'linked_method'.$properties[$i]['ID']};
      $properties[$i]['LINKED_METHOD']=trim(${'linked_method'.$properties[$i]['ID']});
//DebMes($properties[$i]);
      SQLUpdate('modems_params', $properties[$i]);
      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
      if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
      }

     }

    }
    $out['PROPERTIES'] = $properties;
    $out['PAGES'] = $pages;
    $out['PREVPAGE'] = $prevpage;
    $out['NEXTPAGE'] = $nextpage;
}

function sendSMS($title,$phone,$text) {
//DebMes($title.$phone.$text);
   $table_name='modems';
   $rec=SQLSelectOne("SELECT * FROM $table_name WHERE TITLE='".DbSafe($title)."'");
   if ($rec['TYPE'] == 'huawei') {
    include_once '3rdparty/Router.php';
    $router = new Router;
    $router->setAddress($rec['IP']);
//    $send = 1;
// 1 - если все ок
    $send = $router->sendSms($phone,$text);
    if ($send == 1) {
	return 1;
    } else {
	DebMes($send);
	return 0;
    }

   } else if ($rec['TYPE'] == 'zte') {
    include_once '3rdparty/Zte.php';
    $zte = new ZTE_WEB;
    $zte->setAddress($rec['IP']);
//    $send = '{"result":"success"}';
//{"result":"success"} если всё ок
//DebMes($phone.' '.$text);
    $send = $zte->send($phone,$text);
    $send = json_decode($send);
    if ($send->result == 'success') {
	return 1;
    } else {
	DebMes($send);
	return 0;
    }
   }


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
   if ($this->tab == 'smssend') {
 	$this->sms_send($out, $this->id);
   }

   if ($this->tab == 'data') {
	$page=1;
	if (isset($_GET['page']) && is_numeric($_GET['page'])) $page=$_GET['page'];
//DebMes('data');
	$this->getModemParams($out, $this->id,$page);
   }
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
/*
 if ($this->data_source=='modems_params') {
  if ($this->view_mode=='' || $this->view_mode=='search_modems_params') {
   $this->search_modems_params($out);
  }
  if ($this->view_mode=='edit_modems_params') {
   $this->edit_modems_params($out, $this->id);
  }
 }
*/
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
 function sms_send(&$out, $id) {
  require(DIR_MODULES.$this->name.'/modems_sms_send.inc.php');
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
//	DebMes('cycle from processCycle');
	$this->checkModem();
  //to-do
 }
 function getSms($id,$smscount,$perpage=20) {
  $rec=SQLSelectOne("SELECT * FROM modems WHERE ID='$id'");
  if ($rec['TYPE'] == 'huawei') {
   include_once '3rdparty/Router.php';
   $router = new Router;
   $router->setAddress($rec['IP']);
   for ($a=0;$a*$perpage<=$smscount;$a++) {
    $smss = $router->getInbox($a+1,$perpage);
    $total=$smss->Count;
//   DebMes($total);
    $indexes=array();
    for ($i=0;$i<$total;$i++) {
//DebMes($smss);
     if ($smss->Messages->Message[$i]->Smstat == 0) {
      $todb['SMSTAT']=0;
      $todb['IND']=$smss->Messages->Message[$i]->Index;
      $todb['PHONE']=$smss->Messages->Message[$i]->Phone;
      $todb['DEVICE_ID'] = $id;
      $todb['CONTENT']=$smss->Messages->Message[$i]->Content;
      $todb['DATE']=$smss->Messages->Message[$i]->Date;
      $indexes[]=$todb['IND'];
      SQLInsert('modems_sms',$todb);

     if ($rec['LINKED_OBJECT'] && $rec['LINKED_METHOD']) {
        callMethod($rec['LINKED_OBJECT'] . '.' . $rec['LINKED_METHOD'], array('PHONE'=>$todb['PHONE'],'TEXT' => $todb['CONTENT'], 'DATE' => $todb['DATE']));
     }
     }
    }
    if (count($indexes)) {

     // помечаем полученные сообщения как прочитанные
     if ($rec['SMSOPT'] == 0) {
      $router->mark_as_read($indexes);
     // или, удаляем полученные сообщения
     } else if ($rec['SMSOPT'] == 1) {
      $router->deleteSms($indexes);
     }
    // DebMes($indexes);
    }
   }
  } else if ($rec['TYPE'] == 'zte') {
   include_once '3rdparty/Zte.php';
   $zte = new ZTE_WEB;
   $zte->setAddress($rec['IP']);
   $all_sms = $zte->get_sms();
   $i=0;
   $indexes=array();
   foreach ($all_sms as $sms) {
//DebMes($sms['tag']);
    if ($sms['tag'] == 1) {
//    if (true) {
     $todb['SMSTAT']=0;
     $todb['IND']=$sms['id'];
     $todb['PHONE']=$sms['number'];
     $todb['DEVICE_ID'] = $id;
     $todb['CONTENT']=$sms['content'];
     preg_match_all('/\d+/',$sms['date'],$m);
     $m=$m[0];
     $unixtime=mktime($m[3],$m[4],$m[5],$m[1],$m[2],$m[0]);
     $norm_date=date('Y-m-d H:i:s',$unixtime);
     $todb['DATE']=$norm_date;
     $indexes[]=$todb['IND'];
     SQLInsert('modems_sms',$todb);
    }

/*
    $properties[$i]['Smstat']=str_replace(array(0,1,2),array('accept.png','message.png','warning.png'),$sms['tag']);
    $properties[$i]['Index']=$sms['id'];
    $properties[$i]['Phone']=$sms['number'];
    $properties[$i]['Content']=$sms['content'];
    preg_match_all('/\d+/',$sms['date'],$m);
    $m=$m[0];
    $unixtime=mktime($m[3],$m[4],$m[5],$m[1],$m[2],$m[0]);
    $norm_date=date('Y-m-d H:i:s',$unixtime);
    $properties[$i]['Date']=$norm_date;
    $i++;
*/
   }
   if (count($indexes)) {
    // помечаем полученные сообщения как прочитанные
    if ($rec['SMSOPT'] == 0) {
     $zte->mark_as_read($indexes);
    // или, удаляем полученные сообщения
    } else if ($rec['SMSOPT'] == 1) {
     $zte->delete_sms($indexes);
    }
   // DebMes($indexes);
   }
  }
 }
 function checkModem($full=0) {
//  $modemlist=SQLSelect("SELECT * FROM modemsms_devices WHERE CHECK_NEXT<=NOW()");
  $modemlist=SQLSelect("SELECT * FROM modems");
  $total=count($modemlist);
  for($i=0;$i<$total;$i++) {
   $modem=$modemlist[$i];
   $prec=SQLSelect("SELECT * FROM modems_params WHERE DEVICE_ID=".$modem['ID']);
//   $sms=SQLSelect("SELECT * FROM modems_sms WHERE DEVICE_ID=".$modem['ID']);
//	DebMes($modem);
   if ($modem['TYPE'] == 'huawei') {
	include_once '3rdparty/Router.php';
	$router = new Router;
	$router->setAddress($modem['IP']);
	$status = $router->getStatus();
	$signal = $router->getSignal();
	$network = $router->getNetwork();
	$smsCount = $router->getSmsCount();
	$traff = $router->getTrafficStats();
	$month = $router->getMonthStats();
	$modemTotal=(object)array_merge((array)$status,(array)$network,(array)$smsCount,(array)$traff,(array)$month,(array)$signal);
//DebMes($modemTotal);
	foreach ($modemTotal as $key => $value) {
		$new=1;
		foreach ($prec as $line => $param) {
			if ($key == $param['TITLE']) {
				//такой параметр найден. проверяем, изменилось ли значение.
				if ($value != $param['VALUE']) {
					if ($param['LINKED_OBJECT'] && $param['LINKED_PROPERTY']) {
						setGlobal($param['LINKED_OBJECT'].'.'.$param['LINKED_PROPERTY'], $param['VALUE'], array($this->name=>'0'));
					} else if ($param['LINKED_OBJECT'] && $param['LINKED_METHOD']) {
						callMethod($param['LINKED_OBJECT'] . '.' . $param['LINKED_METHOD'], array('VALUE'=>$param['VALUE']));
					}

        	                        $param['VALUE'] = $value;
					$param['UPDATED'] = date('Y-m-d H:i:s');
	                                SQLUpdate('modems_params', $param);
//					DebMes('update '.$key);
				}
				$new=0;
//DebMes($param['TITLE']);
			}
		}
		if ($new && $full) {
			// не было у нас еще такого параметра, добавляем
                        $rec_par['DEVICE_ID'] = $modem['ID'];
                        $rec_par['TITLE'] = $key;
//                        $rec_par['NOTE'] = 'Описание';
	                $rec_par['VALUE'] = $value;
	                $rec_par['UPDATED'] = date('Y-m-d H:i:s');
                        SQLInsert('modems_params', $rec_par);
		}
	}
        $this->getSms($modem['ID'],$modemTotal->LocalInbox,20);

//	$smsFromModem = $router->getInbox(0,500);
//	foreach ()


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
                if ($new && $full) {
                        // не было у нас еще такого параметра, добавляем
                        $rec_par['DEVICE_ID'] = $modem['ID'];
                        $rec_par['TITLE'] = $key;
//                        $rec_par['NOTE'] = 'Описание';
                        $rec_par['VALUE'] = $value;
                        $rec_par['UPDATED'] = date('Y-m-d H:i:s');
                        SQLInsert('modems_params', $rec_par);
                }
        }
//DebMes($modemTotal->sms_nv_rev_total);
        $this->getSms($modem['ID'],$modemTotal->sms_nv_rev_total,20);

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
 modems: SMSOPT int(10) unsigned DEFAULT NULL
 modems: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 modems: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''

 modems_params: ID int(10) unsigned NOT NULL auto_increment
 modems_params: TITLE varchar(100) NOT NULL DEFAULT ''
 modems_params: NOTE varchar(255) NOT NULL DEFAULT ''
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
