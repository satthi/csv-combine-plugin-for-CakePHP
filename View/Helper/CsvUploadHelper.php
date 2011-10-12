<?php
class CsvUploadHelper extends AppHelper {

    public $helpers = array('Form');

    /*
     * アップロード用のフォーム作成
     * 基本のものしか作成しないので変更したいときはhelperを使わず、直接設定をする。
     * その際、モデル名を「Csv」、ファイル名を「csv」に設定する。
     *
     * @param model_name フォームのモデル名
     */
    function form($model_name = null,$url = array()) {
        if (!$model_name){
            $model_name = Inflector::classify($this->params['controller']);
        }
        if (!$url){
            $url = array('controller' => $this->params['controller'],'action' => $this->action);
        }
        $form = '';
        $form .= $this->Form->create($model_name,array('url' => $url, 'type' => 'file'));
        $form .= $this->Form->file('csv');
        $form .= $this->Form->submit(__('Upload',true));
        return $form;
    }

}
