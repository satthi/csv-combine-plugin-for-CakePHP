# CsvCombine plugin for CakePHP VERSION 0.3#
PHP versions  5
CakePHP version 1.3

## 更新履歴 ##

* 2011/09/01 ver 0.2 :拡張子の指定を完全に外して、区切り文字で指定するよう変更。タブ区切りのtxtファイルなども使用可能に。

* 2011/10/06 ver 0.3 :プラグイン名を変更、CSV出力に対応

## 特徴 ##

* CSVやTSVファイルをアップロードしてDBにデータを保存したり、配列を返したりする。

## 準備 ##

app/plugin内にcsv_combineフォルダを設置

CSV入力時にはapp_modelもしくは該当モデルに以下の記述

behavior宣言の後ろの配列は記載しなくても問題なし

TMP . csv_import_upload.[拡張子]にファイルが一時的に生成されては困るときのみ設定

いずれにしても最終的にはファイルは削除されることになる。


    <?php
        class AppModel extends Model {
            var $actsAs = array(
                'CsvImport.CsvImport' =>
                array(
                    'csv_directory' => TMP,
                    'csv_path' => 'test'
                    )
            );
        }

## 使い方 ##
##コントローラ##

※DBにデータを保存※

基本的には$list(カラム名の配列)を引数にcsvSaveメソッドを呼び出す。

csvSaveの引数は以下の通り。

@array $colimn_list カラム名を並び順に(必須

@bool $clear_flag DBを初期化するかどうか。(デフォルトは初期化しない)

@param $delimiter 区切り文字を設定 (デフォルトは","で"\t"や"|"などを指定することが可能)

@array $conditions 初期化条件　初期化条件がある場合は設定可能。(一部データだけを削除する場合など)

@param $column_name カラム名を設定


    <?php
    class CsvTestsController extends AppController {
        var $name = 'CsvTests';
        var $helpers = array('CsvImport.CsvUpload');
        function index() {
            if (!empty($this->data)) {
                $list = array('body','title');
                $this->CsvTest->begin();
                if ($this->CsvTest->csvSave($list,true,",",array('id' < 5))) {
                    $this->CsvTest->commit();
                    print_r('OK');
                    exit;
                } else {
                    $this->CsvTest->rollback();
                    print_r('NG');
                    exit;
                }
            }
        }
    }

※配列を返す※

基本的には$list(カラム名の配列)を引数にcsvDataメソッドを呼び出す。

csvDataの引数は以下の通り。

@array $colimn_list カラム名を並び順に(必須

@param $delimiter 区切り文字を設定 (デフォルトは","で"\t"や"|"などを指定することが可能)

@param $column_name カラム名を設定


    <?php
    class CsvTestsController extends AppController {
        var $name = 'CsvTests';
        var $helpers = array('CsvImport.CsvUpload');
        function index() {
            if (!empty($this->data)) {
                $list = array('body', 'title');
                $data = $this->CsvTest->csvData($list, "\t");
                if ($data === false) {
                    print_r('MISS');
                    exit;
                }
                print_r($data);
                exit;
            }
        }
    }



※CSVを出力する※

CsvImport.CsvExportのコンポーネントを呼び出す

基本的には$list(出力する配列)を引数にexportメソッドを呼び出す。

exportの引数は以下の通り。

@array $list 出力のための配列(二次元配列が基本)

@param $file_name 出力ファイル名(デフォルトはexport.csv)

@param $delimiter 区切り文字の設定(デフォルトは",")

@param $directory 一時保存ディレクトリ(デフォルトはTMP,最終的にファイルを削除をする)


?php

class CsvTestsController extends AppController {

    var $name = 'CsvTests';
    var $components = array('CsvImport.CsvExport');

    function index() {
        $list[] = array(
            'a,a"a', 'bbb', 'ccc'
        );
        $list[] = array(
            'ddd', '', 'fff'
        );
        $this->CsvExport->export($list);
        
    }
}


##ビュー##
フォームが自動で生成される。

実際にはこのヘルパーは使用しなくてよい。

本当にさくっとデータ移行をしたい時に使用するとよいかも。


    <?php 
        echo $this->CsvUpload->form()
    ;?>




## License ##

The MIT Lisence

Copyright (c) 2011 Fusic Co., Ltd. (http://fusic.co.jp)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Author ##

Satoru Hagiwara
