<?php

require_once 'vendor/autoload.php';
$start = microtime(true);
$main_url = 'https://www.wildberries.ru';
$page_get_request = '?page=';
$html = file_get_contents($main_url);
phpQuery::newDocument($html);

$list_menu_item_dom = pq('ul.topmenus')->children('li:not(.divider):not(.submenuless):not(.row-divider):not(.promo-offer):not(.brands):not(.certificate)');

$list_menu_items = Array();

foreach ($list_menu_item_dom as $key => $value) {

    $li = pq($value)->children('a');

    if ($li->html() !== '' && $li->attr('href') !== '') {
        $list_menu_items[$key]['name'] = $li->html();
        $list_menu_items[$key]['link'] = $li->attr('href');
    }
    
    $html_temp = file_get_contents($list_menu_items[$key]['link']);
    phpQuery::newDocument($html_temp);

    $list_submenu_item_dom = pq('ul.maincatalog-list-1')->children('li:not(.j-all-menu-item)');
    foreach ($list_submenu_item_dom as $keys => $val) {
        $li_submenu = pq($val)->children('a');
        $list_menu_items[$key]['subcategories'][$keys]['name'] = $li_submenu->html();
        $list_menu_items[$key]['subcategories'][$keys]['link'] = $li_submenu->attr('href');
        
        phpQuery::unloadDocuments();
        
        $html_temp_sub = file_get_contents($main_url . $list_menu_items[$key]['subcategories'][$keys]['link']);
        phpQuery::newDocument($html_temp_sub);
        $list_menu_items[$key]['subcategories'][$keys]['count_product'] = pq('.total.many>span:not(.active)')->text();

        foreach (pq('.pager-bottom .pager .pageToInsert')->children('a') as $k => $v) {
            if ($k + 2 == pq('.pager-bottom .pager .pageToInsert')->children('a')->count())
                $list_menu_items[$key]['subcategories'][$keys]['count_page'] = pq($v)->html();
        }
        phpQuery::unloadDocuments();
        
        for ($i = 1; $i <= $list_menu_items[$key]['subcategories'][$keys]['count_page']; $i++) 
        {
            phpQuery::newDocument(file_get_contents($main_url.$list_menu_items[$key]['subcategories'][$keys]['link'].$page_get_request.$i));
            foreach (pq('.catalog_main_table .ref_goods_n_p') as $q => $qq) 
            {
                $id = pq($qq)->children('.l_class')->attr('id');
                $link = pq($qq)->attr('href');
                $price = pq($qq)->children('.price')->text();
                $list_menu_items[$key]['subcategories'][$keys]['items'][$q]['id'] = $id;
                $list_menu_items[$key]['subcategories'][$keys]['items'][$q]['link'] = $link;
                $list_menu_items[$key]['subcategories'][$keys]['items'][$q]['price'] = preg_replace("/[^0-9]/", '', $price);
            }
            phpQuery::unloadDocuments();
        }
    }
}
$time = microtime(true) - $start;
printf('Скрипт выполнялся %.4F сек.', $time);
xprint($list_menu_items);
phpQuery::unloadDocuments();
?>