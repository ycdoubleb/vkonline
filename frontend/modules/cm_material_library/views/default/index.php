<div class="cm_material_library-default-index">
    <?php foreach($medias as &$media): ?>
    <div>
        <span class="name single-clamp"><?= $media['name'] ?></span>
        <a class="download" href="<?= "download://resource/{$media['name']}/{$media['url']}/{$media['name']}/{$media['created_at']}/{$media['size']}/" ?>">下载</a>
    </div>
    <?php endforeach; ?>
</div>
