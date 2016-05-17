# CsvCombine plugin for CakePHP3 #

[![Build Status](https://travis-ci.org/satthi/csv-combine-plugin-for-CakePHP.svg?branch=master)](https://travis-ci.org/satthi/csv-combine-plugin-for-CakePHP)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/satthi/csv-combine-plugin-for-CakePHP/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/satthi/csv-combine-plugin-for-CakePHP/?branch=master)

PHP versions  5
CakePHP version 3

## 更新履歴 ##

* 2015/05/01 CakePHP3用に書き換え
* 2016/05/17 固定長対応追加

## 特徴 ##

* 配列 ⇔ CSV・TSVファイルを行う機能
* Cake1.3や2ではファイルのアップロードであったり、保存まで管理していたが自分で使ってなかったので削除しました・・・。
* 固定長に対応しました！

## 準備 ##

※copmposer対応しました
```
"satthi/csv-combine-plugin-for-cakephp": "*"
```

********************
※composerでインストールしないとき
pluginsディレクトリ内にCsvCombineを設置

bootstrapに以下を記述
```
Plugin::load('CsvCombine', ['autoload' => true]);
```
********************

## 使い方(CSV) ##
```php
<?php
namespace App\Controller;

use Cake\Core\Configure;

use CsvCombine\Form\CsvImportForm;

class CsvController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('CsvCombine.CsvExport');
    }
    public function export()
    {
        $list = [
            [
                'test1',
                'test2',
                'test3',
            ],
            [
                'test4',
                'test5',
                'test6',
            ],
        ];
        /*
         *@array $list 出力のための配列(二次元配列が基本)
         *@param $file_name 出力ファイル名(デフォルトはexport.csv)
         *@param $delimiter 区切り文字の設定(デフォルトは",")
         *@param $directory 一時保存ディレクトリ(デフォルトはTMP,最終的にファイルを削除をする)
         *@param $export_encoding 出力するファイルのエンコード(デフォルトはSJIS-win
         *@param $array_encoding 入力する配列のエンコード(デフォルトはUTF-8
        */
        return $this->CsvExport->export($list);
    }

    public function import()
    {
        $import = new CsvImportForm();
        $file = TMP . 'test.csv';
        $column = [
            'key1',
            'key2',
            'key3',
        ];
        /*
         *@array file ファイルパス(必須
         *@array $column カラム名を並び順に(必須
         *@param $delimiter 区切り文字を設定 (デフォルトは","で"\t"や"|"などを指定することが可能)
         *@param $column_name カラム名を設定
         *@param $array_encoding 出力する配列のエンコード(デフォルトはUTF-8
         *@param $import_encoding 入力するファイルのエンコード(デフォルトはSJIS-win
        */
        $result = $import->loadDataCsv($file,$column);
        debug($result);
        exit;
    }
}

```

## 使い方(固定長) ##

```php
<?php
namespace App\Controller;

use Cake\Core\Configure;

use CsvCombine\Form\FixedLengthImportForm;

class CsvController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('CsvCombine.FixedLengthExport');
    }

    public function export()
    {
        $list = [
            [
                'あいう',
                'いいい',
                'uuu',
            ],
            [
                'あいう',
                'いいい',
                'uuu',
            ],
            [
                'あいう',
                'いいい',
                'uuu',
            ],
        ];
        $fixed_options = [
            8,
            10,
            6
        ];
        //makeでファイル作成のみ
        /*
         * export 固定長の出力アクション
         *
         * @array $list 出力のための配列(二次元配列が基本)
         * @array $fixed_options 出力のための固定長の設定(各カラムのバイト数)
         * @param $file_name 出力ファイル名(デフォルトはexport.txt)
         * @param $line_feed_code 改行コード(デフォルトは\r\n)
         * @param $directory 一時保存ディレクトリ(デフォルトはTMP,最終的に削除をする)
         * @param $export_encoding 出力するファイルのエンコード(デフォルトはSJIS-win
         * @param $array_encoding 入力する配列のエンコード(デフォルトはUTF-8
         */

        //$this->FixedLengthExport->make($list,$fixed_options);
        $this->FixedLengthExport->export($list,$fixed_options);

    }

    public function import()
    {
        $filename = TMP . 'test.txt';
        $column_list = [
            ['name' => 'column1', 'length' => 8],
            ['name' => 'column2', 'length' => 10],
            ['name' => 'column3', 'length' => 6],
        ];
        $import = new FixedLengthImportForm();
        /*
         * @text $fileName 固定長テキストファイ
         * @array $column_list 各カラム情報(name:カラム名,length:バイト数)
         * @param $line_feed_code 改行コード(デフォルトは\r\n)
         * @param $array_encoding 出力するする配列のエンコード(デフォルトはUTF-8
         * @param $import_encoding 入力するテキストのエンコード(デフォルトはSJIS-win
         */
        $result = $import->loadData($filename, $column_list);
        debug($result);
        exit;
    }
}

```

## License ##

The MIT Lisence

Copyright (c) 2011 Fusic Co., Ltd. (http://fusic.co.jp)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Author ##

Satoru Hagiwara
