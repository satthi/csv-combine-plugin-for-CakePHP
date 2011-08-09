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
     * @param $colimn_list カラム名を並び順に(必須
     * @bool $clear_flag DBを初期化するかどうか。(デフォルトは初期化しない)
     * @param $file_type ファイルタイプ（CSVかTSVか）
     * @param $conditions 初期化条件　初期化条件がある場合は設定可能。(一部データだけを削除する場合など)
     * @param $column_name カラム名を設定
     */

    function csvSave(&$model, $column_list = array(), $clear_flag = false, $file_type = 'csv', $conditions = array(),$column_name = 'csv') {
        //データやカラムリストがない場合はfalse
        if ($column_list == array()) {
            return false;
        }
        //現在はCSV(カンマ区切り)とTSV(タブ区切り)に対応
        if ($file_type != 'csv' && $file_type != 'tsv') {
            return false;
        }
        $params = Router::getParams();
        //$this->dataの中身を取得
        if (empty($params['data'])) {
            return false;
        }
        $data = $params['data'];
        //モデル名が設定されてないときはコントローラ名からモデル名を取得

        ini_set("memory_limit", -1);
        set_time_limit(0);
        $up_file = $data[$model->alias][$column_name]['tmp_name'];
        //拡張子チェック
        if (pathinfo($data[$model->alias][$column_name]['name'], PATHINFO_EXTENSION) != $file_type) {
            return false;
        }
        $fileName = $this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' .$file_type;
        if (is_uploaded_file($up_file)) {
            move_uploaded_file($up_file, $fileName);
            //データが保存できた時
            if ($this->_loadFormCsv($model, $fileName, $column_list, $clear_flag, $file_type, $conditions)) {
                unlink($this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' .$file_type);
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
     * @param $colimn_list カラム名を並び順に(必須
     * @param $file_type ファイルタイプ（CSVかTSVか）
     * @param $column_name カラム名を設定
     */

    function csvData(&$model, $column_list = array(), $file_type = 'csv',$column_name = 'csv') {
        //データやカラムリストがない場合はfalse
        if ($column_list == array()) {
            return false;
        }
        //現在はCSV(カンマ区切り)とTSV(タブ区切り)に対応
        if ($file_type != 'csv' && $file_type != 'tsv') {
            return false;
        }
        $params = Router::getParams();
        //$this->dataの中身を取得
        if (empty($params['data'])) {
            return false;
        }
        $data = $params['data'];
        //モデル名が設定されてないときはコントローラ名からモデル名を取得
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $up_file = $data[$model->alias][$column_name]['tmp_name'];
        //拡張子チェック
        if (pathinfo($data[$model->alias][$column_name]['name'], PATHINFO_EXTENSION) != $file_type) {
            return false;
        }
        $fileName = $this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' .$file_type;
        if (is_uploaded_file($up_file)) {
            move_uploaded_file($up_file, $fileName);
            //データが保存できた時
            $data = $this->_loadDataCsv($model, $fileName, $column_list, $file_type);
            unlink($this->settings[$model->alias]['csv_directory'] . $this->settings[$model->alias]['csv_path'] . '.' .$file_type);
            return $data;
        } else {
            return false;
        }
    }

    /*
     * _loadFormCsv
     *
     * @param $fileName ファイル名
     * @param $colimn_list カラムリスト
     * @bool $clear_flag 初期化フラグ
     * @param $file_type ファイルタイプ
     * @param $file_type 初期化条件
     */

    private function _loadFormCsv($model, $fileName, $column_list, $clear_flag, $file_type, $conditions) {
        //保存をするのでモデルを読み込み
        $instance = ClassRegistry::init($model->alias);
        try {
            $csvData = file($fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            //配列の中身をまとめて文字コード変換
            //Csv、Tsvの文字コードがSJISなので変換しないと文字化け。
            mb_convert_variables('UTF-8', 'SJIS-win', $csvData);
            $i = 0;
            foreach ($csvData as $line) {
                if ($file_type == 'csv') {
                    $record = explode(",", $line);
                } elseif ($file_type == 'tsv') {
                    $record = explode("\t", $line);
                } else {
                    return false;
                }
                $this->data[$model->alias] = array();
                foreach ($column_list as $k => $v) {
                    if (!empty($record[$k])) {
                        //先頭と末尾の"を削除
                        $b = preg_replace('/^\"/', '', $record[$k]);
                        $b = preg_replace('/\"$/', '', $b);
                        //カラムの数だけセット
                        $this->data[$model->alias] = Set::merge(
                                        $this->data[$model->alias],
                                        array($v => $b)
                        );
                     }else{
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
                    if (!$instance->deleteAll(array('id >=' => 1))) {
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
     * @param $colimn_list カラムリスト
     * @param $file_type ファイルタイプ
     */

    private function _loadDataCsv($model, $fileName, $column_list, $file_type) {
        //保存をするのでモデルを読み込み
        $instance = ClassRegistry::init($model->alias);
        try {
            $csvData = file($fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            //配列の中身をまとめて文字コード変換
            //Csv、Tsvの文字コードがSJISなので変換しないと文字化け。
            mb_convert_variables('UTF-8', 'SJIS-win', $csvData);
            $i = 0;
            foreach ($csvData as $line) {
                if ($file_type == 'csv') {
                    $record = explode(",", $line);
                } elseif ($file_type == 'tsv') {
                    $record = explode("\t", $line);
                } else {
                    return false;
                }
                $this->data[$model->alias] = array();
                foreach ($column_list as $k => $v) {
                    if (!empty($record[$k])) {
                        //先頭と末尾の"を削除
                        $b = preg_replace('/^\"/', '', $record[$k]);
                        $b = preg_replace('/\"$/', '', $b);
                        //カラムの数だけセット
                        $this->data[$model->alias] = Set::merge(
                                        $this->data[$model->alias],
                                        array($v => $b)
                        );
                    }else{
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

}

