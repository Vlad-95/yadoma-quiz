<?php
include_once 'common.php';

class ClientsDb extends SQLite3 {
    function __construct() {
        $config = include('.config.php');

        $this->open($config['db_filename']);
    }

    function log_error($error_msg) {
        log_to_file($error_msg . ': ' . $this->lastErrorMsg());
    }
}

class Model {
    public $db;
    public $id = NULL;
    public $tablename;

    protected $create_table_stmt = <<<EOF
            CREATE TABLE `%s` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT
            );
EOF;

    function __construct($tablename, $id = NULL) {
        $this->db = new ClientsDb();

        $this->id = $id;

        $this->create_table_stmt = sprintf($this->create_table_stmt, $this->tablename);

        if (!$this->__check_table_exists()) {
            $this->__create_table();
        };

        $this->fetch();
    }

    function __check_table_exists() {
        $check_table_exists = <<<EOF
            SELECT 1
            FROM `$this->tablename`;
EOF;

        return $this->db->exec($check_table_exists);
    }

    function __create_table() {
        $table_created = $this->db->exec($this->create_table_stmt);

        if (!$table_created) {
            $this->db->log_error('Не удалось создать таблицу ' . $this->tablename);
        };
    }

    function __fetch_by_cond($condition) {
        $fetch_stmt = <<<EOF
            SELECT *
            FROM `$this->tablename`
            WHERE $condition
            LIMIT 1;
EOF;

        if (!$condition) {
            throw new Exception('Заполните условие для выбора элемента');
        }

        $instance_row = $this->db->query($fetch_stmt);

        return $instance_row->fetchArray();
    }

    function __fetch_by_id() {
        $cond = 'id = ' . $this->id;

        return $this->__fetch_by_cond($cond);
    }

    function fetch() {
        if ($this->id) {
            $result = $this->__fetch_by_id();
        }

        $this->populate_fields($result);
    }

    function populate_fields($fields) {
        if ($fields) {
            $this->id = $fields['id'];
        }
    }

    function exists() {
        return $this->id !== NULL;
    }
}

class Client extends Model {
    public $bitrix_lead_id;
    public $promo_url_param;
    public $promo_activated = FALSE;

    function __construct($bitrix_lead_id = NULL, $promo_url_param = NULL, $id = NULL,
            $promo_activated = FALSE) {
        if (!$bitrix_lead_id && !$promo_url_param) {
            throw new Exception('Нужно задать хотя бы один из параметров bitrix_lead_id и promo_url_param');
        }

        $this->tablename = 'Clients';
        $this->create_table_stmt = <<<EOF
            CREATE TABLE `%s` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `bitrix_lead_id` INT NOT NULL,
                `promo_url_param` TEXT NOT NULL,
                `promo_activated` BOOLEAN NOT NULL DEFAULT FALSE
            );
EOF;

        $this->bitrix_lead_id = $bitrix_lead_id;
        $this->promo_url_param = $promo_url_param;
        $this->promo_activated = $promo_activated;

        parent::__construct($id);
    }

    function save() {
        $promo_activated_str = $this->promo_activated ? '1' : '0';
        $save_stmt;

        if ($this->id) {
            $save_stmt = <<<EOF
                UPDATE `$this->tablename`
                SET `bitrix_lead_id` = $this->bitrix_lead_id,
                    `promo_url_param` = '$this->promo_url_param',
                    `promo_activated` = $promo_activated_str
                WHERE `$this->tablename`.id = $this->id;
EOF;
        } else {
            $save_stmt = <<<EOF
                INSERT INTO `$this->tablename`
                    (`bitrix_lead_id`, `promo_url_param`, `promo_activated`)
                VALUES
                    ($this->bitrix_lead_id, '$this->promo_url_param', $promo_activated_str);
EOF;
        }

        $this->db->exec($save_stmt);

        if (!$this->id) {
            $this->id = $this->db->lastInsertRowID();
        }
    }

    function __fetch_by_bitrix_lead_id() {
        $cond = '`bitrix_lead_id` = ' . $this->bitrix_lead_id;

        return $this->__fetch_by_cond($cond);
    }

    function __fetch_by_promo_url_param() {
        $cond = '`promo_url_param` = "' . $this->promo_url_param . '"';

        return $this->__fetch_by_cond($cond);
    }

    function populate_fields($fields) {
        parent::populate_fields($fields);

        if ($fields) {
            $this->bitrix_lead_id = $fields['bitrix_lead_id'];
            $this->promo_url_param = $fields['promo_url_param'];
            $this->promo_activated = $fields['promo_activated'];
        }
    }

    function fetch() {
        if ($this->id) {
            $result = $this->__fetch_by_id();
        } elseif ($this->bitrix_lead_id) {
            $result = $this->__fetch_by_bitrix_lead_id();
        } elseif ($this->promo_url_param) {
            $result = $this->__fetch_by_promo_url_param();
        } else {
            throw new Exception('Невозможно получить данные, модель Client не заполнена');
        }

        $this->populate_fields($result);
    }
}

?>
