<!--素材移动-->
<p style="margin: 0px;">
<?php foreach ($dataProvider as $data): ?>
    目录【<span style="color:#0066FF;"><?= $data['category_name'] ?></span>】由【<span style="color:#0066FF"><?= $data['old_parent_path'] ?></span>】移动到【<span style="color:#0066FF;"><?= $data['new_parent_path'] ?></span>】<br/>
<?php endforeach; ?>
</p>