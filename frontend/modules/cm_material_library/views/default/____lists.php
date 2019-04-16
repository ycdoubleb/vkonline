<div class="material-content">
    <div class="material-info" data-url="/cm_material_library/default/preview?id={%id%}">
        <div class="open-save">
            <span><i class="glyphicon glyphicon-eye-open"></i> {%visit_count%}</span>
            <span><i class="glyphicon glyphicon-save"></i> {%download_count%}</span>
        </div>
        <div class="material-img" style="background: url({%cover_url%}) center center / contain no-repeat"></div>
        <!--<img class="material-img" src="{%cover_url%}"/>-->
    </div>
    <div class="material-operating">
        <div class="material-name single-clamp"><i class="{%icon%}"></i> {%name%}</div>
        <a download="{%name%}" href="{%download_url%}" title="下载">
            <i class="glyphicon glyphicon-save"></i>
        </a>
        <a class="download" href="download://resource/{%name%}/{%file_id%}/{%name%}/{%created_at%}/{%size%}/" title="插入">
            <i class="glyphicon glyphicon-ok"></i>
        </a>
    </div>
</div>