<?php

/**
  CsvExportComponent  code license:
 *
 * @copyright Copyright (C) 2011 hagiwara.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class CsvExportComponent extends Component {

    var $_controller;

    /**
     * コンポーネント初期化
     * 
     * @access public
     */
    function startup(& $controller) {
        $this->_controller = $controller;
    }

    /*
     * export CSVの出力アクション
     *
     * @array $list 出力のための配列(二次元配列が基本)
     * @param $file_name 出力ファイル名(デフォルトはexport.csv)
     * @param $delimiter 区切り文字の設定(デフォルトは",")
     * @param $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     */

    function export($list, $file_name = 'export.csv', $delimiter = ",", $directory = TMP) {
        $this->layout = null;
        Configure::write('debug', 0);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $csv_list = array();
        mb_convert_variables('SJIS-win', 'UTF-8', $list);
        //$listにカンマか"がいた時の対応
        if (isset($list)) {
            if (is_array($list)) {
                foreach ($list as $k => $list1) {
                    if (is_array($list1)) {
                        foreach ($list1 as $m => $v) {
                            if (is_array($v)){
                                //3次元以上の配列の時はエラー
                                echo 'error';
                                exit;
                            }
                            $csv_list[$k][$m] = $this->_parseCsv($v, $delimiter);
                        }
                    } else {
                        //1次元の時は1列目に値が入る。
                        $csv_list[0][$k] = $this->_parseCsv($list1, $delimiter);
                    }
                }
            } else {
                //文字列の時は1カラムに値が入るだけ。
                $csv_list[0][0] = $this->_parseCsv($list, $delimiter);
            }
        }

        $save_directory = $directory . $file_name;
        $fp = fopen($save_directory, 'w');
        foreach ($csv_list as $fields) {
            fputs($fp, implode($delimiter, $fields) . "\r\n");
        }

        fclose($fp);

        header('Content-Disposition: attachment; filename="' . basename($save_directory) . '"');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($save_directory));
        readfile($save_directory);

        unlink($save_directory);

        exit;
    }

    /*
     * _parseCsv
     * csv(など)の形式に変更
     *
     * @param $v 変換する値
     * @delimiter 区切り文字
     */
    function _parseCsv($v, $delimiter) {
        if (preg_match('/[' . $delimiter . '"]/', $v)) {
            $v = str_replace('"', '""', $v);
            $v = '"' . $v . '"';
        }
        return $v;
    }

}

?>
