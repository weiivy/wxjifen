<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\library\Help;
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
            $file = \Yii::$app->request->post('file');
            if ($file === null) {
                throw  new \Exception('没有上传文件', 0);
            }
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)) {

                //检查文件大小
                $fileSize = $this->getPicSize($file,$result );
                if ($fileSize > 1 * 1024 * 1024) {
                    throw  new \Exception('文件大小不能超过' . 1 . 'M', 0);
                }


                //检查扩展名
                $extensionName = array('jpg', 'jpeg', 'png', 'gif');
                $allowExt = array_map('strtolower', $extensionName);
                if (!in_array(strtolower($result[2]), $allowExt)) {

                    if (empty($originalExtension)) {
                        throw  new \Exception('不允许上传没有扩展名的文件', 0);
                    } else {
                        throw  new \Exception('不允许上传"' . $result[2] . '"格式的文件', 0);
                    }
                }


                //验证扩展名
                $newFile = "/uploads/tmp/product/". @date('Ym') . '/' . @date('d') . '/' . uniqid() . '.' . $result[2];
                Help::recursiveMkdir(\Yii::$app->getBasePath() .'/'.dirname($newFile));
                file_put_contents(\Yii::$app->getBasePath() .'/'.dirname($newFile) . '/'. basename($newFile), base64_decode(str_replace($result[1], '', $file)));

                return array(
                    'status' => 200,
                    'files' => [
                        'url' => \Yii::$app->params['uploadUrl'] . $newFile,
                        'image' => $newFile
                    ],
                    'message' => "上传成功"
                );
            }
        }catch (\Exception $e){
            return array(
                'status' => $e->getCode(),
                'files' => [],
                'message' => $e->getMessage()
            );
        }


        /*try{
            $up = new UploadedFile();
            $up->thumb = [];
            $up->basePath = 'uploads/tmp/product/';
            $up->subPath = @date('Ym') . '/' . @date('d') . '/';


            if (!$up->doUpload('file')) {
                throw new \Exception($up->getError(), 0);
            }
            $files = $up->getFiles();

            return array(
                'status' => 200,
                'files' => [
                    'url'   => \Yii::$app->params['uploadUrl'] . $files[0]['url'],
                    'image' => $files[0]['url']
                ],
                'message' => '上传成功'
            );
        }catch (\Exception $e){
            return array(
                'status' => $e->getCode(),
                'files' => [],
                'message' => $e->getMessage()
            );

        }*/

    }

    /**
     * 计算文件大小
     * @param $file
     * @param $result
     * @return float|int
     */
    public function getPicSize($file, $result)
    {
        $file = str_replace($result[1], '', $file);
        $file = str_replace('=', '', $file);
        $imgLen = strlen($file);
        $fileSize = intval($imgLen-($imgLen/8)*2);
        return $fileSize/1024;

    }


}