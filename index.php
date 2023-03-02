<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
	<meta charset="utf-8">
	<script src="./js/jquery.min.js"></script>
	<link rel="stylesheet" href="./css/style.css">
	<title>微信收款商业版支付实现原理Demo（版本号：1.0.1）</title>
</head>

<body>
    
    <?php
        
        // 北京时区
        date_default_timezone_set('PRC');
        
        // 数据库配置
    	include './Db.php';
    	
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 订单号
        $order_num = date('Ymd').time();
        
        // 订单金额
        $order_price = 0.01;
    	
    	// 获取未支付订单列表
        $getOrderList = $db->set_table('syskm_order')->findAll(['order_status' => 1]);
        
        // 遍历订单
        $orderNoExpire = array();
        for ($i = 0; $i < count($getOrderList); $i++) {
            
            // 订单时间
            $order_time = json_decode(json_encode($getOrderList[$i]))->order_time;
            
            // 订单金额
            $order_money = json_decode(json_encode($getOrderList[$i]))->order_money;
            
            // 获取2分钟未支付的订单
            if(countTimes(time(),$order_time) <= 2){
                
                // 如果存在
                $orderNoExpire[] = $order_money;
    
            }
        }
        
        // 判断是否有2分钟未支付的订单
        if(count($orderNoExpire) == 0){
            
            $needPay = $order_price;
        }else{
            
            // 获取2分钟未支付的订单的最小金额-0.01
            $needPay_min = min($orderNoExpire) - 0.01;
            
            // 如果最小金额-0.01小于订单价格则获取2分钟未支付的订单的最大金额+0.01
            if($needPay_min <= $order_price){
                
                $needPay = max($orderNoExpire) + 0.01;
            }else{
                
                // 否则使用最小金额-0.01
                $needPay = $needPay_min;
            }
            
        }
        
        // 先判断未支付订单量是否超过10个
        if(count($orderNoExpire) >= 10){
            
            // 超过10个订单未支付
            echo '<div class="payInfoCard">
                <div class="header">里客云科技</div>
                <div class="moneyCard" style="padding:20px 20px;">
                    当前支付人数过多，请稍等再刷新页面！
                </div>
            </div>';
        }else{
            
            // 创建订单
            creatOrder($order_num,$order_price,$needPay,$db);
        }
        
        // 创建订单
        function creatOrder($order_num,$order_price,$needPay,$db){
            
            // 订单参数
            $createOrder = [
                'order_num' => $order_num,
                'order_time' => time(),
                'order_price' => $order_price,
                'order_money' => $needPay,
            ];
            
            // 创建
            $createOrderResult = $db->set_table('syskm_order')->add($createOrder);
            if($createOrderResult){
                
                // 成功
                echo '<div class="payInfoCard">
            	    <div class="header">里客云科技</div>
            	    <div class="moneyCard">
            	        <div class="text">支付金额</div>
            	        <div class="money"><span class="rmb">¥</span>'.$needPay.'</div>
            	        <!--二维码-->
            	        <img src="./img/skm.jpg" id="zsmQrcode" class="zsmQrcode" />
            	        <!--<button class="payBtn" onclick="createOrder();">确认支付'.$needPay.'元</button>-->
            	        <p class="payWarning">请识别上方收款码输入'.$needPay.'元</p>
            	        <p class="payWarningMini">输入的金额必须要完全一致</p>
            	        <p id="orderExpireTime"></p>
            	        <p id="orderNum" style="display:none;">'.$order_num.'</p>
            	        <p id="needPay" style="display:none;">'.$needPay.'</p>
            	    </div>
            	</div>
            	
            	<!--提示-->
            	<p class="payTips">我们通过机器人监测本次支付<br/>支付后会立刻显示支付结果<br/>支付后没显示支付结果请联系人工处理<br/>人工微信号：sansure2016</p>';
            }else{
                
                // 失败
                echo '<div class="payInfoCard">
            	    <div class="header">里客云科技</div>
            	    <div class="moneyCard" style="padding:20px 20px;">
            	        创建订单失败！
            	    </div>
            	</div>';
            }
        }
        
        
        // 计算时间戳的差值
        function countTimes($begin,$end){
            
            $begintimes = $begin;
            $endtimes = $end;
            $timediff = abs($endtimes - $begintimes);
            $days = intval($timediff / 86400);
            $remain = $timediff % 86400;
            $hours = intval($remain / 3600);
            $remain = $remain % 3600;
            $mins = intval($remain / 60);
            $secs = $remain % 60;
            return $mins;
        }
        
    ?>
    
    <script>
    
        // 每2秒获取一次支付结果
        var checkPayInterval = setInterval('checkPay()',2000);
        
        // 获取支付结果
        function checkPay(){
            
            // 获取订单号和支付金额
            var orderNum = $("#orderNum").text();
            var needPay = $("#needPay").text();
            
            $.ajax({
                type: "GET",
                url: "./checkPay.php?order_num="+orderNum+"&order_money="+needPay,
                success: function(res){
  
                    // 判断支付结果
                    if(res.code == 200){
                        
                        // 停止计时
                        clearInterval(orderExpireTime);
                
                        // 停止轮询支付结果
                        clearInterval(checkPayInterval);
                        
                        $("#zsmQrcode").prop("src","./img/success.png");
                        $('#orderExpireTime').text('支付成功');
                        console.log('订单已支付');
                        console.log('停止计时');
                        console.log('停止轮询支付结果');
  
                    }else{
                        
                        console.log(res.msg);
                    }
                }
            });
        }
        
        // 倒计时
        // 单位：秒
        var t = 120; // 120秒=2分钟
        var orderExpireTime = setInterval(function(){
            
            t --;
            var s = parseInt(t /3600 );
            var f = parseInt((t % 3600) / 60);
            var m = t % 60;
            
            // 渲染到页面
            $('#orderExpireTime').html(format(f)+':'+format(m));
     
            if (t == 0){
                
                // 停止计时
                clearInterval(orderExpireTime);
                
                // 停止轮询支付结果
                clearInterval(checkPayInterval);
                
                // 显示订单过期
                $("#zsmQrcode").prop("src","./img/expire.png");
                $('#orderExpireTime').text('订单已过期，请刷新页面！');
                console.log('订单已过期');
                console.log('停止计时');
                console.log('停止轮询支付结果');
            }
            
        }, 1000);

        // 格式化时间
        function format(num){
            if (num < 10) {
                return '0' + num;
            }
            return '' + num;
        }
        
    </script>
</body>

</html>
