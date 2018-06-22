<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\library\UploadedFile;

/**
 * message
 * @copyright (c) 2018
 * @author  Weiwei Zhang<zhangweiwei@2345.com>
 */
class UploadImage extends BaseAction
{
    public function run()
    {
        try{
            $up = new UploadedFile();
            $up->thumb = [];
            $up->basePath = 'runtime/product/';
            $up->subPath = @date('Ym') . '/' . @date('d') . '/';


            if (!$up->doUpload('file')) {
                throw new \Exception($up->getError(), 0);
            }
            $files = $up->getFiles();

            $result = array(
                'status' => 200,
                'files' => [
                    'url'   => \Yii::$app->params['uploadUrl'] . $files[0]['url'],
                    'image' => $files[0]['url']
                ],
                'message' => '上传成功'
            );
        }catch (\Exception $e){
            $result = array(
                'status' => $e->getCode(),
                'files' => '',
                'message' => $e->getMessage()
            );

        }
        return json_encode($result);

    }


}