(function(win,$){
    $.fn.e_webupload_fileinput = function(config){
        $('body').append(renderModal());
        var _modal = $('#' + config['modal_id']),
            chooseObject; // 点击选择图片的按钮
        
        _modal.on("shown.bs.modal", init);
        
        function init(){
            var $wrap = $('#uploader'),
            // 图片容器
            $queue = $( '<ul class="filelist"></ul>' ).appendTo( $wrap.find( '.queueList' ) ),
            // 状态栏，包括进度和控制按钮
            $statusBar = $wrap.find( '.statusBar' ),
            // 文件总体选择信息。
            $info = $statusBar.find( '.info' ),
            // 上传按钮
            $uploadBtn = $wrap.find( '.uploadBtn' ),
            // 没选择文件之前的内容。
            $placeHolder = $wrap.find( '.placeholder' ),
            $progress = $statusBar.find( '.progress' ).hide(),
            // 添加的文件数量
            fileCount = 0,
            // 添加的文件总大小
            fileSize = 0,
            // 优化retina, 在retina下这个值是2
            ratio = window.devicePixelRatio || 1,
            // 缩略图大小
            thumbnailWidth = 110 * ratio,
            thumbnailHeight = 110 * ratio,
            // 可能有pedding, ready, uploading, confirm, done.
            state = 'pedding',
            // 所有文件的进度信息，key为file id
            percentages = {},
            // 判断浏览器是否支持图片的base64
            isSupportBase64 = ( function() {
                var data = new Image();
                var support = true;
                data.onload = data.onerror = function() {
                    if( this.width != 1 || this.height != 1 ) {
                        support = false;
                    }
                }
                data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
                return support;
            } )(),

            // 检测是否已经安装flash，检测flash的版本
            flashVersion = ( function() {
                var version;

                try {
                    version = navigator.plugins[ 'Shockwave Flash' ];
                    version = version.description;
                } catch ( ex ) {
                    try {
                        version = new ActiveXObject('ShockwaveFlash.ShockwaveFlash')
                                .GetVariable('$version');
                    } catch ( ex2 ) {
                        version = '0.0';
                    }
                }
                version = version.match( /\d+/g );
                return parseFloat( version[ 0 ] + '.' + version[ 1 ], 10 );
            } )(),

            supportTransition = (function(){
                var s = document.createElement('p').style,
                    r = 'transition' in s ||
                            'WebkitTransition' in s ||
                            'MozTransition' in s ||
                            'msTransition' in s ||
                            'OTransition' in s;
                s = null;
                return r;
            })(),
            //成功上传的文件信息
            uploadedFiles,
            //成功上传的文件数量
            successNum = 0,
            // WebUploader实例
            uploader;
            
            //flash player 检测
            if ( !WebUploader.Uploader.support('flash') && WebUploader.browser.ie ) {
                // flash 安装了但是版本过低。
                if (flashVersion) {
                    (function(container) {
                        window['expressinstallcallback'] = function( state ) {
                            switch(state) {
                                case 'Download.Cancelled':
                                    alert('您取消了更新！')
                                    break;

                                case 'Download.Failed':
                                    alert('安装失败')
                                    break;

                                default:
                                    alert('安装已成功，请刷新！');
                                    break;
                            }
                            delete window['expressinstallcallback'];
                        };

                        var swf = './expressInstall.swf';
                        // insert flash object
                        var html = '<object type="application/' +
                                'x-shockwave-flash" data="' +  swf + '" ';

                        if (WebUploader.browser.ie) {
                            html += 'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
                        }

                        html += 'width="100%" height="100%" style="outline:0">'  +
                            '<param name="movie" value="' + swf + '" />' +
                            '<param name="wmode" value="transparent" />' +
                            '<param name="allowscriptaccess" value="always" />' +
                        '</object>';

                        container.html(html);

                    })($wrap);

                // 压根就没有安转。
                } else {
                    $wrap.html('<a href="http://www.adobe.com/go/getflashplayer" target="_blank" border="0"><img alt="get flash player" src="http://www.adobe.com/macromedia/style_guide/images/160x41_Get_Flash_Player.jpg" /></a>');
                }

                return;
            } else if (!WebUploader.Uploader.support()) {
                alert( 'Web Uploader 不支持您的浏览器！');
                return;
            }
            
            // 实例化
            uploader = WebUploader.create({
                pick: {
                    id: '#filePicker',
                    label: '点击选择文件',
                    multiple: config.pick.multiple
                },
                dnd: '#uploader .queueList',
                paste: document.body,
                accept: config.accept,
                swf: './webuploader/Uploader.swf',
                server: config.server,
                formData: config.formData,
                disableGlobalDnd: config.disableGlobalDnd,
                chunked: config.chunked,
                chunkSize: config.chunkSize,
                fileNumLimit: config.pick.multiple ? config.fileNumLimit : 1,
                fileSizeLimit: config.fileSizeLimit,
                fileSingleSizeLimit: config.fileSingleSizeLimit,
                compress: config.compress != null ? {
                    width: config.compress.width,
                    height: config.compress.height,
                    quality: config.compress.quality,
                    allowMagnify: config.compress.allowMagnify,
                    crop: config.compress.crop,
                    preserveHeaders: config.compress.preserveHeaders,
                    noCompressIfLarger: config.compress.noCompressIfLarger,
                    compressSize: config.compress.compressSize
                } : false,
            });
            
            // 拖拽时不接受 js, txt 文件。
            uploader.on( 'dndAccept', function( items ) {
                var denied = false,
                    len = items.length,
                    i = 0,
                    // 修改js类型
                    unAllowed = 'text/plain;application/javascript ';
                for ( ; i < len; i++ ) {
                    // 如果在列表里面
                    if ( ~unAllowed.indexOf( items[ i ].type ) ) {
                        denied = true;
                        break;
                    }
                }

                return !denied;
            });

            uploader.on('dialogOpen', function() {
                console.log('here');
            });
            
            // 添加“添加文件”的按钮，
            uploader.addButton({
                id: '#filePicker2',
                label: '+',
                multiple: config.pick.multiple
            });
            
            // 当有文件添加进来时执行，负责view的创建
            function addFile( file ) {
                var $li = $('<li id="' + file.id + '">' +
                        '<p class="title">' + file.name + '</p>' +
                        '<p class="imgWrap"></p>' +
                        '<p class="progress"><span></span></p>' +
                        '</li>'),
                        $btns = $('<div class="file-panel">' +
                                '<span class="cancel">删除</span>' +
                                '<span class="rotateRight">向右旋转</span>' +
                                '<span class="rotateLeft">向左旋转</span></div>').appendTo($li),
                        $prgress = $li.find('p.progress span'),
                        $wrap = $li.find('p.imgWrap'),
                        $info = $('<p class="error"></p>'),
                        showError = function (code) {
                            switch (code) {
                                case 'exceed_size':
                                    text = '文件大小超出';
                                    break;

                                case 'interrupt':
                                    text = '上传暂停';
                                    break;

                                default:
                                    text = '上传失败，请重试';
                                    break;
                            }

                            $info.text(text).appendTo($li);
                        };

                if (file.getStatus() === 'invalid') {
                    showError(file.statusText);
                } else {
                    // @todo lazyload
                    $wrap.text('预览中');
                    uploader.makeThumb(file, function (error, src) {
                        var img;

                        if (error) {
                            $wrap.text('不能预览');
                            return;
                        }

                        if (isSupportBase64) {
                            img = $('<img src="' + src + '">');
                            $wrap.empty().append(img);
                        } else {
                            $.ajax('../../server/preview.php', {
                                method: 'POST',
                                data: src,
                                dataType: 'json'
                            }).done(function (response) {
                                if (response.result) {
                                    img = $('<img src="' + response.result + '">');
                                    $wrap.empty().append(img);
                                } else {
                                    $wrap.text("预览出错");
                                }
                            });
                        }
                    }, thumbnailWidth, thumbnailHeight);

                    percentages[ file.id ] = [file.size, 0];
                    file.rotation = 0;
                }

                file.on('statuschange', function (cur, prev) {
                    if (prev === 'progress') {
                        $prgress.hide().width(0);
                    } else if (prev === 'queued') {
                        $li.off('mouseenter mouseleave');
                        $btns.remove();
                    }

                    // 成功
                    if (cur === 'error' || cur === 'invalid') {
                        console.log(file.statusText);
                        showError(file.statusText);
                        percentages[ file.id ][ 1 ] = 1;
                    } else if (cur === 'interrupt') {
                        showError('interrupt');
                    } else if (cur === 'queued') {
                        $info.remove();
                        $prgress.css('display', 'block');
                        percentages[ file.id ][ 1 ] = 0;
                    } else if (cur === 'progress') {
                        $info.remove();
                        $prgress.css('display', 'block');
                    } else if (cur === 'complete') {
                        $prgress.hide().width(0);
                        $li.append('<span class="success"></span>');
                    }

                    $li.removeClass('state-' + prev).addClass('state-' + cur);
                });

                $li.on('mouseenter', function () {
                    $btns.stop().animate({height: 30});
                });

                $li.on('mouseleave', function () {
                    $btns.stop().animate({height: 0});
                });

                $btns.on('click', 'span', function () {
                    var index = $(this).index(),
                            deg;

                    switch (index) {
                        case 0:
                            uploader.removeFile(file);
                            return;

                        case 1:
                            file.rotation += 90;
                            break;

                        case 2:
                            file.rotation -= 90;
                            break;
                    }

                    if (supportTransition) {
                        deg = 'rotate(' + file.rotation + 'deg)';
                        $wrap.css({
                            '-webkit-transform': deg,
                            '-mos-transform': deg,
                            '-o-transform': deg,
                            'transform': deg
                        });
                    } else {
                        $wrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
                        // use jquery animate to rotation
                        // $({
                        //     rotation: rotation
                        // }).animate({
                        //     rotation: file.rotation
                        // }, {
                        //     easing: 'linear',
                        //     step: function( now ) {
                        //         now = now * Math.PI / 180;

                        //         var cos = Math.cos( now ),
                        //             sin = Math.sin( now );

                        //         $wrap.css( 'filter', "progid:DXImageTransform.Microsoft.Matrix(M11=" + cos + ",M12=" + (-sin) + ",M21=" + sin + ",M22=" + cos + ",SizingMethod='auto expand')");
                        //     }
                        // });
                    }


                });

                $li.appendTo($queue);
            }
            // 负责view的销毁
            function removeFile( file ) {
                var $li = $('#'+file.id);

                delete percentages[ file.id ];
                updateTotalProgress();
                $li.off().find('.file-panel').off().end().remove();
            }
            
            //更新总进度
            function updateTotalProgress() {
                var loaded = 0,
                    total = 0,
                    spans = $progress.children(),
                    percent;

                $.each( percentages, function( k, v ) {
                    total += v[ 0 ];
                    loaded += v[ 0 ] * v[ 1 ];
                } );

                percent = total ? loaded / total : 0;


                spans.eq( 0 ).text( Math.round( percent * 100 ) + '%' );
                spans.eq( 1 ).css( 'width', Math.round( percent * 100 ) + '%' );
                updateStatus();
            }
            
            //更新状态
            function updateStatus() {
                var text = '', stats;

                if ( state === 'ready' ) {
                    text = '选中' + fileCount + '个文件，共' +
                            WebUploader.formatSize( fileSize ) + '。';
                } else if ( state === 'confirm' ) {
                    stats = uploader.getStats();
                    if ( stats.uploadFailNum ) {
                        text = '已成功上传' + stats.successNum+ '，'+
                            stats.uploadFailNum + '个文件上传失败，<a class="retry" href="#">重新上传</a>失败文件或<a class="ignore" href="#">忽略</a>'
                    }

                } else {
                    stats = uploader.getStats();
                    text = '共' + fileCount + '个（' +
                            WebUploader.formatSize( fileSize )  +
                            '），已上传' + stats.successNum + '个';

                    if ( stats.uploadFailNum ) {
                        text += '，失败' + stats.uploadFailNum + '个';
                    }
                }

                $info.html( text );
            }
            
            //设置状态
            function setState( val ) {
                var file, stats;

                if ( val === state ) {
                    return;
                }

                $uploadBtn.removeClass( 'state-' + state );
                $uploadBtn.addClass( 'state-' + val );
                state = val;

                switch ( state ) {
                    case 'pedding':
                        $placeHolder.removeClass( 'element-invisible' );
                        $queue.hide();
                        $statusBar.addClass( 'element-invisible' );
                        uploader.refresh();
                        break;

                    case 'ready':
                        $placeHolder.addClass( 'element-invisible' );
                        $( '#filePicker2' ).removeClass( 'element-invisible');
                        $queue.show();
                        $statusBar.removeClass('element-invisible');
                        uploader.refresh();
                        break;

                    case 'uploading':
                        $( '#filePicker2' ).addClass( 'element-invisible' );
                        $progress.show();
                        $uploadBtn.text( '暂停上传' );
                        break;

                    case 'paused':
                        $progress.show();
                        $uploadBtn.text( '继续上传' );
                        break;

                    case 'confirm':
                        $progress.hide();
                        $( '#filePicker2' ).removeClass( 'element-invisible' );
                        $uploadBtn.text( '开始上传' );

                        stats = uploader.getStats();
                        if ( stats.successNum && !stats.uploadFailNum ) {
                            setState( 'finish' );
                            return;
                        }
                        break;
                    case 'finish':
                        stats = uploader.getStats();
                        if ( stats.successNum ) {
                            //alert( '上传成功' );
                            _modal.modal('hide');
                        } else {
                            // 没有成功的图片，重设
                            state = 'done';
                            location.reload();
                        }
                        break;
                }

                updateStatus();
            }
            
            //单独文件上传进度处理
            uploader.onUploadProgress = function( file, percentage ) {
                var $li = $('#'+file.id),
                    $percent = $li.find('.progress span');

                $percent.css( 'width', percentage * 100 + '%' );
                percentages[ file.id ][ 1 ] = percentage;
                updateTotalProgress();
            };
            
            //文件加入队列
            uploader.onFileQueued = function( file ) {
                fileCount++;
                fileSize += file.size;

                if ( fileCount === 1 ) {
                    $placeHolder.addClass( 'element-invisible' );
                    $statusBar.show();
                }

                addFile( file );
                setState( 'ready' );
                updateTotalProgress();
            };
            
            //文件从队列里删除
            uploader.onFileDequeued = function( file ) {
                fileCount--;
                fileSize -= file.size;

                if ( !fileCount ) {
                    setState( 'pedding' );
                }

                removeFile( file );
                updateTotalProgress();

            };
            
            uploader.on( 'all', function( type ) {
                var stats;
                switch( type ) {
                    case 'uploadFinished':
                        setState( 'confirm' );
                        break;
                    case 'startUpload':
                        setState( 'uploading' );
                        break;
                    case 'stopUpload':
                        setState( 'paused' );
                        break;
                }
            });
            
            uploader.onError = function( code ) {
                var msg = code;
                switch (code) {
                    case 'Q_EXCEED_NUM_LIMIT':
                        msg = '添加的文件数量超出 fileNumLimit 的设置';
                        break;
                    case 'Q_EXCEED_SIZE_LIMIT':
                        msg = '添加的文件总大小超出了 fileSizeLimit 的设置';
                        break;
                    case 'Q_TYPE_DENIED':
                        msg = '添加的文件类型错误';
                        break;
                    case 'P_DUPLICATE':
                        msg = '添加的文件重复了';
                        break;
                }
                alert( 'Error: ' + msg );
            };
            
            uploader.onUploadSuccess = function (b, c) {
                //return (k++, uploadedFiles.push(c))
                
            }
            
            $uploadBtn.on('click', function() {
                if ( $(this).hasClass( 'disabled' ) ) {
                    return false;
                }
                if ( state === 'ready' ) {
                    uploader.upload();
                } else if ( state === 'paused' ) {
                    uploader.upload();
                } else if ( state === 'uploading' ) {
                    uploader.stop();
                }
            });
            $info.on( 'click', '.retry', function() {
                uploader.retry();
            } );
            $info.on( 'click', '.ignore', function() {
                alert( 'todo' );
            } );
            $uploadBtn.addClass( 'state-' + state );
            updateTotalProgress();

            $('#filePicker2').mouseenter(function(){
                uploader.refresh();
            });;
        }
        /**
         * 渲染模态框
         * @returns {String|Boolean}
         */
        function renderModal () {
            var modal_id = config['modal_id'];
            if ($('#' + modal_id).length == 0) {
                return '<div id="' + config['modal_id'] + '" class="fade modal modal-c" role="dialog" tabindex="-1">' +
                        '<div class="modal-dialog cus-size">' +
                            '<div class="modal-content">' +
                                '<div class="modal-header">' +
                                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                                    '<h4 class="modal-title">上传文件</h4>' +
                                '</div>' +
                                '<div class="modal-body">' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            } else {
                return false;
            }
        }
        
        /**
         * 渲染模态框
         * @returns {String}
         */
        function buildModalBody () {
            return '<div role="tabpanel" class="tab-pane upload active" id="upload">' +
                        '<div id="uploader" class="uploader">' +
                            '<div class="queueList">' +
                                '<div id="dndArea" class="placeholder">' +
                                    '<div id="filePicker"></div>' +
                                        '<p id="">或将文件拖到这里</p>' +
                               ' </div>' +
                            '</div>' +
                            '<div class="statusBar">' +
                                '<div class="infowrap">' +
                                    '<div class="progress" style="display: none;">' +
                                        '<span class="text">0%</span>' +
                                        '<span class="percentage" style="width: 0%;"></span>' +
                                    '</div>' +
                                    '<div class="info">共0个（0B），已上传0个</div>' +
                                    '<div class="accept"></div>' +
                                '</div>' +
                                '<div class="btns">' +
                                    '<div class="uploadBtn btn btn-primary state-pedding" style="margin-top: 4px;">确定使用</div>' +
                                    '<div class="modal-button-upload" style="float: right; margin-left: 5px;">' +
                                        '<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
        }
        
        $('.' + config['modal_id']).on('click', function () {
            chooseObject = $(this);
            _modal.modal('show');
            _modal.find('.modal-body').html('');
            _modal.find('.modal-body').html(buildModalBody());
        });
        $(document).on('click', '.delImage', function () {
            var _this = $(this);
            _this.prev().attr("src", config.defaultImage);
            _this.parent().prev().find("input").val("");
        });
        $(document).on('click', '.delMultiImage', function () {
            $(this).parent().remove();
        });
        // 解决多modal下滚动以及filePicker失效问题
        $(document).on('hidden.bs.modal', '.modal', function () {
            if($('.modal:visible').length) {
                $(document.body).addClass('modal-open');
            }
            $('.modal-c').find('.modal-body').html('');
        });
    };
})(window,jQuery);
