<?php
include_once '../bitrix.php';
include_once '../common.php';
include_once '../db.php';
include_once '../promo.php';

$config = include('../.config.php');

log_to_file('Вызван вебхук new_lead');
log_to_file(file_get_contents('php://input'));

$auth_token = null;
if (array_key_exists('auth', $_REQUEST)) {
    if (array_key_exists('application_token', $_REQUEST['auth'])) {
        $auth_token = $_REQUEST['auth']['application_token'];
    }
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    log_to_file('new_lead.php: метод не поддерживается');
    http_response_code(405);
} elseif ($_REQUEST['event'] != 'ONCRMLEADADD') {
    log_to_file('new_lead.php: неизвестное событие ' . $_REQUEST['event']);
    http_response_code(400);
} elseif ($_REQUEST['auth']['application_token'] != $config['bitrix_app_token']) {
    log_to_file('new_lead.php: отклонен входящий вебхук с неверным токеном доступа. '
                . 'IP-адрес: ' . $_SERVER['REMOTE_ADDR']);
    http_response_code(401);
} else {
    $bitrix_lead_id = $_REQUEST['data']['FIELDS']['ID'];

    $client = new Client($bitrix_lead_id);

    if ($client->exists()) {
        log_to_file("Лид " . $client->bitrix_lead_id . " уже есть в базе");
    } else {
        $promo_url_generator = new Promocode();
        $promo_url_param = $promo_url_generator->get_new_promo_url();
        $promo_url = $config['host'] . '/activate-promo.php?promo=' . $promo_url_param;

        $client->promo_url_param = $promo_url;
        $client->save();

        $fields = array(
            'UF_CRM_1592744455' => $promo_url,
        );

        $query_data = array(
            'id' => $client->bitrix_lead_id,
            'fields' => $fields,
            'params' => array("REGISTER_SONET_EVENT" => "N"),
        );

        $result = make_bitrix_request('crm.lead.update', $query_data);

        if (array_key_exists('error', $result)) {
            log_to_file("Ошибка при обновлении лида: " . $result['error_description']);
        } else {
            log_to_file("Лиду " . $client->bitrix_lead_id . " присвоен промо-URL " . $promo_url);
        }
    }
}

?>
