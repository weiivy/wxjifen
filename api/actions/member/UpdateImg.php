<?php
namespace api\actions\member;


use api\actions\BaseAction;
use api\library\UploadedFile;
use api\models\Member;

class UpdateImg extends BaseAction
{
    public function run()
    {
        try{
            $up = new UploadedFile();
            $up->thumb = Member::getThumbParams();
            $up->basePath = Member::getImagePath();
            $up->subPath = @date('Ym') . '/' . @date('d') . '/';

            if (!$up->doUpload('file')) {
                throw new \Exception($up->getError(), 0);
            }

            $files = $up->getFiles();
            $member = Member::findOne(['id' => $this->memberId]);
            $memberOld = clone $member;
            $member->avatar = $files[0]['thumbnailUrl'][1];
            $member->save();

            if($member->errors) {
                \Yii::error(json_encode($member->errors));
                throw new \Exception("修改失败", 0);
            }
            static::deletePicture($memberOld);
            return array(
                'status' => 200,
                'message' => '修改成功'
            );
        }catch (\Exception $e){
            return array(
                'status' => $e->getCode(),
                'message' => $e->getMessage()
            );

        }

    }
    /**
     * 删除Picture图片文件
     */
    protected static function deletePicture(Member $member)
    {
        $base = dirname(\Yii::$app->getBasePath()) ;
        if (!empty($member->avatar)) {

            //原图
            @unlink($base . $member->avatar);

            //缩略图
            foreach ($member->getThumbParams() as $k => $v) {
                @unlink($base . $member->getThumb($k));
            }
        }
    }

}