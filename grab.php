<?php

include __DIR__.'/src/Cache.php';
include __DIR__.'/src/Data.php';
include __DIR__.'/src/Telegram.php';

$data = new Data();
$cache = new Cache();

$shops = $data->retrieve();

if (!$shops) {
    throw new Exception('No data received');
} else {
    var_dump($shops);
}

$normalPrefix = 'normal';
$digitalPrefix = 'digital';

$available = [];

foreach ($shops as $shop) {
    $shopName = $shop['name'];

    foreach (
        [
            $normalPrefix,
            $digitalPrefix,
        ] as $prefix
    ) {
        if ($shop["{$prefix}_info"]['available'] ?? false) {
            $available[$shopName][$prefix] ??= $shop["{$prefix}_link"];
        }
    }
}

if ($cache->pull() === $available) {
    echo "Same";
    exit;
}

$cache->push($available);

if ($available) {
    $message = "";

    foreach ($available as $shopName => $shopData) {
        $list = [];

        foreach (
            [
                $normalPrefix => '💿 с диском',
                $digitalPrefix => '🌐 без диска',
            ] as $prefix => $caption
        ) {
            if (isset($shopData[$prefix])) {
                $list[] = sprintf("[%s](%s)", $caption, $shopData[$prefix]);
            }
        }

        $message .= sprintf("%s: %s\n\n", $shopName, implode(', ', $list));
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