<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='modems';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating '<%LANG_TITLE%>' (varchar, required)
   $rec['TITLE']=gr('title');
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'ip' (varchar)
   $rec['IP']=gr('ip');
  //updating 'type' (varchar)
   $rec['TYPE']=gr('type');
  }
  // step: data
  if ($this->tab=='data') {
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  // step: default
  if ($this->tab=='') {
	$modems=array('huawei','zte');
	for ($i=0;$i<count($modems);$i++) {
		$rec['MODEMS'][$i]['NAME'] = $modems[$i];
		if ($modems[$i] == $rec['TYPE']) $rec['MODEMS'][$i]['SELECTED'] = 'selected';

	}
  }
  // step: data
  if ($this->tab=='data') {
  }
  if ($this->tab=='sms') {
   if ($rec['TYPE'] == 'huawei') {
    include_once '3rdparty/Router.php';
    $router = new Router;
    $router->setAddress($rec['IP']);
    if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
 	$router->deleteSms($_GET['delete_id']);
    }
    $page=1;
    if (isset($_GET['page']) && is_numeric($_GET['page'])) $page=$_GET['page'];
    $totalCount=SQLSelectOne("SELECT VALUE FROM modems_params WHERE DEVICE_ID='".$rec['ID']."' AND TITLE='LocalInbox'");

 //DebMes($totalCount);
    if (isset($totalCount['VALUE']) && is_numeric($totalCount['VALUE'])) {
     $out['TOTALCOUNT']=$totalCount['VALUE'];
     $smss = $router->getInbox($page);
     $pagesCount=(int)($totalCount['VALUE']/20);
 //DebMes($pagesCount);

     for ($i=0;$i<=$pagesCount;$i++) {
 	$pages[$i]['NUM'] = $i+1;
 	if ($i+1 == $page) $pages[$i]['SELECTED']='1';
     }
 /*    $pages[0]['NUM']='1';
     $pages[1]['NUM']='2';
     $pages[1]['SELECTED']='1';
     $pages[2]['NUM']='3';
 */
    } else {
     $smss = $router->getInbox($page,500);

    }
 //DebMes($smss);
 //   for ($smss->Messages->Message as $key => $sms) {
    $total=$smss->Count;
 //DebMes($total);
    for ($i=0;$i<$total;$i++) {
       //global ${'title'.$properties[$i]['ID']};
       $properties[$i]['Smstat']=str_replace(array(0,1),array('message.png','accept.png'),$smss->Messages->Message[$i]->Smstat);
       $properties[$i]['Index']=$smss->Messages->Message[$i]->Index;
       $properties[$i]['Phone']=$smss->Messages->Message[$i]->Phone;
       $properties[$i]['Content']=$smss->Messages->Message[$i]->Content;
       $properties[$i]['Date']=$smss->Messages->Message[$i]->Date;
 //	DebMes($smss->Messages->Message[$i]->Smstat);
    }
 //DebMes('123333');
 //	DebMes(print_r($sms->Index,true));
 //	DebMes($key);
   } else if ($rec['TYPE'] == 'zte') {
	include_once '3rdparty/Zte.php';
	$zte = new ZTE_WEB;
	$zte->setAddress($rec['IP']);
	$all_sms = $zte->get_sms();
	$i=0;
	foreach ($all_sms as $sms) {
//		DebMes($sms['tag']);
		$properties[$i]['Smstat']=str_replace(array(0,1,2),array('message.png','accept.png','accept.png'),$sms['tag']);
		$properties[$i]['Index']=$sms['id'];
		$properties[$i]['Phone']=$sms['number'];
		$properties[$i]['Content']=$sms['content'];
		preg_match_all('/\d+/',$sms['date'],$m);
		$m=$m[0];
//		$norm_date=$m[2].'.'.$m[1].'.'.$m[0].' '.$m[3].':'.$m[4].':'.$m[5];
		$norm_date='20'.$m[0].'-'.$m[1].'-'.$m[2].' '.$m[3].':'.$m[4].':'.$m[5];
		$properties[$i]['Date']=$norm_date;
		$i++;
	}
   }
   $out['PROPERTIES']=$properties;
   $out['PAGES']=$pages;

  }

  if ($this->tab=='data') {

   //dataset2
   $new_id=0;
   global $delete_id;
   if ($delete_id) {
    SQLExec("DELETE FROM modems_params WHERE ID='".(int)$delete_id."'");
   }
   $properties=SQLSelect("SELECT * FROM modems_params WHERE DEVICE_ID='".$rec['ID']."' ORDER BY TITLE");
   $total=count($properties);
   if (!$total)    $this->checkModem();
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['ID']==$new_id) continue;
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
   $out['PROPERTIES']=$properties;
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }

  outHash($rec, $out);
