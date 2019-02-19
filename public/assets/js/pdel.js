//全选/全不选
$("#CheckAll").bind("click", function () {
    $("input[name='Check[]']").prop("checked", this.checked);
});


//批量删除
$("#Delete").click(function () {
    if (confirm('确定要删除所选吗?')) {
        var checks = $("input[name='Check[]']:checked");
        if (checks.length == 0) {
            alert('未选中任何项！');
            return false;
        }
        //将获取的值存入数组
        var checkData = new Array();
        checks.each(function () {
            checkData.push($(this).val());
        });
        $.get(
            "http://localhost/manage/teacher/del",
            {id: checkData.toString()},
            function (result) {
                if (result = true) {
                    window.location.reload();
                } else {
                    alert('删除失败');
                }
            });
    }
});