<?php
namespace Adminx\Model;

class FinanceModel extends \Think\Model
{
    protected $_auto = array(
        array('createTime', 'time', 1, 'function'),
    );
    protected $_validate = array(
        array('username', 'require', '请输入会员手机号码'),
        //array('type', 'check_type', '充值类型错误', 0, 'callback', 1),
        array('money', 'require', '请输入金额'),
        array('money', 'currency', '金额必须是货币')
    );



    // 手机号检测
    function check_type($type){
        $moneyType = C('moneyType');
        if (!array_key_exists($type, $moneyType)) {
            return false;
        }else{
            return true;
        }
    }
}

?>