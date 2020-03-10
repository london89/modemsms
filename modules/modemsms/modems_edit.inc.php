<?php
/*
* @version 0.1 (wizard)
*/
//DebMes($this->data_source);
//DebMes(gr('smsopt'));
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
   $fromsqltitle=SQLSelectOne("SELECT * FROM $table_name WHERE TITLE ='".DbSafe($rec['TITLE'])."'");
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'ip' (varchar)
   $rec['IP']=gr('ip');
  //updating 'type' (varchar)
   $rec['TYPE']=gr('type');
   $rec['SMSOPT']=gr('smsopt');

  } else if ($this->tab=='sms') {
	$delete_ids=gr('delete_ids');
  } else if ($this->tab=='data') {
	$delete_ids=gr('delete_ids');
  }
  // step: data
//  if ($this->tab=='data') {
//  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
     $out['OK']=1;
    } else {
     if (!$fromsqltitle['TITLE']) {
      $new_rec=1;
      $rec['ID']=SQLInsert($table_name, $rec); // adding new record
      $out['OK']=1;
     } else {
      $out['ERR']=1;
      $out['ERR_TITLE']='Модем с таким именем уже существует.';
     }
    }
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
	$smsopt=array(0 => 'Помечать как прочитанные', 1 => 'Удалять с модема', 2 => 'Ничего не делать');
	foreach ($smsopt as $key => $opt) {
		$rec['SMSOPTS'][$key]['NAME'] = $opt;
		$rec['SMSOPTS'][$key]['KEY'] = $key;
		if ($key == $rec['SMSOPT']) $rec['SMSOPTS'][$key]['SELECTED'] = 'selected';
	}

  }
  // step: data
//  if ($this->tab=='data') {
//  }
/*
  if ($this->tab=='sms') {
   if ($rec['TYPE'] == 'huawei') {
    include_once '3rdparty/Router.php';
    $router = new Router;
    $router->setAddress($rec['IP']);
    if (isset($delete_ids) && is_array($delete_ids)) {
       $router->deleteSms($delete_ids);
    }

    $page=1;
    if (isset($_GET['page']) && is_numeric($_GET['page'])) $page=$_GET['page'];
    $totalCount=SQLSelectOne("SELECT VALUE FROM modems_params WHERE DEVICE_ID='".$rec['ID']."' AND TITLE='LocalInbox'");

    if (isset($totalCount['VALUE']) && is_numeric($totalCount['VALUE'])) {
     $out['TOTALCOUNT']=$totalCount['VALUE'];
     $smss = $router->getInbox($page);
     $pagesCount=(int)($totalCount['VALUE']/20);
     $prevpage=$nextpage=array();
     for ($i=0;$i<=$pagesCount;$i++) {
 	$pages[$i]['NUM'] = $i+1;
 	if ($i+1 == $page) {
         if ($i>0) $prevpage['NUM']=$i;
         if ($i<$pagesCount) $nextpage['NUM']=$i+2;
	 $pages[$i]['SELECTED']='1';
	}
     }
    } else {
     $smss = $router->getInbox($page,500);

    }
    $total=$smss->Count;
    for ($i=0;$i<$total;$i++) {
       $properties[$i]['Smstat']=str_replace(array(0,1),array('message.png','accept.png'),$smss->Messages->Message[$i]->Smstat);
       $properties[$i]['Index']=$smss->Messages->Message[$i]->Index;
       $properties[$i]['Phone']=$smss->Messages->Message[$i]->Phone;
       $properties[$i]['Content']=$smss->Messages->Message[$i]->Content;
       $properties[$i]['Date']=$smss->Messages->Message[$i]->Date;
    }
   } else if ($rec['TYPE'] == 'zte') {
	include_once '3rdparty/Zte.php';
	$zte = new ZTE_WEB;
	$zte->setAddress($rec['IP']);
        if (isset($delete_ids) && is_array($delete_ids)) {
	    $ids_list=implode(";",$delete_ids);
//		DebMes($ids_list);
            $zte->delete_sms($ids_list);
//		DebMes($delete_ids);
        }

	$all_sms = $zte->get_sms();
	$i=0;
	foreach ($all_sms as $sms) {
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
	}
   }
   $out['PROPERTIES']=$properties;
   $out['PAGES']=$pages;
   $out['PREVPAGE']=$prevpage;
   $out['NEXTPAGE']=$nextpage;

  }
*/
  if ($this->tab=='sms') {
    if (isset($delete_ids) && is_array($delete_ids)) {
        $ids_list=implode(",",$delete_ids);
        SQLExec("DELETE FROM modems_sms WHERE ID IN (".DbSafe($ids_list).")");
        $page=1;
        if (isset($_GET['page']) && is_numeric($_GET['page'])) $page=$_GET['page'];
        $this->redirect('?tab=data&view_mode=edit_modems&tab=sms&id='.$rec['ID'].'&page='.$page);

//      DebMes($ids_list);
    }

    $page=1;
    if (isset($_GET['page']) && is_numeric($_GET['page'])) $page=$_GET['page'];
    $totalCount=SQLSelectOne("SELECT count(*) as count FROM modems_sms WHERE DEVICE_ID='".$rec['ID']."'");

    if (isset($totalCount['count']) && is_numeric($totalCount['count'])) {
     $out['TOTALCOUNT']=$totalCount['count'];
     $smss=SQLSelect("SELECT * FROM modems_sms WHERE DEVICE_ID='".$rec['ID']."' ORDER BY date DESC LIMIT ".(($page-1)*20).",20");
     $pagesCount=(int)($totalCount['count']/20);
     $prevpage=$nextpage=array();
     for ($i=0;$i<=$pagesCount;$i++) {
        $pages[$i]['NUM'] = $i+1;
        if ($i == $page-1) {
         if ($i>0) $prevpage['NUM']=$i;
         if ($i<$pagesCount) $nextpage['NUM']=$i+2;
         $pages[$i]['SELECTED']='1';
        }
     }
    }

   $i=0;
   foreach ($smss as $sms) {
    $properties[$i]['Smstat']=str_replace(array(0,1),array('message.png','accept.png'),$sms['SMSTAT']);
    $properties[$i]['Index']=$sms['ID'];
    $properties[$i]['Phone']=$sms['PHONE'];
    $properties[$i]['Content']=$sms['CONTENT'];
    $properties[$i]['Date']=$sms['DATE'];
    $i++;
   }
   $out['PROPERTIES']=$properties;
   $out['PAGES']=$pages;
   $out['NUM']=$page;
   $out['PREVPAGE']=$prevpage;
   $out['NEXTPAGE']=$nextpage;
//	DebMes($smss);
  }
  if ($this->command=='refresh') {
//	DebMes('Refresh');
	$this->checkModem();
        $this->redirect('?tab=data&view_mode=edit_modems&id='.$rec['ID']);

  }
  if ($this->command=='markasread') {
//	DebMes($_GET);
   SQLExec("UPDATE modems_sms set SMSTAT = 1 where DEVICE_ID=".$rec['ID']." AND ID=".DbSafe($_GET['msg_id']));
//   SQLUpdate();
/*   if ($rec['TYPE'] == 'huawei') {
    include_once '3rdparty/Router.php';
    $router = new Router;
    $router->setAddress($rec['IP']);
    $router->mark_as_read(array($_GET['msg_id']));

   } else if ($rec['TYPE'] == 'zte') {
    include_once '3rdparty/Zte.php';
    $zte = new ZTE_WEB;
    $zte->setAddress($rec['IP']);
    $zte->mark_as_read($_GET['msg_id']);


   }
*/
//	DebMes('markasread');
        $this->redirect('?tab=sms&view_mode=edit_modems&id='.$rec['ID'].'&page='.$_GET['page']);

  }
  if ($this->command=='fullrefresh') {
//	DebMes('Refresh');
	$this->checkModem(1);
        $this->redirect('?tab=data&view_mode=edit_modems&id='.$rec['ID']);

  }
  if ($this->tab=='data') {

//DebMes('data');
//DebMes('data');
   //dataset2
   $new_id=0;
/*   global $delete_id;
   if ($delete_id) {
    SQLExec("DELETE FROM modems_params WHERE ID='".(int)$delete_id."'");
    $this->redirect('?tab=data&view_mode=edit_modems&id='.$rec['ID']);
   }
*/
    if (isset($delete_ids) && is_array($delete_ids)) {
	$ids_list=implode(",",$delete_ids);
	SQLExec("DELETE FROM modems_params WHERE ID IN (".DbSafe($ids_list).")");
        $page=1;
        if (isset($_GET['page']) && is_numeric($_GET['page'])) $page=$_GET['page'];
        $this->redirect('?tab=data&view_mode=edit_modems&id='.$rec['ID'].'&page='.$page);

//	DebMes($ids_list);
    }
/*
   $properties=SQLSelect("SELECT * FROM modems_params WHERE DEVICE_ID='".$rec['ID']."' ORDER BY TITLE");
   $total=count($properties);
   if (!$total)    $this->checkModem();
//DebMes($this->mode);
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
*/
//   $out['PROPERTIES']=$properties;
  }

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
//  $this->redirect('?');
  }


  outHash($rec, $out);
