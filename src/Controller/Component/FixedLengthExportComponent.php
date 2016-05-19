<?php
namespace CsvCombine\Controller\Component;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\MethodNotAllowedException;


/**
 * FixedLengthExportComponent  code license:
 *
 * @copyright Copyright (C) 2011 hagiwara.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class FixedLengthExportComponent extends Component {

    private $_controller;

    /**
     * コンポーネント初期化
     *
     * @access public
     */
    public function startup(Event $event) {
        $this->_controller = $event->subject();
    }

    /*
     * export 固定長の出力アクション
     *
     * @param array $list 出力のための配列(二次元配列が基本)
     * @param array $fixed_options 出力のための固定長の設定(各カラムのバイト数)
     * @param string $file_name 出力ファイル名(デフォルトはexport.txt)
     * @param string $line_feed_code 改行コード(デフォルトは\r\n)
     * @param string $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     * @param string $export_encoding 出力するファイルのエンコード(デフォルトはSJIS-win
     * @param string $array_encoding 入力する配列のエンコード(デフォルトはUTF-8
     */
    public function export($list, $fixed_options, $file_name = 'export.txt', $line_feed_code = "\r\n", $directory = TMP,$export_encoding = 'SJIS-win',$array_encoding = 'UTF-8')
    {
        //layoutを切って autoRenderも外しておく
        $this->_controller->viewBuilder()->layout('ajax');
        $this->_controller->autoRender = false;

        //headerのセット
        $save_directory = $this->make($list, $fixed_options, $file_name , $line_feed_code, $directory ,$export_encoding ,$array_encoding);
        $basename = basename($save_directory);
        $filesize = filesize($save_directory);
        $this->_controller->response->header('Content-Disposition', 'attachment; filename="' . $basename . '"');
        $this->_controller->response->type('application/octet-stream');
        $this->_controller->response->header('Content-Transfer-Encoding', 'binary');
        $this->_controller->response->header('Content-Length', $filesize);

        readfile($save_directory);

        unlink($save_directory);
    }

    /*
     * export 固定長の作成アクション
     *
     * @param array $list 出力のための配列(二次元配列が基本)
     * @param array $fixed_options 出力のための固定長の設定(各カラムのバイト数)
     * @param string $file_name 出力ファイル名(デフォルトはexport.txt)
     * @param string $line_feed_code 改行コード(デフォルトは\r\n)
     * @param string $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     * @param string $export_encoding 出力するファイルのエンコード(デフォルトはSJIS-win
     * @param string $array_encoding 入力する配列のエンコード(デフォルトはUTF-8
     */

    public function make($list, $fixed_options, $file_name = 'export.txt', $line_feed_code = "\r\n",$directory = TMP,$export_encoding = 'SJIS-win',$array_encoding = 'UTF-8')
    {
        Configure::write('debug', 0);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        mb_convert_variables($export_encoding, $array_encoding, $list);
        //$listにカンマか"がいた時の対応
        $return_text = '';
        foreach ($list as $row => $list_val) {
            foreach ($fixed_options as $fixed_option_key => $fixed_length) {
                if (!array_key_exists($fixed_option_key, $list_val)) {
                    //必要なデータが存在しないエラー
                    throw new MethodNotAllowedException('data not exist');
                } else if (strlen($list_val[$fixed_option_key]) > $fixed_length) {
                    throw new MethodNotAllowedException('length error');
                }
                $return_text .= str_pad($list_val[$fixed_option_key], $fixed_length);
            }
            $return_text .= $line_feed_code;
        }

        $save_directory = $directory . $file_name;
        $fp = fopen($save_directory, 'w');
        fwrite($fp, $return_text);

        fclose($fp);

        return $save_directory;
    }

}
