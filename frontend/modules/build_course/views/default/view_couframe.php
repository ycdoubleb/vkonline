<li id="{%id%}">
    <div class="head blue">
        <a href="#toggle_{%id%}" data-toggle="collapse" aria-expanded="true" onclick="replace($(this))"><i class="fa fa-minus-square-o"></i><span class="name">{%name%}</span></a>
        <div class="icongroup">
            <a href="add-couframe?node_id={%id%}" onclick="showModal($(this));return false;"><i class="fa fa-plus"></i></a>
            <a href="edit-couframe?id={%id%}" onclick="showModal($(this));return false;"><i class="fa fa-pencil"></i></a>
            <a href="del-couframe?id={%id%}" onclick="showModal($(this));return false;"><i class="fa fa-times"></i></a>
            <a href="javascript:;" class="handle"><i class="fa fa-arrows"></i></a>
        </div>
    </div>
    <div id="toggle_{%id%}" class="collapse in nodes" aria-expanded="true">
        <ul id="video" class="sortable list"></ul>
    </div>
</li>