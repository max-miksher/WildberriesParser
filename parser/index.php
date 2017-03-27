<?php 
require_once '../vendor/autoload.php';
require_once '../db_connect.php';      //подключиться к базе
require_once '../write_res.php';       //выполнение запросов к базе
require_once 'multy_query.php';       //параллельные запросы


 
$main_url = 'https://www.wildberries.ru';   //адрес магазина
$page_get_request = '?page=';               //добавочный адрес страница
$page_size = '&pagesize=200';               //выводить по 200 товаров на страницу
$list_menu_items = Array();                 //объявляем массив для данных
$max_connet = 170;


get_categiry();                             //получам категории и подкатегории в переменную

$urls = Array();                            //список первых страниц каждой подкатегории
foreach ($list_menu_items as $catkey => $catvalue) {
    foreach ($catvalue['subcategories'] as $subcatkey => $subcatvalue) {
        $urls[count($urls)] = $main_url.$subcatvalue['link'];
    }
}

//$urls = array_chunk($urls, 20);            //разбили массив категорий на пакеты

/*foreach ($urls as $key => $value) {        // спарсили каждый пакет
    $htmls[$key] = multyrequest($value);
}*/

$start = microtime(true);//начало отсчета времени работы скрипта
//foreach ($urls as $key => $url) {
    $ex=true;
    $corent_page = 1;                                   //текущая страница
    $htmls = Array();                                   //массив с ответами
    while($ex) 
    {
        $pages_array = Array();                         //массив страниц
                                                                  
        for($i=$corent_page;$i<$corent_page+$max_connet;$i++)    //собрали массив страниц для парсинга
        {
            if($i!=1)
                $pages_array[count($pages_array)] = $urls[0].$page_get_request.$i;
            else
                $pages_array[count($pages_array)] = $urls[0];
        }
        
        $corent_page += $max_connet;
        
        while(isset($pages_array))
        {
            $htmls_tmp = multyrequest($pages_array);            //получили ответ со страницами
            unset($pages_array);
            foreach ($htmls_tmp as $key => $html) {
                if((strpos($html['head'], 'TP/1.1 200 OK'))!=FALSE)
                {
                    //unset($htmls_tmp[$key]);
                    //$pages_array[count($pages_array)] = $key;
                    $ex=false;
                } 

                //xprint($html['head']);
            }
            //xprint($pages_array);
            
            $htmls += $htmls_tmp;
            
        }
        
        
        if($corent_page>=2200)$ex=false;
        
    }
    
//}
xprint(count($htmls));
$time = microtime(true) - $start;//сохраняем время работы скрипта
printf('Чтение подкатегорий завершено через %.4F сек.</br>', $time);//вывводим время работы скрипта