<?php

/**
 * CsvImportBehavior  code license:
 *
 * @copyright Copyright (C) 2011 hagiwara.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class CsvImportBehavior extends ModelBehavior {
    const CSV_IMPORT_FILE_PATH = TMP;
    const CSV_IMPORT_FILE_NAME = 'csv_import_upload';

    var $_defaults = array(
        'csv_directory' => self::CSV_IMPORT_FILE_PATH,
        'csv_path' => self::CSV_IMPORT_FILE_NAME
    );

    function setup(&$model, $config = array()) {
        $settings = array_merge($this->_defaults, $config);
        $this->settings[$model->alias] = $settings;
    }

    /*
     * csvSave
     *
     * @array $colimn_list カラム名を並び順に(必須
     * @bool $clear_flag DBを初期化するかどうか。(デフォルトは初期化しない)
     * @param $delimiter 区切り文字を設定 (デフォルトは","で"\t"や"|"などを指定することが可能)
     * @array $conditions 初期化条件　初期化条件がある場合は設定可能。(一部データだけを削除する場合など)
     * @param $column_name カラム名を設定
     * @param $array_encoding 出力する配列のエンコード(デフォルトはUTF-8
     * @param $import_encoding 入力するファイルのエンコード(デフォルトはSJIS-win
     */

    function csvSave(&$model, $column_list = array(), $clear_flag = false, $delimiter = ",", $conditions = array(), $column_name = 'csv',$array_encoding = 'UTF-8',$import_encoding = 'SJIS-win') {
        //データやカラムリストがない場合はfalse
        if ($column_list == array()) {
            return false;
        }
        $params = Router::getRequest();
        //$this->dataの中身を取得
        if (!isset($params->data)) {
            return false;
        }
        $data = $params->data;
        //モデル名が設定されてないときはコントローラ名からモデル名を取得

        ini_set("memory_limit", -1);
        set_time_limit(0);
        $up_file = $data[$model->alias][$column_name]['tmp_name'];
        $ext = pathinfo($data[$model->alias][$column_name]['name']);
        if (empty($ext)) {
            $ext = 'txt';
        }
        $fileName = $this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' . $ext;
        if (is_uploaded_file($up_file)) {
            move_uploaded_file($up_file, $fileName);
            //データが保存できた時
            if ($this->_loadFormCsv($model, $fileName, $column_list, $clear_flag, $conditions, $delimiter,$array_encoding,$import_encoding)) {
                unlink($this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' . $ext);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * csvData
     *
     * @array $colimn_list カラム名を並び順に(必須
     * @param $delimiter 区切り文字を設定 (デフォルトは","で"\t"や"|"などを指定することが可能)
     * @param $column_name カラム名を設定
     * @param $array_encoding 出力する配列のエンコード(デフォルトはUTF-8
     * @param $import_encoding 入力するファイルのエンコード(デフォルトはSJIS-win
     */

    function csvData(&$model, $column_list = array(), $delimiter = ",", $column_name = 'csv',$array_encoding = 'UTF-8',$import_encoding = 'SJIS-win') {
        $params = Router::getRequest();
        //$this->dataの中身を取得
        if (!isset($params->data)) {
            return false;
        }
        $data = $params->data;
        //モデル名が設定されてないときはコントローラ名からモデル名を取得
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $up_file = $data[$model->alias][$column_name]['tmp_name'];
        $ext = pathinfo($data[$model->alias][$column_name]['name']);
        if (empty($ext)) {
            $ext = 'txt';
        }
        $fileName = $this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' . $ext;
        if (is_uploaded_file($up_file)) {
            move_uploaded_file($up_file, $fileName);
            //データが保存できた時
            $data = $this->_loadDataCsv($model, $fileName, $column_list, $delimiter,$array_encoding,$import_encoding);
            unlink($this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' . $ext);
            return $data;
        } else {
            return false;
        }
    }

    /*
     * _loadFormCsv
     *
     * @param $fileName ファイル名
     * @array $colimn_list カラムリスト
     * @bool $clear_flag 初期化フラグ
     * @array $conditions 初期化条件
     * @param $delimiter 区切り文字
     * @param $array_encoding
     * @param $import_encoding
     */

    private function _loadFormCsv($model, $fileName, $column_list, $clear_flag, $conditions, $delimiter,$array_encoding,$import_encoding) {
        //保存をするのでモデルを読み込み
        $instance = ClassRegistry::init($model->alias);
        try {
            $csvData = file($fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            //配列の中身をまとめて文字コード変換
            //Csv、Tsvの文字コードがSJISなので変換しないと文字化け。
            mb_convert_variables($array_encoding, $import_encoding, $csvData);
            $i = 0;
            foreach ($csvData as $line) {
                $record = $this->parseCSV($line, $delimiter);

                $this->data[$model->alias] = array();
                foreach ($column_list as $k => $v) {
                    if (isset($record[$k])) {
                        //先頭と末尾の"を削除
                        $b = $record[$k];
                        $this->data[$model->alias] = Set::merge(
                                        $this->data[$model->alias],
                                        array($v => $b)
                        );
                    } else {
                        $this->data[$model->alias] = Set::merge(
                                        $this->data[$model->alias],
                                        array($v => '')
                        );
                    }
                }

                $instance->set($this->data[$model->alias]);
                //バリデーションが必要であればモデルにセットする
                if ($instance->validates()) {
                    $data[$i] = $this->data[$model->alias];
                } else {
                    return false;
                }
                $i++;
            }
            //初期化フラグがたっていたら初期化
            if ($clear_flag == true) {
                if (empty($conditions)) {
                    //要は全部削除する
                    if (!$instance->deleteAll(array($model->alias . '.id >=' => 1))) {
                        return false;
                    }
                } else {
                    //条件が設定されていたらその条件の絡むだけを削除する
                    if (!$instance->deleteAll($conditions)) {
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }
        if (!empty($data)) {
            $instance->saveAll($data);
            return true;
        } else {
            return false;
        }
    }

    /*
     * _loadDataCsv
     *
     * @param $fileName ファイル名
     * @array $colimn_list カラムリスト
     * @param $delimiter 区切り文字
     * @param $array_encoding
     * @param $import_encoding
     */

    private function _loadDataCsv($model, $fileName, $column_list, $delimiter,$array_encoding,$import_encoding) {
        //保存をするのでモデルを読み込み
        $instance = ClassRegistry::init($model->alias);
        try {
            $csvData = file($fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            //配列の中身をまとめて文字コード変換
            //Csv、Tsvの文字コードがSJISなので変換しないと文字化け。
            mb_convert_variables($array_encoding, $import_encoding, $csvData);
            $i = 0;
            foreach ($csvData as $line) {
                $record = $this->parseCSV($line, $delimiter);

                $this->data[$model->alias] = array();
                foreach ($column_list as $k => $v) {
                    if (isset($record[$k])) {
                        //先頭と末尾の"を削除
                        $b = $record[$k];
                        //カラムの数だけセット
                        $this->data[$model->alias] = Set::merge(
                                        $this->data[$model->alias],
                                        array($v => $b)
                        );
                    } else {
                        $this->data[$model->alias] = Set::merge(
                                        $this->data[$model->alias],
                                        array($v => '')
                        );
                    }
                }

                $instance->set($this->data[$model->alias]);
                //バリデーションが必要であればモデルにセットする
                $data[$i] = $this->data[$model->alias];
                $i++;
            }
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }

    /**
     * parseCSV
     *
     * this is a port of the original code written by yossy.
     *
     * @author yossy
     * @author hagiwara
     *
     * @see http://yossy.iimp.jp/wp/?p=56
     * @return array
     */
    private function parseCSV($line, $delimiter) {
        $pattern = '/(?:^|' . $delimiter . ')(?:"((?:[^"]|"")*)"|([^' . $delimiter . '"]*))/';
        preg_match_all($pattern, $line, $matches);
        $ret_array = array();
        for ($i = 0; $i < count($matches[0]); $i++) {
            if (isset($matches[1][$i]) && $matches[1][$i] !== '') {
                $ret_array[] = preg_replace('/""/', '"', $matches[1][$i]);
            } elseif (isset($matches[2][$i]) && $matches[2][$i] !== '') {
                $ret_array[] = $matches[2][$i];
            } else {
                //空は詰めるわけではないし、必要なカラムかどうかの判別はここではない。
                $ret_array[] = '';
            }
        }
        return $ret_array;
    }

}

