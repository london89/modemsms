<?php

mb_internal_encoding("UTF-8");

class ZTE_WEB
{
	public $tz="+3";
        public function setAddress($address)
        {
                //Remove trailing slash if any.
/*                $address = rtrim($address, '/');

                //If not it starts with http, we assume HTTP and add it.
                if(strpos($address, 'http') !== 0)
                {
                        $address = 'http://'.$address;
                }
*/
                $this->ip = $address;
        }

        public function url($url,$post="")
        {
                        $ch = curl_init($url);
                        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                        curl_setopt($ch,CURLOPT_BINARYTRANSFER,false);
                        curl_setopt($ch,CURLOPT_HEADER,false);
                        curl_setopt($ch,CURLOPT_TIMEOUT, 90);
                        curl_setopt($ch, CURLOPT_REFERER, 'http://'.$this->ip.'/index.html');
                        $header = array();
                        $header[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
                        $header[] = 'Accept-Charset: Windows-1251,utf-8;q=0.7,*;q=0.7';
                        $header[] = 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3';
                        $header[] = 'Pragma: ';
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                        unset ($header);
                        if(!empty($post)) {curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, $post);}
                $content = curl_exec($ch);
                        curl_close($ch);
                return $content;
        }

        public function utf2hex($str)
        {
                $l=mb_strlen($str);
                $res='';
                for ($i=0;$i<$l;$i++)
                {
                        $s = mb_substr($str,$i,1);
                        $s = mb_convert_encoding($s, 'UCS-2LE', 'UTF-8');
                $s = dechex(ord(substr($s, 1, 1))*256+ord(substr($s, 0, 1)));
                if (mb_strlen($s)<4) $s = str_repeat("0",(4-mb_strlen($s))).$s;
                $res.=$s;
        }
        return $res;
        }

        public function hex2utf($str)
        {
                $l=mb_strlen($str)/4;
                $res='';
                for ($i=0;$i<$l;$i++) $res.=html_entity_decode('&#'.hexdec(mb_substr($str,$i*4,4)).';',ENT_NOQUOTES,'UTF-8');
        return $res;
        }

        //отправляет смску
        public function send($number,$text)
        {
                $url = 'http://'.$this->ip.'/goform/goform_set_cmd_process';
                $post='isTest=false&';
                $post.= 'goformId=SEND_SMS&';
                $post.= 'notCallback=true&';
                $post.= 'Number='.urlencode($number).'&';
                $date = gmdate('y;m;d;h;i;s;'.$this->tz,time()+($this->tz*3600));
                $post.= 'sms_time='.urlencode($date).'&';
                $post.= 'MessageBody='.($this->utf2hex($text)).'&';
                $post.= 'ID=-1&';
                $post.= 'encode_type=UNICODE';
                return $this->url($url,$post);
        }

        //возвращает массив всех смсок
        public function get_sms($page=1)
        {
		$page--;
//                $cont=$this->url('http://'.$this->ip.'/goform/goform_get_cmd_process?cmd=sms_data_total&page=$page&data_per_page=10&mem_store=1&tags=10&order_by=order+by+id+desc');
                $cont=$this->url('http://'.$this->ip.'/goform/goform_get_cmd_process?cmd=sms_data_total&page=$page&data_per_page=500&mem_store=1&tags=10&order_by=order+by+id+desc');
                $cont = json_decode($cont,true);
                $cont = $cont['messages'];
//DebMes(count($cont).' '.$page);
                foreach ($cont as $id => $arr) $cont[$id]['content']=$this->hex2utf(($cont[$id]['content']));
                return $cont;
        }
        public function get_params()
        {
                $cont=$this->url('http://'.$this->ip.'/goform/goform_get_cmd_process?multi_data=1&isTest=false&sms_received_flag_flag=0&sts_received_flag_flag=0&cmd=modem_main_state%2Cpin_status%2Cloginfo%2Cnew_version_state%2Ccurrent_upgrade_state%2Cis_mandatory%2Csms_received_flag%2Csts_received_flag%2Csignalbar%2Cnetwork_type%2Cnetwork_provider%2Cppp_status%2CEX_SSID1%2Cex_wifi_status%2CEX_wifi_profile%2Cm_ssid_enable%2Csms_unread_num%2CRadioOff%2Csimcard_roam%2Clan_ipaddr%2Cstation_mac%2Cbattery_charging%2Cbattery_vol_percent%2Cbattery_pers%2Cspn_display_flag%2Cplmn_display_flag%2Cspn_name_data%2Cspn_b1_flag%2Cspn_b2_flag%2Crealtime_tx_bytes%2Crealtime_rx_bytes%2Crealtime_time%2Crealtime_tx_thrpt%2Crealtime_rx_thrpt%2Cmonthly_rx_bytes%2Cmonthly_tx_bytes%2Cmonthly_time%2Cdate_month%2Cdata_volume_limit_switch%2Cdata_volume_limit_size%2Cdata_volume_alert_percent%2Cdata_volume_limit_unit%2Croam_setting_option%2Cupg_roam_switch');
                $cont = json_decode($cont,true);
//                $cont = $cont['messages'];
  //              foreach ($cont as $id => $arr) $cont[$id]['content']=$this->hex2utf(($cont[$id]['content']));
                return $cont;
        }
        public function get_net_params()
        {
                $cont=$this->url('http://'.$this->ip.'/goform/goform_get_cmd_process?isTest=false&cmd=wifi_coverage%2Cm_ssid_enable%2Cimei%2Cweb_version%2Cwa_inner_version%2Chardware_version%2CMAX_Access_num%2CSSID1%2Cm_SSID%2Cm_HideSSID%2Cm_MAX_Access_num%2Clan_ipaddr%2Cwan_active_band%2Cmac_address%2Cmsisdn%2CLocalDomain%2Cwan_ipaddr%2Cipv6_wan_ipaddr%2Cipv6_pdp_type%2Cpdp_type%2Cppp_status%2Csim_iccid%2Csim_imsi%2Crmcc%2Crmnc%2Crssi%2Crscp%2Clte_rsrp%2Cecio%2Clte_snr%2Cnetwork_type%2Clte_rssi%2Clac_code%2Ccell_id%2Clte_pci%2Cdns_mode%2Cprefer_dns_manual%2Cstandby_dns_manual%2Cprefer_dns_auto%2Cstandby_dns_auto%2Cipv6_dns_mode%2Cipv6_prefer_dns_manual%2Cipv6_standby_dns_manual%2Cipv6_prefer_dns_auto%2Cipv6_standby_dns_auto%2Cmodel_name&multi_data=1');
                $cont = json_decode($cont,true);
//                $cont = $cont['messages'];
  //              foreach ($cont as $id => $arr) $cont[$id]['content']=$this->hex2utf(($cont[$id]['content']));
                return $cont;
        }
        public function get_sms_params()
        {
                $cont=$this->url('http://'.$this->ip.'/goform/goform_get_cmd_process?isTest=false&cmd=sms_capacity_info');
                $cont = json_decode($cont,true);
//                $cont = $cont['messages'];
  //              foreach ($cont as $id => $arr) $cont[$id]['content']=$this->hex2utf(($cont[$id]['content']));
                return $cont;
        }

        //удаляет все смс
        public function clear_sms($cont=0)
        {
                if ($cont===0) $cont=$this->get_sms();
                $list_id='';
                $url = 'http://'.$this->ip.'/goform/goform_set_cmd_process';
                foreach ($cont as $id => $arr) $list_id.=$cont[$id]['id'].';';
                $post='isTest=false&goformId=DELETE_SMS&msg_id='.urlencode($list_id).'&Callback=true';
                return $this->url($url,$post);
        }
        public function delete_sms($id)
        {
		if (!isset($id) || !is_numeric($id)) return false;
                $url = 'http://'.$this->ip.'/goform/goform_set_cmd_process';
                $post='isTest=false&goformId=DELETE_SMS&msg_id='.$id.';&Callback=true';
                return $this->url($url,$post);
        }

        public function mark_as_read($id)
        {
                $url = 'http://'.$this->ip.'/goform/goform_set_cmd_process';
         	$id = $id.';';
                $post='isTest=false&goformId=SET_MSG_READ&msg_id='.urlencode($id).'&tag=0';
                return $this->url($url,$post);
        }

}
