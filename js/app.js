const { createApp, ref, computed } = Vue;

createApp({
    setup() {
        const orderQRCode = ref(''); // 二维码
        const orderTitle = ref('支付测试'); // 订单标题
        const orderNum = ref(''); // 订单号
        const orderTime = ref(''); // 订单时间
        const orderPrice = ref('0.01'); // 订单价格
        const orderAmount = ref(''); // 需要支付的金额
        const orderAmountCheckPayStatus = ref(''); // 这个用来查询订单状态的金额
        const paybtnsShow = ref(true); // 发起支付的按钮显示状态
        const showPayTips = ref(false); // 支付提醒
        const showQRCode = ref(false); // 是否展示二维码
        const showCountdown = ref(false); // 是否展示倒计时
        const qrcodeStatusText = ref(''); // 二维码状态文案
        const isQRCodeExpired = ref(false); // 二维码是否过期
        const countdown = ref(0); // 倒计时分钟
        const showLoadding = ref(false); // 是否显示加载动画
        const loaderColor = ref(''); // 加载动画主题色
        const orderEnd = ref(''); // 订单结束文案
        const payApp = ref(''); // 支付平台
        
        // 格式化倒计时
        const formattedCountdown = computed(() => {
            const minutes = Math.floor(countdown.value / 60).toString().padStart(2, '0');
            const seconds = (countdown.value % 60).toString().padStart(2, '0');
            return `${minutes}:${seconds}`;
        });

        // 创建订单
        function createOrder(type) {
            axios.get(`server/createOrder.php`, {
                params: {
                    order_price: orderPrice.value,
                    order_amount: orderPrice.value,
                    order_type: type,
                    order_title: orderTitle.value
                }
            })
            .then(response => {
                const data = response.data;
                if (data.code === 0) {
                    
                    // 显示加载
                    showLoadding.value = true;
                    paybtnsShow.value = false;
                    
                    // 主题色
                    loaderColor.value = 'border-top: 4px solid #48c063;'
                    payApp.value = '微信';
                    
                    // 在这里加入一个延时
                    // 1.3秒后才会执行下面的
                    setTimeout(() => {
                        showPayTips.value = true;
                        showQRCode.value = true;
                        showCountdown.value = true;
                        orderQRCode.value = data.order_qrcode;
                        orderNum.value = data.order_num;
                        orderTime.value = data.order_time;
                        orderAmount.value = `￥${data.order_amount}`;
                        orderAmountCheckPayStatus.value = data.order_amount;
                        countdown.value = 120; // 订单有效期120秒（2分钟）
                        startCountdown();
                        showLoadding.value = false;
                    }, 1300);
                    
                    // 开始轮询订单状态
                    startPolling();
                }else {
                    
                    // 非0状态码
                    orderEnd.value = data.msg;
                }
            })
            .catch(error => {
                console.error('服务器响应失败:', error);
                orderEnd.value = '服务器发生错误...';
            });
        }
        
        // 订单计时器
        let countdownTimer = null;
        
        // 开始计时
        function startCountdown() {
            if (countdown.value > 0) {
                countdownTimer = setTimeout(() => {
                    countdown.value--;
                    startCountdown();
                }, 1000);
            } else {
                qrcodeStatusText.value = '订单已过期';
                isQRCodeExpired.value = true;
                showCountdown.value = false;
                orderEnd.value = '订单已过期，请重新下单！';
            }
        }
        
        // 停止计时
        function stopCountdown() {
            if (countdownTimer !== null) {
                clearTimeout(countdownTimer);
                countdownTimer = null;
            }
        }
        
        // 轮询订单状态
        function startPolling() {
            pollingInterval = setInterval(() => {
                axios.get(`server/checkOrder.php`, {
                    params: {
                        order_amount: orderAmountCheckPayStatus.value,
                        order_num: orderNum.value
                    }
                })
                .then(response => {
                    const data = response.data;
                    if (data.code === 0) {
                        
                        // 已支付
                        orderQRCode.value = 'img/' + response.data.order_type + '-logo.png';
                        showCountdown.value = false;
                        orderEnd.value = response.data.msg;
                        stopPolling(); // 停止轮询
                        stopCountdown() // 停止计时
                    }
                })
                .catch(error => {
                    console.error('检查订单状态失败:', error);
                    orderEnd.value = '检查订单状态失败...';
                });
            }, 1500);
        }
        
        // 停止轮询
        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        return {
            orderQRCode,
            orderTitle,
            orderNum,
            orderTime,
            orderPrice,
            orderAmount,
            orderAmountCheckPayStatus,
            showPayTips,
            showQRCode,
            showCountdown,
            paybtnsShow,
            qrcodeStatusText,
            isQRCodeExpired,
            formattedCountdown,
            createOrder,
            showLoadding,
            loaderColor,
            orderEnd,
            payApp
        };
    },
    
    // HTML模板
    template: `
    <div class="order">
        <div class="header">里客云科技 - 订单单页</div>
        
        <div class="order-info">
            <p class="info" v-if="orderTitle">
                <span class="info-title">订单标题</span>
                <span class="info-data">{{ orderTitle }}</span>
            </p>
            <p class="info" v-if="orderNum">
                <span class="info-title">订单号</span>
                <span class="info-data">{{ orderNum }}</span>
            </p>
            <p class="info" v-if="orderTime">
                <span class="info-title">订单时间</span>
                <span class="info-data">{{ orderTime }}</span>
            </p>
            <p class="info">
                <span class="info-title">订单金额</span>
                <span class="info-data">￥{{ orderPrice }}</span>
            </p>
        </div>
        
        <div class="paybtns" v-if="paybtnsShow">
            <button class="paybtn wxpay" @click="createOrder('wxpay')">微信支付</button>
        </div>
        
        <p class="order_amount">
            <span class="you_need_pay">{{ orderAmount }}</span>
        </p>
        
        <p class="pay_tips" v-show="showPayTips">请使用{{ payApp }}扫码支付<span class="you_need_pay_">{{ orderAmount }}</span>元</p>
        
        <p class="clickOtherAmountTips" v-show="showPayTips">扫码后戳 <span>其它金额</span> 输入上方金额</p>
        
        <div class="loader" v-show="showLoadding" :style="loaderColor"></div>
        <p class="loadding_text" v-show="showLoadding">正在创建订单...</p>
        
        <div class="qrcode" v-show="showQRCode">
            <div class="qrcode_status_text" v-if="qrcodeStatusText">{{ qrcodeStatusText }}</div>
            <img :src="orderQRCode" class="qrcode_img" :class="{ qrcode_blur: isQRCodeExpired }" />
        </div>
        
        <p class="countdown" v-show="showCountdown">请在 <span id="countdown_time">{{ formattedCountdown }}</span> 内完成支付</p>
        <p class="countdown" v-if="orderEnd"> {{ orderEnd }} </p>
    </div>
    
    <div class="bottom_links">
        <div class="bottom_links_inner">
            <a href="#" class="link">查询订单</a>
            <a href="#" class="link">联系客服</a>
        </div>
    </div>`
}).mount('#app');