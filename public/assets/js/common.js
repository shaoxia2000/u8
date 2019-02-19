//全选/全不选
$("#CheckAll").bind("click", function () {
    $("input[name='Check[]']").prop("checked", this.checked);
});

/**
 * 通用化删除操作
 * @param obj
 * @param input需要加 del_url
 * 例：<input type="button" class="btn btn-danger" id="Delete" name="Delete" value="批量删除" onclick="app_del(this)" del_url="{:url('teacher/del')}">
 */
function app_del(obj) {
    // 获取模板当中的url地址
    url = $(obj).attr('del_url');
    layer.confirm('确认要删除吗？', function (index) {
        var checks = $("input[name='Check[]']:checked");
        if (checks.length == 0) {
            layer.alert('未选中任何项！');
            return false;
        }
        //将获取的值存入数组
        var checkData = new Array();
        checks.each(function () {
            checkData.push($(this).val());
        });
        $.get(
            url,
            {id: checkData.toString()},
            function (result) {
                if (result = true) {
                    window.location.reload();
                } else {
                    layer.alert('删除失败');
                }
            });
    });
}


/**
 * 学生表单独删除操作(由于进行了分表,所以需要获取schid)
 * @param obj
 * @param input需要加 del_url
 * 例：<input type="button" class="btn btn-danger" id="Delete" name="Delete" value="批量删除" onclick="app_del(this)" del_url="{:url('teacher/del')}">
 */
function students_del(obj) {
    // 获取模板当中的url地址
    url = $(obj).attr('del_url');
    schid = $(obj).attr('del_schid');
    layer.confirm('确认所选中的学生将不参与分班吗？', function (index) {
        var checks = $("input[name='Check[]']:checked");
        if (checks.length == 0) {
            layer.alert('未选中任何项！');
            return false;
        }
        //将获取的值存入数组
        var checkData = new Array();
        checks.each(function () {
            checkData.push($(this).val());
        });
        $.get(
            url,
            {id: checkData.toString(), schid: schid},
            function (result) {
                if (result = true) {
                    window.location.reload();
                } else {
                    layer.alert('操作失败');
                }
            });
    });
}


/**
 * 通用化提交操作
 * @param obj
 * @param input需要加 add_form add_url
 * 例：<input type="button" add_form="form1" add_url="{:url('teacher/add')}" class="btn btn-primary" value="提交" onclick="sub(this)">
 */
function sub(obj) {
    layer.load(2, {
        content: '正在检查数据，请耐心等待!', offset: ['50%', '20%'], shade: [0.1, '#3595CC'], area: ['100%', '30%'], success: function (layero) {
            layero.find('.layui-layer-content').css({"width": "200px", "padding-left": "30px", "padding-top": "7px"});
        }
    });
    var add_form = $(obj).attr('add_form');
    var add_url = $(obj).attr('add_url');
    var form = document.getElementById(add_form);
    var fd = new FormData(form);
    $.ajax({
        type: "post",
        url: add_url,
        data: fd,//表单数据
        dataType: 'json',
        processData: false,  // 不处理数据
        contentType: false,
        // cache: false,         // 不缓存
        // async: false, //同步处理
        success: function (res) {
            console.log(res.msg);
            if (res.code == 100) {
                layer.closeAll("loading");
                layer.msg(res.msg);
                layer.close(layer.index);
                window.parent.location.reload();
            }
            if (res.code == 101) {
                layer.alert('<font color="#8b0000">以下身份信息找不到对应数据,数据提交失败!</font></br>' + res.msg, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                });
            }

            if (res.code == 108) {
                layer.closeAll("loading");
                $("#backvalue").html("<font color=\"#8b0000\">数据身份证信息重复,数据提交失败!</font></br><button class=\"btn\" data-clipboard-text=\"" + res.msg + "\">复制出错信息</button><br><br>");
            }

            if (res.code == 103 || res.code == 104 || res.code == 105) {
                layer.closeAll("loading");
                layer.msg(res.msg);
            }
        }
    });
    return false;

}


/**
 * 添加教师组提交操作
 * @param obj
 * @param input需要加 add_form add_url
 * 例：<input type="button" add_form="form1" add_url="{:url('teacher/add')}" class="btn btn-primary" value="提交" onclick="sub(this)">
 */
function subtgroup(obj) {
    var add_form = $(obj).attr('add_form');
    var add_url = $(obj).attr('add_url');
    var add_type = $(obj).attr('add_type');
    var form = document.getElementById(add_form);
    var leftv = $('#slider-range').slider("values", 0).toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    var rightv = $('#slider-range').slider("values", 1).toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    var zv = leftv + ',' + rightv;
    if (add_type != 1) {
        var pzv = (parseInt(rightv) - parseInt(leftv)) / 2;
        if (leftv == rightv) {
            layer.msg('分层数量不能相等！');
            return false;
        }
        if (pzv < 22) {
            layer.msg('单组分层人数不能少于22人！');
            return false;
        }
    }
    var fd = new FormData(form);
    fd.append('fceng', zv);
    $.ajax({
        type: "post",
        url: add_url,
        data: fd,//表单数据
        dataType: 'json',
        processData: false,  // 不处理数据
        contentType: false,
        success: function (res) {
            console.log(res);
            if (res.code == 100) {
                layer.msg(res.msg);
                layer.close(layer.index);
                window.parent.location.reload();
            }
            if (res.code == 101) {
                layer.msg(res.msg);
            }
        }
    });
    return false;

}


function bandsbt(title, url) {
    var index = layer.open({
        type: 2,
        btn: ['关闭'],
        area: ['50%', '50%'],
        title: title,
        content: url
    });
}




