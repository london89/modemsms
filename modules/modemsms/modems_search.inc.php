<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['modems_qry'];
  } else {
   $session->data['modems_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_modems="ID DESC";
  $out['SORTBY']=$sortby_modems;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM modems WHERE $qry ORDER BY ".$sortby_modems);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
