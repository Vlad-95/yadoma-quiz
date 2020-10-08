<?php
include_once 'bitrix.php';
include_once 'common.php';
include_once 'db.php';
include_once 'promo.php';

$next_url = '/index.html';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config = include('.config.php');

    function get_user_choice($select_name) {
        $user_choice = 'Не определились';
        if (!empty($_POST[$select_name]) && is_array($_POST[$select_name])) {
            $user_choice = implode(', ', $_POST[$select_name]);
        }

        return array($user_choice);
    }

    session_start();

    $name = $_POST['name'];
    $tel_data = array(array(
        "VALUE" => $_POST['tel'],
        "VALUE_TYPE" => "WORK",
    ));
    $email_data = array(array(
        "VALUE" => $_POST['email'],
        "VALUE_TYPE" => "WORK",
    ));

    $desired_area_data = get_user_choice('desired_area');
    $desired_ending_date_data = get_user_choice('desired_ending_date');
    $desired_apartment_sizes_data = get_user_choice('desired_apartment_sizes');
    $desired_finishing_data = get_user_choice('desired_finishing');
    $desired_prices_data = get_user_choice('desired_prices');
    $desired_buy_method_data = get_user_choice('desired_buy_method');
    $desired_communication_method = $_POST['communication_method'];

    $promo_url_generator = new Promocode();
    $promo_url_param = $promo_url_generator->get_new_promo_url();
    $promo_url = $config['host'] . '/activate-promo.php?promo=' . $promo_url_param;

    $fields = array(
        'TITLE' => $config['bitrix_title'],
        'SOURCE_ID' => $config['bitrix_source_id'],
        'NAME' => $name,
        'PHONE' => $tel_data,
        'EMAIL' => $email_data,
        'UF_CRM_1583621851' => $desired_area_data,
        'UF_CRM_1588445932' => $desired_ending_date_data,
        'UF_CRM_1583621792' => $desired_apartment_sizes_data,
        'UF_CRM_1588446070' => $desired_finishing_data,
        'UF_CRM_1583257900' => $desired_prices_data,
        'UF_CRM_1588446114' => $desired_buy_method_data,
        'UF_CRM_1592677999' => $desired_communication_method,
        'UF_CRM_1592744455' => $promo_url,
        'UTM_SOURCE' => $_POST['utm_source'],
        'UTM_MEDIUM' => $_POST['utm_medium'],
        'UTM_CAMPAIGN' => $_POST['utm_campaign'],
        'UTM_CONTENT' => $_POST['utm_content'],
        'UTM_TERM' => $_POST['utm_term'],
    );

    $query_data = array(
        'fields' => $fields,
        'params' => array("REGISTER_SONET_EVENT" => "Y"),
    );

    $result = make_bitrix_request('crm.lead.add', $query_data);

    if (array_key_exists('error', $result)) {
        log_to_file("Ошибка при сохранении лида: " . $result['error_description']);
    } else {
        $bitrix_lead_id = (int)$result['result'];

        $client = new Client($bitrix_lead_id, $promo_url_param);
        $client->save();
    };

    $next_url = '/quiz-thanks.html';
}

header('Location: ' . $next_url);
die();
?>
