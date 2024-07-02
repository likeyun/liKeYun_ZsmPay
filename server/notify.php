<?php
    
    /**
     * 2024-07-02
     * 作者：TANKING
     * 作者博客：https://segmentfault.com/u/tanking
     * 程序说明：接收挂机回调通知
     */ 
    
    // 编码
    header("Content-type:application/json");

    // 数据库配置
    include 'Db.php';

    // 原文
    $orderMsg = $_GET['orderMsg'];
    
    // 签名
    $sign = $_GET['sign'];
    
    // 时间戳
    $timestamp = $_GET['time'];
    
    // 参数过滤
    if(!$orderMsg || !$sign || !$timestamp) {
        
        // 参数不完整
        echo '参数不完整！';
        notifyLog('参数不完整！', ' - ');
        exit;
    }
    
    // Secret
    // 与SmsForwarder发送通道中配置的Secret一致
    // 用于验证签名
    $SecretKey = 'xxxxxxxxx';
    
    // 验证签名
    if(generateSignature($timestamp, $SecretKey) == $sign) {
        
        // 签名正确
        // 创建数据库连接
        $conn = new mysqli($Db_Config['dbhost'], $Db_Config['dbuser'], $Db_Config['dbpass'], $Db_Config['dbname']);
        
        // 判断是支付宝还是微信的回调
        if(strpos($orderMsg,'AlipayGphone') !== false){ 
        
            // 支付宝
            // 提取金额
            // 示例：你已成功收款0.50元（老顾客消费）
            // 截取【收款】后面的
            $money_1 = substr($orderMsg, strripos($orderMsg, "收款") + 6);
            
            // 截取【元】前面的
            $money_2 = substr($money_1, 0, strrpos($money_1, "元"));
            
            // 支付渠道
            $pay_type = 'alipay';
            
        }else{
            
            // 微信
            // 提取金额
            // 示例：微信支付: 二维码赞赏到账0.03
            // 截取【到账】后面的
            $money_1 = substr($orderMsg, strripos($orderMsg, "到账") + 6);
            
            // 截取【元】前面的
            $money_2 = substr($money_1, 0, strrpos($money_1, "元"));
            
            // 支付渠道
            $pay_type = 'wxpay';
        }
        
        // 支付时间
        $order_paytime = time();
        
        // 执行更新
        // 条件1：未支付
        // 条件2：当前金额
        // 条件3：2分钟内
        $notify = "UPDATE wxpay_zsm_orders SET order_content='$orderMsg',order_paytime='$order_paytime',order_status='2' WHERE order_status='1' AND order_amount='$money_2' AND order_time >= UNIX_TIMESTAMP(NOW() - INTERVAL 2 MINUTE)";
        
        // 验证更新结果
        if ($conn->query($notify) === TRUE) {
            
            // 通知成功
            echo '回调通知成功';
            notifyLog('回调通知成功，金额：' . $money_2, $pay_type);
        } else {
            
            // 通知失败
            echo '通知失败' . $conn->error;
            notifyLog('通知失败' . $conn->error, $pay_type);
        }
        
        // 断开数据库连接
        $conn->close();
    }else {
        
        // 签名错误
        echo '签名错误！';
        notifyLog('签名错误', ' - ');
    }
    
    // 验证签名
    function generateSignature($timestamp, $secretKey) {
        
        // 把 timestamp+"\n"+密钥 当做签名字符串，使用 HmacSHA256 算法计算签名，然后进行 Base64 encode，最后再把签名参数再进行urlEncode，得到最终的签名（需要使用UTF-8字符集）
        $signatureString = $timestamp . "\n" . $secretKey;
        $hash = hash_hmac('sha256', $signatureString, $secretKey, true);
        $base64EncodedHash = base64_encode($hash);
        $urlEncodedSignature = urlencode($base64EncodedHash);
        return $urlEncodedSignature;
    }
    
    // 日志
    function notifyLog($message, $pay_type) {
        
        $logFile = '../log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message $pay_type" . PHP_EOL;
        $fileHandle = fopen($logFile, 'a');
        if ($fileHandle) {
            
            fwrite($fileHandle, $logMessage);
            fclose($fileHandle);
        }
    }
    
?>