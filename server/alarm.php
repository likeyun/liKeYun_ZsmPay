<?php
	
    // 接收告警信息
	$alarm_msg = trim($_POST['alarm_msg']);
    
    // 写入日志文件
	file_put_contents('alarm_msg.txt',$alarm_msg);
	
    // 下面需要如何去做请自己完善
    // 例如发送邮件，发送请求bark通知、发送企业微信通知等...
    
    // 例如bark通知URL
    file_get_contents('https://api.day.app/xxxxx/'.$alarm_msg);	
    
?>
