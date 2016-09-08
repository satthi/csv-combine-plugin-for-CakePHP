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
    private $_defaultOptions = [
        'file_name' => 'export.txt',
        'line_feed_code' => "\r\n",
        'directory' => TMP,
        'export_encoding' => 'SJIS-win',
        'array_encoding' => 'UTF-8',
        'extra_fixed_options' => []
    ];
    private $_textData = '';

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
     * @param array $fixed_options 出力のための固定長の設定(各カラムのバイト数及び型)
     * @param array $options 下記パラメータを必要に応じて設定
     * file_name 出力ファイル名(デフォルトはexport.txt)
     * line_feed_code 改行コード(デフォルトは\r\n)
     * $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     * export_encoding 出力するファイルのエンコード(デフォルトはSJIS-win
     * array_encoding 入力する配列のエンコード(デフォルトはUTF-8
     * extra_fixed_options 出力のための固定長の設定(列によって桁数が異なる場合の設定)
     */
    public function export($list, $fixed_options, $options)
    {
        $options = array_merge($this->_defaultOptions,$options);
        extract($options);

        //layoutを切って autoRenderも外しておく
        $this->_controller->viewBuilder()->layout('ajax');
        $this->_controller->autoRender = false;

        //headerのセット
        $save_directory = $this->make($list, $fixed_options, $options);
        $basename = basename($save_directory);
        $this->_controller->response->file($save_directory, ['download' => true, 'name' => $basename]);
    }

    /*
     * make 固定長の作成アクション
     *
     * @param array $list 出力のための配列(二次元配列が基本)
     * @param array $fixed_options 出力のための固定長の設定(各カラムのバイト数)
     * @param array $options 下記パラメータを必要に応じて設定
     * file_name 出力ファイル名(デフォルトはexport.txt)
     * line_feed_code 改行コード(デフォルトは\r\n)
     * $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
     * export_encoding 出力するファイルのエンコード(デフォルトはSJIS-win
     * array_encoding 入力する配列のエンコード(デフォルトはUTF-8
     * extra_fixed_options 出力のための固定長の設定(列によって桁数が異なる場合の設定)
     */
    public function make($list, $fixed_options, $options)
    {
        Configure::write('debug', 0);
        ini_set("memory_limit", -1);
        set_time_limit(0);

        $this->_textData = '';
        $options = array_merge($this->_defaultOptions,$options);
        extract($options);

        mb_convert_variables($export_encoding, $array_encoding, $list);

        // keyを振りなおしておく。
        $list = array_merge($list);
        $list_count = count($list);
        //$listにカンマか"がいた時の対応
        $return_text = '';
        foreach ($list as $row => $list_val) {
            $column_options = $fixed_options;
            if (array_key_exists($row + 1, $extra_fixed_options)) {
                $column_options = $extra_fixed_options[$row + 1];
            } elseif (array_key_exists($row - $list_count, $extra_fixed_options)) {
                $column_options = $extra_fixed_options[$row - $list_count];
            }

            foreach ($column_options as $fixed_option_key => $fixed_info) {
                if (!array_key_exists($fixed_option_key, $list_val)) {
                    //必要なデータが存在しないエラー
                    throw new MethodNotAllowedException('data not exist');
                } else if (strlen($list_val[$fixed_option_key]) > $fixed_info['length']) {
                    throw new MethodNotAllowedException('length error');
                }

                if ($fixed_info['type'] == 'text') {
                    $return_text .= str_pad($list_val[$fixed_option_key], $fixed_info['length']);
                } elseif ($fixed_info['type'] == 'integer') {
                    $return_text .= sprintf('%0' . $fixed_info['length'] . 's', ($list_val[$fixed_option_key]));
                } else {
                    throw new MethodNotAllowedException('type error');
                }
            }
            $return_text .= $line_feed_code;
        }

        $this->_textData = $return_text;
        $save_directory = $directory . $file_name;
        $fp = fopen($save_directory, 'w');
        fwrite($fp, $return_text);

        fclose($fp);

        return $save_directory;
    }

    /*
     * getRawData ファイルに出力した生テキストデータを取得
     */
    public function getRawData()
    {
        return $this->_textData;
    }
}
