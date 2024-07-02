<?php
    
    /**
     * 2024-07-02
     * 作者：TANKING
     * 作者博客：https://segmentfault.com/u/tanking
     * 程序说明：创建订单
     */ 
    
    // 编码
    header("Content-type:application/json");

    // 数据库配置
    include 'Db.php';
    
    // 创建连接
    $conn = new mysqli($Db_Config['dbhost'], $Db_Config['dbuser'], $Db_Config['dbpass'], $Db_Config['dbname']);

    // 初始订单价格
    $order_price = $_GET['order_amount'];
    $order_price_ = $_GET['order_amount'];
    
    // 支付渠道
    $order_type = $_GET['order_type'];
    
    // 订单标题
    $order_title = $_GET['order_title'];
    
    if(!$order_price) {
        
        // 订单金额为空
        $result = array(
            'code' => -3,
            'msg' => '订单金额为空'
        );
        exit;
    }
    
    if(!$order_type) {
        
        // 支付渠道为空
        $result = array(
            'code' => -4,
            'msg' => '支付渠道为空'
        );
        exit;
    }
    
    if(!$order_title) {
        
        // 订单标题为空
        $result = array(
            'code' => -5,
            'msg' => '订单标题为空'
        );
        exit;
    }

    // 创建数组并将初始值存入
    $order_amount_array = array($order_price);
    
    // 基于订单价格生成20个金额
    // 循环19次，每次将 $order_price 加0.01，并存入数组
    for ($i = 0; $i < 19; $i++) {
        $order_price += 0.01;
        $order_amount_array[] = $order_price;
    }
    
    // 建立一个数组用于存储未被使用的金额
    $order_amount_allow = array();
    
    // 建立一个数组用于存储已被使用的金额
    $order_amount_forbidden = array();
    
    // 循环这个数组
    foreach ($order_amount_array as $order_amount) {
        
        // 查询数据库中是否存在当前金额2分钟内的订单
        $sql = "SELECT * FROM wxpay_zsm_orders WHERE order_amount = '$order_amount' AND order_status = '1' AND order_time >= UNIX_TIMESTAMP(NOW() - INTERVAL 2 MINUTE)";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            
            // 将这个金额加入到这个数组中（已被使用）
            $order_amount_forbidden[] = $order_amount;
        } else {
            
            // 将这个金额加入到这个数组中（未被使用）
            $order_amount_allow[] = $order_amount;
        }
    }
    
    // 如果未被使用的金额用完了
    if(count($order_amount_allow) == 0) {
        
        // 还有很多未支付的订单
        $result = array(
            'code' => -1,
            'msg' => '太多人了，稍后再试试吧...'
        );
        exit;
    }
    
    // 在未被使用的金额中取第一个用于创建订单
    // 订单参数
    $order_num = date('Ymd').time();
    $order_time = time();
    $order_amount = $order_amount_allow[0];
    
    // 自定义参数
    // 例如保存用户的电子邮箱、备注、手机号等信息到这笔订单
    // 该字段为varchar(255)类型
    $order_extra = '';
    
    // 创建一个订单
    $sql = "INSERT INTO wxpay_zsm_orders (order_num, order_title, order_time, order_amount, order_price, order_type, order_extra) 
    VALUES ('$order_num', '$order_title', '$order_time', '$order_amount', '$order_price_', '$order_type', '$order_extra')";
    
    if ($conn->query($sql) === TRUE) {
        
        // 创建订单成功
        $result = array(
            'code' => 0,
            'msg' => '创建订单成功',
            'order_num' => $order_num,
            'order_amount' => number_format($order_amount, 2),
            'order_title' => $order_title,
            'order_time' => date('Y-m-d H:i:s', $order_time),
            'order_qrcode' => 'img/qrcode.png',
            'order_type' => $order_type
        );
    } else {
        
        // 创建订单失败
        $result = array(
            'code' => -2,
            'msg' => '创建订单失败',
            'error' => $sql . $conn->error
        );
    }
    
    // 断开数据库连接
    $conn->close();
    
    // 返回JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
?>