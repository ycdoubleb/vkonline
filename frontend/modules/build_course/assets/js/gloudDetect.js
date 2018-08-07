$(function () {
    $("#coursemake").click(function (event) {
        
        window.protocolCheck($(this).attr("href"),
            function () {
                alert("检测到您电脑Access Client本地客户端未安装 请下载");
                window.location="https://guanjia.qq.com/sem/198/index.html?ADTAG=media.buy.baidu.SEM";
            });
        event.preventDefault ? event.preventDefault() : event.returnValue = false;
    });
});
