<?php

include __DIR__.'/src/Cache.php';
include __DIR__.'/src/Data.php';
include __DIR__.'/src/Telegram.php';

$data = new Data();
$cache = new Cache();

$allStores = $data->retrieve();

if (!$allStores) {
    throw new Exception('No data received');
}

$russiaStores = array_filter($allStores, fn(array $store) => strpos($store['price'], 'RUB'));

$available = array_values(array_filter($russiaStores, fn(array $store) => $store['status'] !== 'OUT_OF_STOCK'));

if ($cache->pull() === $available) {
    echo "Same";
    exit;
}

$cache->push($available);

if ($available) {
    $format = function (array $stores) {
        return array_map(fn(array $store) => sprintf("%s: %s", $store['site'], $store['url']), $stores);
    };

    $stores = array_unique(array_reduce($available, fn($stack, $store) => [...$stack, $store['site']], []));

    $ps5 = $data->filterByTitle($available, 'playstation 5');
    $ps5_digital = $data->filterByTitle($available, 'playstation 5 digital');

    $message = "";

    $ps5_icon = "💿";
    $ps5_digital_icon = "🌐";

    $ps5 && $message .= $ps5_icon;
    $ps5_digital && $message .= $ps5_digital_icon;
    $message .= " — можно заказать\n\n";

    foreach ($stores as $storeTitle) {
        $message .= "$storeTitle: ";

        if (isset($ps5[$storeTitle])) {
            $message .= sprintf("[%s %s](%s)", $ps5_icon, 'с диском', $ps5[$storeTitle]['url']);
        }

        if (isset($ps5[$storeTitle]) && isset($ps5_digital[$storeTitle])) {
            $message .= " ";
        }

        if (isset($ps5_digital[$storeTitle])) {
            $message .= sprintf("[%s %s](%s)", $ps5_digital_icon, 'без диска', $ps5_digital[$storeTitle]['url']);
        }

        $message .= "\n\n";
    }
} else {
    $message = 'А всё уже, раньше надо было';
}

$options = getopt('s');

if (isset($options['s'])) {
    echo $message;
} else {
    $telegram = new Telegram();
    $telegram->sendMessage($message);
}