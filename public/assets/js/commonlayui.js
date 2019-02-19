function layuu(obj) {
	layui.use(['form', 'layer'], function () {
		var form = layui.form;
		// 键盘组合键alt+s，开启自动填充数据
		$(document).keydown(function (event) {
			if (event.keyCode == 83 && event.altKey) {
				if (obj == 'qxuseradd') {
					form.val(obj, {
						"name": "少侠2018"
						, "tel": "13895729550"
					});
				}
				if (obj == 'teacheradd') {
					form.val(obj, {
						"name": "姜程"
						, "school": "哈尔滨理工大学"
						, "age": "35"
						, "teachage": "5"
						, "tel": "13895729550"
					});
				}
				if (obj == 'secondfbadd') {
					form.val(obj, {
						"name": "姜程"
						, "id_num": "230624198401240017"
						, "content": "升级测试"
					});
				}
				if (obj == 'teachergroupadd') {
					form.val(obj, {
						"tgroupname": "2018一年级第一次"
						, "tnum": "8"
						, "fcengnum": "5"
					});
				}
			}
		});
		
		form.on('submit(formDemo)', function (data) {
			var fd = new FormData(data.form);
			$.ajax({
				type: "post",
				url: data.elem.value,
				data: fd,//表单数据
				dataType: 'json',
				cache: false,         // 不缓存
				processData: false,  // 不处理数据
				contentType: false,  //
				async: false, //同步处理
				error: function (res) {
					console.log(res);
				},
				success: function (res) {
					// console.log(res.msg);
					if (res.code == 100) {
						if (obj == 'teacheradd') {
							self.location = document.referrer;
						}
						
						if (obj == 'bandsbt') {
							layer.alert(res.msg, function () {
								var index = parent.layer.getFrameIndex(window.name);
								parent.layer.close(index);
							});
						} else {
							layer.close(layer.index);
							window.parent.location.reload();
						}
					}
					if (res.code == 101) {
						layer.msg(res.msg);
					}
				}
			});
			return false;//阻止表单跳转
		});
	});
}