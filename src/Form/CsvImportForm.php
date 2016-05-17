<?php

namespace CsvCombine\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;
use Cake\Utility\Hash;

class CsvImportForm extends Form
{


    protected function _buildValidator(Validator $validator)
    {
        return $validator;
    }


    public function loadDataCsv($fileName, $column_list, $delimiter = ",", $array_encoding = 'utf8',$import_encoding = 'sjis-win')
    {
        //保存をするのでモデルを読み込み
        try {
            $data = array();
            $csvData = array();
            $file = fopen($fileName,"r");
            while($data = $this->fgetcsv_reg($file,65536,$delimiter)){//CSVファイルを","区切りで配列に
                mb_convert_variables($array_encoding,$import_encoding,$data);
                $csvData[] = $data;
            }

            $i = 0;
            foreach ($csvData as $line) {
                $this_data = array();
                foreach ($column_list as $k => $v) {
                    if (isset($line[$k])) {
                        //先頭と末尾の"を削除
                        $b = $line[$k];
                        //カラムの数だけセット
                        $this_data = Hash::merge(
                                        $this_data,
                                        array($v => $b)
                        );
                    } else {
                        $this_data = Hash::merge(
                                        $this_data,
                                        array($v => '')
                        );
                    }
                }

                $data[$i] = $this_data;
                $i++;
            }
        } catch (\Exception $e) {
            return false;
        }

        return $data;
    }

    /**
     * fgetcsv_reg
     *
     * this is a port of the original code written by yossy.
     *
     * @author yossy
     * @author hagiwara
     *
     * @see http://yossy.iimp.jp/wp/?p=56
     * @return array
     */
    private function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"')
    {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false; // Added for PHP Warning.
        while ( $eof != true ) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
        $_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';

        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);

        $_csv_data = $_csv_matches[1];

        for ( $_csv_i=0; $_csv_i<count($_csv_data); $_csv_i++ ) {
            $_csv_data[$_csv_i] = preg_replace('/^'.$e.'(.*)'.$e.'$/s', '$1', $_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }

}
