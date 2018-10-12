<div class="modal fade myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">提示消息</h4>
            </div>
            
            <div class="modal-body" id="myModalBody">内容</div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="button" data-dismiss="modal">确定</button>
            </div>
            
       </div>
    </div> 
</div>

<script type="text/javascript">
    
    /**
     * 显示模态框
     * @param {String} url
     * @returns {Boolean}
     */
    function showModal(url){
        $(".myModal").html("");
        $('.myModal').modal("show").load(url);
        return false;
    }

</script>
