<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$site_title = 'domain list';

require SITE_HEADER;

$domains = miz_domain::get_all();

?>

<div class="mb-3">
    <h1 class="h4 mb-0">Domains (<?= count($domains) ?>)</h1>
</div>

<div class="mb-3">
    <input type="text" class="form-control form-control-sm" id="filter-text" placeholder="Lọc domain...">
    <div class="text-body-secondary mt-1 text-end" id="filter-count" style="font-size: .8rem;"></div>
</div>

<div class="mb-3">
    <a class="btn btn-sm btn-primary" href="/domain-edit.php">Thêm domain</a>
    <a href="/cmd.php?cmd=/miz/bin/miz-lamp-deploy" class="btn btn-sm btn-success">CẬP NHẬT CÀI ĐẶT</a>
</div>

<?php foreach ($domains as $domain_id => $domain_config) {
    $domain_list = miz_domain::parse_domains($domain_config['domains']);
?>
<div id="domain-<?= e($domain_id) ?>" class="card shadow-sm mb-3">
    <div class="card-body">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($domain_list as $domain_name) {
            $href = 'https://' . (strpos($domain_name, '*.') === 0 ? substr($domain_name, 2) : $domain_name);
        ?>
        <a class="badge text-bg-light border text-decoration-none" href="<?= e($href) ?>" target="_blank">
            <?= e($domain_name) ?>
        </a>
        <?php } ?>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <span class="badge <?= $domain_config['status'] === 'on' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($domain_config['status']) ?></span>
        <span class="badge text-bg-secondary"><?= e($domain_config['mode']) ?></span>
        
        <span class="badge text-bg-dark">|</span>

        <a class="badge text-bg-warning" href="/domain-edit.php?id=<?= rawurlencode($domain_id) ?>">[SỬA]</a>
        <a class="badge text-bg-danger" href="/domain-delete.php?id=<?= rawurlencode($domain_id) ?>">[XÓA]</a>
    </div>
    </div>
</div>
<?php } ?>

<script>
    function filter_match(text, query) {
        return text.toLowerCase().indexOf(query.toLowerCase()) !== -1;
    }

    const $filter_input = $('#filter-text');
    const $count_el = $('#filter-count');
    const $cards = $('.card.shadow-sm.mb-3');

    $filter_input.on('input', function () {
        const query = $(this).val();
        let visible_count = 0;

        $cards.each(function () {
            const $badges = $(this).find('.badge.text-bg-light.border');
            const match = $badges.toArray().some(function (badge) {
                return filter_match($(badge).text(), query);
            });
            const show = query === '' || match;
            $(this).toggle(show);
            if (show) visible_count++;
        });

        $count_el.text(visible_count + '/' + $cards.length);
    });

    $count_el.text($cards.length + '/' + $cards.length);
</script>

<?php require SITE_FOOTER; ?>
