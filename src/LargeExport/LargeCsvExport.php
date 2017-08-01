<?php
namespace CsvCombine\LargeExport;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Utility\Security;
use Cake\Filesystem\File;

/**
 * LargeCsvExport  code license:
 *
 * @copyright Copyright (C) 2011 hagiwara.
 * @since CakePHP(tm) v 1.3
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class LargeCsvExport {

    private $tmpCsvFp;
    private $defaultSettings = [
        'delimiter' => ',',
        'export_encoding' => 'SJIS-win',
        'array_encoding' => 'UTF-8',
    ];
    private $settings;

    /**
     * __construct
     *
     */
    public function __construct($settings = [])
    {
        $this->settings = array_merge(
            $this->defaultSettings,
            $settings
        );
        $this->tmpCsvFp = new File($this->getTmpFileName());
    }

    private function getTmpFileName()
    {
        $tmpFileName = TMP . 'csv_file_' . Security::hash(time() . rand());
        // ファイルが存在した場合は何もしない
        if (file_exists($tmpFileName)) {
            return $this->getTmpFileName();
        }
        return $tmpFileName;
    }


    public function addRow($lists)
    {
        if (!is_array($lists)) {
            throw new MethodNotAllowedException('$list must be array.');
        }
        $csvRow = $this->parseCsv($lists);
        $this->tmpCsvFp->write($csvRow, 'a');
    }

    public function read()
    {
        $csvText = $this->tmpCsvFp->read();
        // ファイル削除
        $this->tmpCsvFp->delete();
        $this->tmpCsvFp->close();
        return $csvText;
    }

    /*
     * _parseCsv
     * csv(など)の形式に変更
     *
     * @param string $v 変換する値
     * @param string $delimiter 区切り文字
     */
    private function parseCsv($lists)
    {
        // 文字コードの変換
        mb_convert_variables($this->settings['export_encoding'], $this->settings['array_encoding'], $lists);
        foreach ($lists as $listKey => $list) {
            //区切り文字・改行・ダブルクオートの時
            if (preg_match('/[' . $this->settings['delimiter'] . '\\n"]/', $list)) {
                $list = str_replace('"', '""', $list);
                $lists[$listKey] = '"' . $list . '"';
            }
        }
        // カンマ区切り
        return implode($this->settings['delimiter'], $lists) . "\r\n";
    }

}
