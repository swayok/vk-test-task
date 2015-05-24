{{? it.total > 0 }}
<nav>
    <ul class="pager">
        <li class="{{?it.page === 1}}disabled{{?}}">
            <a href="#" class="prev-page"><span aria-hidden="true">&larr;</span> <?php echo \Dictionary\translate('Newer'); ?></a>
        </li>
        <li class="pagination-info">
            <?php echo \Dictionary\translate('Page'); ?>: {{= it.page }}
            (<?php echo \Dictionary\translate('Rows'); ?>: {{= (it.items_per_page * (it.page - 1)) + 1}} - {{=Math.min(it.total, it.items_per_page * it.page) }}
            <?php echo \Dictionary\translate('From'); ?> {{= it.total }})
        </li>
        <li class="{{?it.page === it.pages}}disabled{{?}}">
            <a href="#" class="next-page"><?php echo \Dictionary\translate('Older'); ?> <span aria-hidden="true">&rarr;</span></a>
        </li>
    </ul>
</nav>
{{?}}