<?php

namespace CsvCombine\Form;

use Cake\Form\Form;

class FixedLengthImportForm extends Form
{

    /*
     * loadData 固定長読み込みアクション
     *
     * @param string $fileName 固定長テキストファイ
     * @param array $column_list 各カラム情報(name:カラム名,length:バイト数)
     * @param string $line_feed_code 改行コード(デフォルトは\r\n)
     * @param string $array_encoding 出力するする配列のエンコード(デフォルトはUTF-8
     * @param string $import_encoding 入力するテキストのエンコード(デフォルトはSJIS-win
     */
    public function loadData($fileName, $column_list, $line_feed_code = "\r\n", $array_encoding = 'utf8',$import_encoding = 'sjis-win')
    {
        $fp = fopen($fileName,'r');
        $data = fread($fp, filesize($fileName));
        fclose($fp);

        $return_info = [];
        //まずは分割
        $data_explode = explode($line_feed_code, $data);
        foreach ($data_explode as $row => $text) {
            //空行は無視
            if (strlen($text) === 0) {
                continue;
            }
            $start_point = 0;
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
