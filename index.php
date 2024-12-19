
<?php
// https://b24-jr3k5i.bitrix24.ru/rest/1/m8plllckhgjsn4w0/

require_once("src/crest.php");

$start = microtime(true);

$array = [];

$totalContacts = CRest::call(
    'crm.contact.list'
);

$array["total_contacts"] = $totalContacts['total'];

$totalDeals = CRest::call(
    'crm.deal.list'
);

$array["total_deal"] = $totalDeals['total'];

// Узнать количество контактов с заполненным полем COMMENTS

$countContactWithComments = CRest::call(
'crm.contact.list',
        [
            'FILTER' =>
            [
                '!=COMMENTS' => '',
            ],
        ]
);

$array["count_with_comments"] = $countContactWithComments['total'];

// Найти все сделки без контактов

$dealWithoutContacts = CRest::call(
'crm.deal.list',
        [
            'FILTER'=>
            [
                '!=CONTACT_ID' => 0,
            ],
        ]
);

$array["deal_without_contacts"] = $dealWithoutContacts['total'];

// Узнать сколько сделок в каждой из существующих Направлений

$categories = CRest::call(
'crm.category.list',
        [
            'entityTypeId' => 2, // 2 - id сделок (прочитал в документации)
        ]
);

foreach ($categories['result']['categories'] as $key=>$category) {
    $deals = CRest::call(
    'crm.deal.list',
        [
            'FILTER' =>
            [
                'CATEGORY_ID' => $category['id']
            ]
        ]
    );

    $array["count_$key"."_chopper"] = $deals['total'];

}

// Посчитать сумму значений поля "Баллы", его код - ufCrm5_1734072847, узнается через item.fields (в элементе с title = "Баллы")
//

$countScore = 0;
$i = 0;

do {
    $items = CRest::call(
        'crm.item.list',
        [
            'entityTypeId' => 1038,
            'select' => ['ufCrm5_1734072847'],
            'filter' => ['!=ufCrm5_1734072847' => 0],
            'start' => $i * 50,
        ],
    );

    foreach ($items['result']['items'] as $key => $item) {
        $countScore += $item['ufCrm5_1734072847'];
    }

    $i++;

} while(count($items['result']['items']));

$array["point_sum"] = $countScore;

echo "<pre>";
print_r($array);
echo "</pre>";

echo 'Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';