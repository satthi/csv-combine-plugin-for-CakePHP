<?php

namespace CsvCombine\Form;

use Cake\Form\Form;

class FixedLengthImportForm extends Form
{
    private $_defaultOptions = [
        'line_feed_code' => "\r\n",
        'directory' => TMP,
        'array_encoding' => 'UTF-8',
        'import_encoding' => 'SJIS-win',
        'extra_fixed_options' => []
    ];

    /*
     * loadData 固定長読み込みアクション
     *
     * @param string $fileName 固定長テキストファイ
     * @param array $column_list 各カラム情報(name:カラム名,length:バイト数)
     * @param array $options 下記パラメータを必要に応じて設定
     * line_feed_code 改行コード(デフォルトは\r\n)
     * array_encoding 出力するする配列のエンコード(デフォルトはUTF-8
     * import_encoding 入力するテキストのエンコード(デフォルトはSJIS-win
     * extra_fixed_options 出力のための固定長の設定(列によって桁数が異なる場合の設定)
     */
    public function loadData($fileName, $fixed_options, $options = [])
    {
        $options = array_merge($this->_defaultOptions,$options);
        extract($options);

        $fp = fopen($fileName,'r');
        $data = fread($fp, filesize($fileName));
        fclose($fp);

        $return_info = [];
        //まずは分割
        $data_explode = explode($line_feed_code, $data);
        $list_count = count($data_explode);
        foreach ($data_explode as $row => $text) {
            //空行は無視
            if (strlen($text) === 0) {
                continue;
            }
            $start_point = 0;
            $column_list = $fixed_options;
            if (array_key_exists($row + 1, $extra_fixed_options)) {
                $column_list = $extra_fixed_options[$row + 1];
            } elseif (array_key_exists($row - $list_count + 1, $extra_fixed_options)) {
                $column_list = $extra_fixed_options[$row - $list_count + 1];
            }

            foreach ($column_list as $column_info) {
                $return_info[$row][$column_info['name']] = rtrim(substr($text, $start_point, $column_info['length']));
                $start_point += $column_info['length'];
            }
        }

        //最後にまとめて文字コードを変換
        mb_convert_variables($array_encoding, $import_encoding, $return_info);

        return $return_info;
    }

}
