<li id="{%id%}">
    <div class="head">
        <a href="#toggle_{%id%}" data-toggle="collapse" aria-expanded="false" onclick="replace($(this))">
            <div>
                <i class="fa fa-caret-right"></i>
            </div>
            <span class="name">{%name%}</span>
        </a>
        <div class="icongroup">
            <a href="../video/create?node_id={%id%}" onclick="showModal($(this));return false;">
                <i class="fa fa-plus"></i>
            </a>
            <a href="../course-node/update?id={%id%}" onclick="showModal($(this));return false;">
                <i class="fa fa-pencil"></i>
            </a>
            <a href="../course-node/delete?id={%id%}" onclick="showModal($(this));return false;">
                <i class="fa fa-times"></i>
            </a>
            <a href="javascript:;" class="handle"><i class="fa fa-arrows"></i></a>
        </div>
    </div>
    <div id="toggle_{%id%}" class="collapse nodes" aria-expanded="false">
        <ul id="video" class="sortable list"></ul>
    </div>
</li>