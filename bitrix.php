<?php
include_once 'common.php';

function make_bitrix_request($path, $params) {
    $config = include('.config.php');

    $query_url = sprintf(
        '%s/rest/11/%s/%s.json',
        $config['bitrix_host'],
        $config['bitrix_webhook_key'],
        $path
    );
    $query_data = http_build_query($params);

    log_to_file($query_data);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $query_url,
        CURLOPT_POSTFIELDS => $query_data,
    ));
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result, 1);

    return $result;
}

?>
