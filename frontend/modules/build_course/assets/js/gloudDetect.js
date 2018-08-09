$(function () {
    $("#coursemake").click(function (event) {

        window.protocolCheck($(this).attr("href"),
                function () {
                    var bln = confirm("检测到您电脑未安装‘板书工具’ 是否下载安装？");
                    if (bln == true) {
                        window.location = "https://guanjia.qq.com/sem/198/index.html?ADTAG=media.buy.baidu.SEM";
                    }
                });
        event.preventDefault ? event.preventDefault() : event.returnValue = false;
    });
});
