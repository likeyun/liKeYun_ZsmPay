<?php
    
    /**
     * 2024-07-02
     * 作者：TANKING
     * 作者博客：https://segmentfault.com/u/tanking
     * 程序说明：轮询订单回调状态
     */ 
    
    // 编码
    header("Content-type:application/json");

    // 数据库配置
    include 'Db.php';
    
    // 创建连接
    $conn = new mysqli($Db_Config['dbhost'], $Db_Config['dbuser'], $Db_Config['dbpass'], $Db_Config['dbname']);

    // 订单号
    $order_num = $_GET['order_num'];
    
    // 订单金额
    $order_amount = $_GET['order_amount'];
    
    if(!$order_amount) {
        
        // 订单金额为空
        $result = array(
            'code' => -1,
            'msg' => '订单金额为空'
        );
        exit;
    }
    
    if(!$order_num) {
        
        // 订单号为空
        $result = array(
            'code' => -2,
            'msg' => '订单号为空'
        );
        exit;
    }
    
    // 查询订单状态
    $checkOrder = "SELECT * FROM wxpay_zsm_orders WHERE order_num='$order_num' AND order_amount='$order_amount'";
    $checkOrderSQL = $conn->query($checkOrder);
     
    if ($checkOrderSQL->num_rows > 0) {
        
        // 有结果
        $checkOrderRows = $checkOrderSQL->fetch_assoc();
        if($checkOrderRows['order_status'] == 2) {
            
            // 已支付
            $result = array(
                'code' => 0,
                'msg' => '已完成支付',
                'order_type' => $checkOrderRows['order_type']
            );
        }else {
            
            // 未支付
            $result = array(
                'code' => -2,
                'msg' => '未完成支付'
            );
        }
    } else {
        
        // 无结果
        $result = array(
            'code' => -1,
            'msg' => '无法查询到该订单的状态'
        );
    }
    
    // 断开数据库连接
    $conn->close();
    
    // 返回JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>