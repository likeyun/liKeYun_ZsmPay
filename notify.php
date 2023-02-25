<?php

    // 页面编码
    header("Content-type:application/json");

    // 原文
    $orderMsg = $_GET['orderMsg'];
    
    // 数据库配置
	include './Db.php';

	// 实例化类
	$db = new DB_API($config);
	
    // 订单金额、需支付的金额、通知原文、db实例
	updateOrder($orderMsg,$db);
    
    // 修改支付结果
    function updateOrder($orderMsg,$db){
        
        // 截取
        // 示例：二维码赞赏到账1.00元
        // 截取【到账】后面的
        $money_1 = substr($orderMsg, strripos($orderMsg, "到账") + 6);
        
        // 截取【元】前面的
        $money_2 = substr($money_1, 0, strrpos($money_1, "元"));
        
        // 更新订单
        $updateOrderResult = $db->set_table('mqpay_order')->update(['order_status'=>1,'order_money'=>$money_2],['order_status'=>2,'order_paytime'=>time(),'order_msg'=>$orderMsg]);

        if($updateOrderResult){
            
            // 成功
            $ret = array(
                'code' => 200,
                'msg' => '支付成功',
                'order_num' => $order_num,
                'order_money' => $money_2,
                'order_msg' => $orderMsg
            );
        }else{
            
            // 失败
            $ret = array(
                'code' => 200,
                'msg' => '支付失败',
                'order_num' => $order_num
            );
        }
    }
    
    // 返回JSON
    echo json_encode($ret,JSON_UNESCAPED_UNICODE);
    
?>