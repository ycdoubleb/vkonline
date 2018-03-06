/* 
 * @link http://www.wskeee.com
 * @copyright Y.C.Double.B
 * @author wskeee
 */
var wskeee = window.wskeee = {};
wskeee.logger = 
{
    trace : function()
    {
        if(typeof(console) != "undefined" && typeof(console.log) != "undefined") console.log.apply(console,arguments);
    },
    error : function()
    {
        if(typeof(console) != "undefined" && typeof(console.error) != "undefined") console.error.apply(console,arguments);
    }
}
wskeee.rbac = (function($)
{
    //搜索进行中...
    var _onSearching = false;
    var pub = {
        id:undefined,
        assignUrl:undefined,
        searchUrl:undefined,
        //分配
        assign : function(action)
        {
            var params = {
                id : pub.id,
                action : action,
                items : $('#list-' + (actioin == 'assign' ? 'avaliable' : 'assigned')).val()
            };
            $.post(pub.searchUrl,params,
                function()
                {
                    //...更新
                });
        },
        /**
         * 查找 角色/权限
         * @param {string} id  userId/角色id
         * @param {string} target   avaliable/assigned 所有/已分配
         * @param {string} force    是否强制
         * @returns {void}
         */
        searchItem:function(id,target,force)
        {
            if(!_onSearching || force)
            {
                _onSearching = true;
                var $inp = $('#search-'+target);
                setTimeout(function()
                {
                    var data = {
                        id : id,
                        target : target,
                        term : $inp.val()
                    };
                    $.get(pub.searchUrl,data,
                        function(r)
                        {
                            var $list = $('#list-'+target);
                            $list.html('');
                            wskeee.logger.trace(r);
                            if(r.Roles)
                            {
                                var $group = $('<optgroup label="角色"/>');
                                $.each(r.Roles,function()
                                {
                                    $('<option>').val(this['name']).text(this['description']).appendTo($group);
                                });
                                $group.appendTo($list);
                            }

                            if(r.Permissions)
                            {
                                var $group = $('<optgroup label="权限"/>');
                                $.each(r.Permissions,function()
                                {
                                    $('<option>').val(this['name']).text(this['description']).appendTo($group);
                                });
                                $group.appendTo($list);
                            }
                        }).done(function()
                        {
                            _onSearching = false;
                        });
                },500);
            }
        },
        /**
         * 查找用户的角色与权限
         * @param {string} target   avaliable/assigned 所有/已分配
         * @param {string} force    强制
         * @returns {void}
         */
        searchAssign:function(target, force)
        {
            pub.searchItem(pub.id,target,force);
        },
        /**
         * 查找角色的权限
         * @param {string} target   avaliable/assigned 所有/已分配
         * @param {string} force    强制
         * @returns {void}
         */
        searchRole:function(target, force)
        {
            pub.searchItem(pub.id,target,force);
        },
        addChild:function(action)
        {
            var params={
                id : pub.id,
                action : action,
                items : $('#list-'+(action == 'assign' ? 'avaliable' : 'assigned')).val()
            };
            $.post(pub.assignUrl,params,function(data)
            {
                pub.searchRole('avaliable',true);
                pub.searchAssign('assigned',true);
                wskeee.logger.trace('【addChild】','action: '+action,'result: ',data);
            });
        },
        /**
         * 初始属性 = {
         *      userId:String,
         *      roleName:String,
         *      searchUrl:String,
         *      assignUrl:String
         *  }
         * @param {Object} properties
         * @returns {void}
         */
        initProperties:function(properties)
        {
            $.each(properties,function (key,value)
            {
                pub[key] = value;
            })
        }
    };
    return pub;
})(jQuery);
