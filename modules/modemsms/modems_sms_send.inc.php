<?php
/*
* @version 0.1 (wizard)
*/

  if ($this->mode=='sendsms') {
   $err=$err_title='';
   $phone = gr('phone');
   $text = gr('text');
   $table_name='modems';
   $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
   if (isset($rec['TITLE'])) {
//   $send = $this->sendSMS($title,$phone,$text);
     $send=1;
//     DebMes($rec['TITLE'].$phone.$text);
     if ($send) {
      $out['OK'] = 1;
     } else {
      $out['ERR'] = 1;
      $out['ERR_TITLE'] = 'Отправка не удалась...';
     }
    }
   }
