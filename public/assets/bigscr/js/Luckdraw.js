var xinm = new Array();
	xinm[0] = "images/1.jpg"
	xinm[1] = "images/2.jpg"
	xinm[2] = "images/3.jpg"
	xinm[3] = "images/4.jpg"
	xinm[4] = "images/5.jpg"
	xinm[5] = "images/6.jpg"
	xinm[6] = "images/1.jpg"
	xinm[7] = "images/2.jpg"
	xinm[8] = "images/3.jpg"
	xinm[9] = "images/4.jpg"
	xinm[10] = "images/5.jpg"
	xinm[11] = "images/6.jpg"

	var phone = new Array();
	phone[0] = "王尼玛"
	phone[1] = "张全蛋"
	phone[2] = "纸巾老撕"
	phone[3] = "王铁柱"
	phone[4] = "田二妞"
	phone[5] = "唐马儒"
	phone[6] = "张三"
	phone[7] = "李四"
	phone[8] = "王二麻子"
	phone[9] = "咯咯咯"
	phone[10] = "一二三"
	phone[11] = "四五六"

var nametxt = $('.slot');
var phonetxt = $('.name');
var pcount = xinm.length-1;//参加人数
var runing = true;
var trigger = true;
var inUser = (Math.floor(Math.random() * 10000)) % 1 + 1;
var num = 0;
var Lotterynumber =1; //设置单次抽奖人数

$(function () {
	nametxt.css('background-image','url('+xinm[0]+')');
	phonetxt.html(phone[0]);
});

// 开始停止
function start() {

	if (runing) {

		if ( pcount <= Lotterynumber ) {
			// alert("抽奖人数不足5人");
		}else{
			runing = false;
			$('#start').text('停止抽选人员');
			startNum()
		}

	} else {
		$('#start').text('正在抽取中('+ Lotterynumber+')');
		zd();
	}
	
}

// 开始抽奖

function startLuck() {
	runing = false;
	$('#btntxt').removeClass('start').addClass('stop');
	startNum()
}

// 循环参加名单
function startNum() {
	num = Math.floor(Math.random() * pcount);
	nametxt.css('background-image','url('+xinm[num]+')');
	phonetxt.html(phone[num]);
	t = setTimeout(startNum, 100);
}

// 停止跳动
function stop() {
	pcount = xinm.length-1;
	clearInterval(t);
	t = 0;
}

// 打印中奖人

function zd() {
	if (trigger) {

		trigger = false;
		var i = 0;

		if ( pcount >= Lotterynumber ) {
			stopTime = window.setInterval(function () {
				if (runing) {
					runing = false;
					$('#btntxt').removeClass('start').addClass('stop');
					startNum();
				} else {
					runing = true;
					$('#btntxt').removeClass('stop').addClass('start');
					stop();

					i++;
					Lotterynumber--;

					$('#start').text('正在抽取中('+ Lotterynumber+')');

					if ( i == 1 ) {
						console.log("抽奖结束");
						window.clearInterval(stopTime);
						$('#start').text("重新选择人员");
						Lotterynumber = 1;
						trigger = true;
					};

					if ( Lotterynumber == inUser) {
						// 指定中奖人
						$('.luck-user-list').prepend("<li><div class='portrait' style='background-image:url("+xinm[num]+")'></div><div class='luckuserName'>"+phone[num]+"</div></li>");
						$('.modality-list ul').append("<li><div class='luck-img' style='background-image:url("+xinm[num]+")'></div><p>"+phone[num]+"</p></li>");
					}else{
						//打印中奖者名单
						$('.luck-user-list').prepend("<li><div class='portrait' style='background-image:url("+xinm[num]+")'></div><div class='luckuserName'>"+phone[num]+"</div></li>");
						$('.modality-list ul').append("<li><div class='luck-img' style='background-image:url("+xinm[num]+")'></div><p>"+phone[num]+"</p></li>");
						//将已中奖者从数组中"删除",防止二次中奖
						xinm.splice($.inArray(xinm[num], xinm), 1);
						phone.splice($.inArray(phone[num], phone), 1);
					};
				}
			},1000);
		};
	}
}

