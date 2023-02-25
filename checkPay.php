<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 数据库配置
	include './Db.php';

	// 实例化类
	$db = new DB_API($config);
	
    // 获取订单号和支付金额
	$order_num = $_GET['order_num'];
	$order_money = $_GET['order_money'];
	
    // 根据订单号和订单金额来查询支付结果
    $getOrderPayStatus = $db->set_table('mqpay_order')->find(['order_num'=>$order_num,'order_money'=>$order_money]);
    
    // 判断支付结果
    if($getOrderPayStatus){
        
        // 支付状态
        $order_status = json_decode(json_encode($getOrderPayStatus))->order_status;
        
        if($order_status == 2){
            
            // 支付成功
            $payResult = array(
                'code' => 200,
                'msg' => '支付成功'
            );
        }else{
            
            // 未支付
            $payResult = array(
                'code' => 202,
                'msg' => '未支付'
            );
        }
        
    }else{
        
        // 无结果
        $payResult = array(
            'code' => 201,
            'msg' => '未支付'
        );
    }
    
    // 返回JSON
    echo json_encode($payResult,JSON_UNESCAPED_UNICODE);
    
?>