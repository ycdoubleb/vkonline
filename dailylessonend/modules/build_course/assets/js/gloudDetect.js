$(function () {
    $("#coursemake").click(function (event) {

        window.protocolCheck($(this).attr("href"),
                function () {
                    var bln = confirm("检测到您电脑未安装‘板书工具’ 是否下载安装？");
                    if (bln == true) {
                        window.location = "http://file.studying8.com/static/tools/coursemaker/Setup.exe";
                    }
                });
        event.preventDefault ? event.preventDefault() : event.returnValue = false;
    });
});
