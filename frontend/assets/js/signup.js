//jQuery time
var current_fs, next_fs, previous_fs; //fieldsets
var left, opacity, scale; //fieldset properties which we will animate
var animating; //flag to prevent quick multi-click glitches

//邀请码页下一步
$(".next").click(function () {
    var brand = $("#user-customer_id").val();
    if (brand === "") {
        alert("请输入您的邀请码！");
        return false;
    }
    if (animating)
        return false;
    animating = true;

    current_fs = $(this).parent();
    next_fs = $(this).parent().next();

    //activate next step on progressbar using the index of next_fs
    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

    //show the next fieldset
    next_fs.show();
    next();
});

//用户账号密码页下一步
$("#user-next").click(function () {
    var user = $("#user-username").val();
    if (user === "") {
        alert("请输入用户名！");
        return false;
    }
    if(/[\u4E00-\u9FA5]/g.test(user))
    {
      alert ("用户名不能包含中文！");
      return false;
    }
    var pass = $("#user-password_hash").val();
    var pass1 = $("#user-password2").val();
    if (pass === "") {
        alert("请输入密码！");
        return false;
    }
    if (pass1 !== pass) {
        alert("两次密码不一致！");
        return false;
    }

    if (animating)
        return false;
    animating = true;

    current_fs = $(this).parent();
    next_fs = $(this).parent().next();

    //activate next step on progressbar using the index of next_fs
    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

    //show the next fieldset
    next_fs.show();
    next();
});

//用户信息页下一步
$("#info-next").click(function () {
    var user = $("#user-nickname").val();
    if (user === "") {
        alert("请输入真实姓名！");
        return false;
    }
    var phone = $("#user-phone").val(),
            phoneReg = /^1[3|4|5|6|7|8][0-9]{9}$/,
            flag = phoneReg.test(phone);
    if (phone === "") {
        alert("请输入手机号！");
        return false;
    } else if (!flag) {
        alert("电话号码填写不正确！");
        return false;
    }

    if (animating)
        return false;
    animating = true;

    current_fs = $(this).parent();
    next_fs = $(this).parent().next();

    //activate next step on progressbar using the index of next_fs
    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

    //show the next fieldset
    next_fs.show();
    next();
});

//下一步公共方法
var next = function () {
    //hide the current fieldset with style
    current_fs.animate({opacity: 0}, {
        step: function (now, mx) {
            //as the opacity of current_fs reduces to 0 - stored in "now"
            //1. scale current_fs down to 80%
            scale = 1 - (1 - now) * 0.2;
            //2. bring next_fs from the right(50%)
            left = (now * 50) + "%";
            //3. increase opacity of next_fs to 1 as it moves in
            opacity = 1 - now;
            current_fs.css({'transform': 'scale(' + scale + ')'});
            next_fs.css({'left': left, 'opacity': opacity});
        },
        duration: 800,
        complete: function () {
            current_fs.hide();
            animating = false;
        },
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
    });
};

//上一步
$(".previous").click(function () {
    if (animating)
        return false;
    animating = true;

    current_fs = $(this).parent();
    previous_fs = $(this).parent().prev();

    //de-activate current step on progressbar
    $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

    //show the previous fieldset
    previous_fs.show();
    //hide the current fieldset with style
    current_fs.animate({opacity: 0}, {
        step: function (now, mx) {
            //as the opacity of current_fs reduces to 0 - stored in "now"
            //1. scale previous_fs from 80% to 100%
            scale = 0.8 + (1 - now) * 0.2;
            //2. take current_fs to the right(50%) - from 0%
            left = ((1 - now) * 50) + "%";
            //3. increase opacity of previous_fs to 1 as it moves in
            opacity = 1 - now;
            current_fs.css({'left': left});
            previous_fs.css({'transform': 'scale(' + scale + ')', 'opacity': opacity});
        },
        duration: 800,
        complete: function () {
            current_fs.hide();
            animating = false;
        },
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
    });
});
