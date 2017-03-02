<?php

$meta['more_news'] = ['numeric'];

$meta['hash_no'] = ['numeric', '_caution' => 'warning'];
$meta['no_pref'] = ['numeric', '_caution' => 'warning'];

$meta['perm_share'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];
$meta['perm_fb'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];
$meta['perm_tw'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];
$meta['perm_link'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];
$meta['perm_gp'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];

$meta['perm_add'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];
$meta['perm_manage'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];
$meta['perm_rss'] = ['multichoice', '_choices' => [1, 2, 4, 8, 16, 32, 64, 128, 255, 1000]];

$meta['default_image'] = ['string'];
$meta['tpl'] = ['string'];
