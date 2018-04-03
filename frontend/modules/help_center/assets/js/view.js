//<<点赞部分    
$("#thumbs-up").click(function () {
    var isAdd = $(this).attr("data-add");
    if (isAdd === "false") {
        $.post("/help_center/api/post-like", $("#thumbs-up-form").serialize(), function (datar) {
            if (datar['code'] === 200) {
                $("#thumbs-up").attr("data-add", "true");
                $("#thumbs-up").children("i").removeClass("fa-thumbs-o-up");
                $("#thumbs-up").children("i").addClass("fa-thumbs-up");
                //$("#Post-like_count").val(datar['data']['number']);
                $(".thumbs-up>font").text(datar['data']['number']);
                if(datar['data']['like'] === true)
                    $("#thumbs-down").addClass("disabled");
            }
        });
    } else {
        $.post("/help_center/api/cancel-post-like", $("#thumbs-up-form").serialize(), function (datar) {
            if (datar['code'] === 200) {
                $("#thumbs-up").attr("data-add", "false");
                $("#thumbs-up").children("i").removeClass("fa-thumbs-up");
                $("#thumbs-up").children("i").addClass("fa-thumbs-o-up");
                //$("#Post-like_count").val(datar['data']['number']);
                $(".thumbs-up>font").text(datar['data']['number']);
                if(datar['data']['like'] === false)
                    $("#thumbs-down").removeClass("disabled");
            }
        });
    }
    return false;
});
//点赞部分>>
//<<踩部分    
$("#thumbs-down").click(function () {
    var isAdd = $(this).attr("data-add");
    if (isAdd === "false") {
        $.post("/help_center/api/post-unlike", $("#thumbs-down-form").serialize(), function (datar) {
            if (datar['code'] === 200) {
                $("#thumbs-down").attr("data-add", "true");
                $("#thumbs-down").children("i").removeClass("fa-thumbs-o-down");
                $("#thumbs-down").children("i").addClass("fa-thumbs-down");
                //$("#Post-unlike_count").val(datar['data']['number']);
                $(".thumbs-down>font").text(datar['data']['number']);
                if(datar['data']['unlike'] === true)
                    $("#thumbs-up").addClass("disabled");
            }
        });
    } else {
        $.post("/help_center/api/cancel-post-unlike", $("#thumbs-down-form").serialize(), function (datar) {
            if (datar['code'] === 200) {
                $("#thumbs-down").attr("data-add", "false");
                $("#thumbs-down").children("i").removeClass("fa-thumbs-down");
                $("#thumbs-down").children("i").addClass("fa-thumbs-o-down");
                //$("#Post-unlike_count").val(datar['data']['number']);
                $(".thumbs-down>font").text(datar['data']['number']);
                if(datar['data']['unlike'] === false)
                    $("#thumbs-up").removeClass("disabled");
            }
        });
    }
    return false;
});
//踩部分>>

