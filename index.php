<?php

$errorsFile = "errors.json";
$sitesFile = "websites.csv";

$csv = array_map('str_getcsv', file($sitesFile));
array_walk($csv, function(&$a) use ($csv) {
  $a = array_combine($csv[0], $a);
});
array_shift($csv); # remove column header

$item = $csv[array_rand($csv)];

$url = $item["URL"];

if (php_sapi_name() === "cli") {
    echo "{$url}: ";
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_NOBODY, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$head = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$errors = json_decode(file_get_contents($errorsFile), true);

if ($httpCode >= 400) {
    $errors[$url] = $httpCode;
    file_put_contents($errorsFile, json_encode($errors));
} else {
    if (isset($errors[$url])) {
        if (php_sapi_name() === "cli") echo "[!!!] ";
        unset($errors[$url]);
    }
    file_put_contents($errorsFile, json_encode($errors));
}

if (php_sapi_name() !== "cli") {
    readfile($errorsFile);
    echo "\n";
} else {
    echo "{$httpCode}\n";
}
