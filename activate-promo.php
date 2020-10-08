<?php
include_once 'bitrix.php';
include_once 'common.php';
include_once 'db.php';
include_once 'promo.php';

$next_url = '/index.html';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $config = include('.config.php');

    $promo_url_param = $_GET['promo'];
    $client = new Client(NULL, $promo_url_param);

    if ($client->exists() && !$client->promo_activated) {
        $fields = array(
            'UF_CRM_1592677957' => 'Применен',
        );

        $query_data = array(
            'id' => $client->bitrix_lead_id,
            'fields' => $fields,
            'params' => array("REGISTER_SONET_EVENT" => "Y"),
        );

        $result = make_bitrix_request('crm.lead.update', $query_data);

        if (array_key_exists('error', $result)) {
            log_to_file("Ошибка при обновлении лида: " . $result['error_description']);
        } else {
            $client->promo_activated = TRUE;
            $client->save();
        }

        $next_url = '/promo-thanks.html';
    } elseif ($client->promo_activated) {
        $next_url = '/promo-thanks.html';
    }
}

header('Location: ' . $next_url);
die();
?>
