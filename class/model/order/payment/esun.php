<?php
require_once "returncode/esun.php";
class Model_Order_Payment_Esun {
    //put your code here
    protected $config;
    protected $mackey;
    protected $mode;
    protected $codedata = array();
    protected $url = array(
        'testing' => "https://acqtest.esunbank.com.tw/acq_online/online/sale42.htm",
        'running' => "https://acq.esunbank.com.tw/acq_online/online/sale42.htm",
    );
    protected $template = "templates/ws-cart-card-transmit-tpl.html";
    function __construct($config,$mackey,$mode="testing") {
        $this->config = $config;
        $this->mackey = $mackey;
        $this->mode = $mode;
        $this->codedata = array_merge($this->codedata,$this->config);
    }
    //結帳
    function checkout($o_id,$total_price,$extra_info=array()){
        $this->codedata['ono'] = strtoupper($o_id);
        $this->codedata['ta'] = $total_price;
        if(!empty($extra_info)){
            foreach($extra_info as $k => $v){
                if(!isset($this->codedata[$k])){
                    $this->codedata[$k] = $v;
                }
            }
        }
        $tpl = new TemplatePower($this->template);
        $tpl->prepare();
        foreach($this->codedata as $k => $v){
            $tpl->newBlock("CARD_FIELD_LIST");
            $tpl->assign(array(
                "TAG_KEY"   => strtoupper($k),
                "TAG_VALUE" => $v,
            ));
        }
        $code = $this->make_code($this->codedata);
        $tpl->assignGlobal("TAG_INPUT_STR",$code[0]);
        $tpl->newBlock("CARD_FIELD_LIST");
        $tpl->assign(array(
            "TAG_KEY"   => "M",
            "TAG_VALUE" => $code[1]
        ));
        $tpl->assignGlobal("AUTHORIZED_URL",$this->url[$this->mode]);
        $tpl->printToScreen();
        die();
    }
    //製作押碼
    function make_code($codedata){
        $input_str = implode("&",$codedata) . "&" . $this->mackey;
        return array($input_str,md5($input_str));
    }
    //更新訂單
    function update_order(DB $db,$result){
        $oid = $result['ONO'];
        if($result['RC']=='00'){ //交易成功
            if($this->validate($result)){
                //更新訂單資料
                $sql = "update ".$db->prefix("order")." set "
                        . "o_status = '1', "
                        . "RC = '".$db->quote($result['RC'])."', "
                        . "LTD = '".$db->quote($result['LTD'])."', "
                        . "LTT = '".$db->quote($result['LTT'])."', "
                        . "RRN = '".$db->quote($result['RRN'])."', "
                        . "AIR = '".$db->quote($result['AIR'])."' "
                        . "where o_id='".$db->quote($oid)."'";
            }else{
                throw new Exception("return result doesn't valiated!");
            }
        }else{
            //更新訂單狀態
            if($result['RC']!='G6'){ //錯誤原因非訂單編號重複
                $sql = "update ".$db->prefix("order")." set "
                        . "o_status='21', "
                        . "RC = '".$db->quote($result['RC'])."' "
                        . "where o_id='".$db->quote($oid)."'";
            }
        }
        //$db->query($sql,true);
        //$sql = "select * from ".$db->prefix("order")." where o_id='".$oid."'";
        //return $db->query_firstRow($sql,true);
        return $sql;
    }
    //驗證回傳結果
    function validate($result){
        $m = array_pop($result);
        $code = $this->make_code($result);
        return ($m==$code[1]);
    }
    
}
