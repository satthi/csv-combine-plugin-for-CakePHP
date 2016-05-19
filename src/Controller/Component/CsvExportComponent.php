<?php
namespace CsvCombine\Controller\Component;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * CsvExportComponent  code license:
 *
 * @copyright Copyright (C) 2011 hagiwara.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class CsvExportComponent extends Component {

    private $_controller;

    /**
     * コンポーネント初期化
     *
     * @access public
     */
    public function startup(Event $event)
    {
        $this->_controller = $event->subject();
    }

    /*
     * export CSVの出力アクション
     *
     * @param array $list 出力のための配列(二次元配列が基本)
     * @param string $file_name 出力ファイル名(デフォルトはexport.csv)
     * @param string $delimiter 区切り文字の設定(デフォルトは",")
     * @param string $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     * @param string $export_encoding 入力するファイルのエンコード(デフォルトはSJIS-win
     * @param string $array_encoding 出力する配列のエンコード(デフォルトはUTF-8
     */
    public function export($list, $file_name = 'export.csv', $delimiter = ",", $directory = TMP,$export_encoding = 'SJIS-win',$array_encoding = 'UTF-8')
    {
        //layoutを切って autoRenderも外しておく
        $this->_controller->viewBuilder()->layout('ajax');
        $this->_controller->autoRender = false;

        //headerのセット
        $basename = basename($save_directory);
        $filesize = filesize($save_directory);
        $this->_controller->response->header('Content-Disposition', 'attachment; filename="' . $basename . '"');
        $this->_controller->response->type('application/octet-stream');
        $this->_controller->response->header('Content-Transfer-Encoding', 'binary');
        $this->_controller->response->header('Content-Length', $filesize);

        $save_directory = $this->make($list, $file_name , $delimiter , $directory ,$export_encoding ,$array_encoding);
        readfile($save_directory);

        unlink($save_directory);
    }

    /*
     * make CSVの生成アクション
     *
     * @param array $list 出力のための配列(二次元配列が基本)
     * @param string $file_name 出力ファイル名(デフォルトはexport.csv)
     * @param string $delimiter 区切り文字の設定(デフォルトは",")
     * @param string $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     * @param string $export_encoding 入力するファイルのエンコード(デフォルトはSJIS-win
     * @param string $array_encoding 出力する配列のエンコード(デフォルトはUTF-8
     */
    public function make($list, $file_name = 'export.csv', $delimiter = ",", $directory = TMP,$export_encoding = 'SJIS-win',$array_encoding = 'UTF-8')
    {
        Configure::write('debug', 0);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $csv_list = array();
        mb_convert_variables($export_encoding, $array_encoding, $list);
        //$listにカンマか"がいた時の対応
        if (isset($list)) {
            if (is_array($list)) {
                foreach ($list as $k => $list1) {
                    if (is_array($list1)) {
                        foreach ($list1 as $m => $v) {
                            if (is_array($v)){
                                //3次元以上の配列の時はエラー
                                throw new MethodNotAllowedException('array layer error');
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

        return $save_directory;
    }

    /*
     * _parseCsv
     * csv(など)の形式に変更
     *
     * @param string $v 変換する値
     * @param string $delimiter 区切り文字
     */
    private function _parseCsv($v, $delimiter)
    {
        //区切り文字・改行・ダブルクオートの時
        if (preg_match('/[' . $delimiter . '\\n"]/', $v)) {
            $v = str_replace('"', '""', $v);
            $v = '"' . $v . '"';
        }
        return $v;
    }

}
