<?php
include_once 'db.php';

class Promocode {
    function get_new_promo_url() {
        $url_generated = FALSE;
        $promo_url = '';

        while (!$url_generated) {
            $promo_url = $this->__gen_uuid4();
            $is_unique = $this->__check_uniqueness($promo_url);

            if ($is_unique) {
                $url_generated = TRUE;
            }
        }

        return $promo_url;
    }

    function __gen_uuid4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    function __check_uniqueness($promo_url) {
        $is_unique = TRUE;
        $client = new Client(NULL, $promo_url);

        if ($client->exists()) {
            $is_unique = FALSE;
        }

        return $is_unique;
    }
}

?>
