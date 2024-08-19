<?php
    
    /**
     * 2024-08-19
     * 作者：TANKING
     * 作者博客：https://segmentfault.com/u/tanking
     * 程序说明：电脑版监控回调
     */ 
    
    // 编码
    header("Content-type:application/json");
    
    // 数据库配置
    include 'Db.php';
    
    // 日志记录函数
    function log_notify_message($message) {
        file_put_contents('notify_pc_msg.txt', $message, FILE_APPEND);
    }
    
    // 收款金额
    $amount = trim($_POST['amount']);
    
    // 微信昵称
    $sender = trim($_POST['sender']);
    
    // 到账时间
    $timestamp = trim($_POST['timestamp']);
    
    // 监听到的原文
    $orderMsg = $amount . $sender . $timestamp;
    
    // 签名
    $signature = trim($_POST['signature']);
    
    // 签名安全校验Screct
    // 需要与软件设置的一致
    $Screct = '9c961ceca8c35e7208f031283609ffab';
    
    // 参数过滤
    if(!$amount || !$signature || !$timestamp) {
        
        // 参数不完整
        $notify_pc_msg = '参数不完整' . $timestamp . "\n";
        log_notify_message($notify_pc_msg);
        exit;
    }
    
    // 验证签名
    // 签名算法：MD5（$Screct+$amount+$timestamp（接收到的XXXX-XX-XX XX:XX:XX要转换为时间戳））
    if ($signature == md5($Screct . $amount . strtotime($timestamp))) {
        
        try {
            
            // 创建数据库连接
            $conn = new PDO("mysql:host={$Db_Config['dbhost']};dbname={$Db_Config['dbname']}", $Db_Config['dbuser'], $Db_Config['dbpass']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 执行更新
            // 条件1：未支付
            // 条件2：当前金额
            // 条件3：2分钟内
            $sql = "UPDATE wxpay_zsm_orders 
                    SET order_content = :orderMsg, order_paytime = :timestamp, order_status = '2' 
                    WHERE order_status = '1' 
                    AND order_amount = :amount 
                    AND order_time >= UNIX_TIMESTAMP(NOW() - INTERVAL 2 MINUTE)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':orderMsg', $orderMsg);
            $stmt->bindParam(':timestamp', $timestamp);
            $stmt->bindParam(':amount', $amount);
            
            // 验证更新结果
            if ($stmt->execute()) {
                
                // 通知成功
                $notify_pc_msg = '微信昵称：' . $sender . '，收款金额：' . $amount . '，收款时间：' . $timestamp . "\n";
                log_notify_message($notify_pc_msg);
            } else {
                
                // 通知失败
                $notify_pc_msg = '通知失败' . $timestamp . "\n";
                log_notify_message($notify_pc_msg);
            }
            
        } catch (PDOException $e) {
            
            // 通知失败
            $notify_pc_msg = '数据库连接失败：' . $e->getMessage() . "\n";
            log_notify_message($notify_pc_msg);
        }
        
    } else {
        
        // 签名错误
        $notify_pc_msg = '签名错误，' . $timestamp . "\n";
        log_notify_message($notify_pc_msg);
    }

?>
