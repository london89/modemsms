<?php
/*
* @version 0.1 (wizard)
*/

  if ($this->mode=='sendsms') {
   $err=$err_title='';

   $phone = gr('phone');
   $text = gr('text');
   $send = $this->sendSMS($id,$phone,$text);
   if ($send) {
	$out['OK'] = 1;
   } else {
	$out['ERR'] = 1;
	$out['ERR_TITLE'] = 'Отправка не удалась...';

   }

  }

