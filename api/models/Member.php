<?php
/**
 * Created by PhpStorm.
 * User: zhangweiwei
 * Date: 18/4/8
 * Time: 下午10:09
 */

namespace api\models;


class Member extends \common\models\Member
{
    const GRADE_10 = 10; //会员
    const GRADE_20 = 20; //代理
    const GRADE_30 = 30; //股东

    const status_10 = 10; //正常
    const status_20 = 20; //删除

    /**
     * 会员等级别名
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $grade
     * @return mixed|null
     */
    public static function gradeAlisa($grade)
    {
        $array = [
            static::GRADE_10 => '会员',
            static::GRADE_20 => '代理',
            static::GRADE_30 => '股东'
        ];
        return isset($array[$grade]) ? $array[$grade] : null;
    }

    /**
     * 状态别名
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $grade
     * @return mixed|null
     */
    public static function statusAlisa($status)
    {
        $array = [
            static::status_10 => '正常',
            static::status_20 => '删除'
        ];
        return isset($array[$status]) ? $array[$status] : null;
    }

    /**
     * 缩略图
     * @return array
     */
    public static function getThumbParams()
    {
        $thumb = [
            '1' => ['w' => 100, 'h' => 100,'cut' => true],
        ];

        return empty($thumb) ? null : $thumb;
    }

    /**
     * 返回图片存储目录
     * @return string
     */
    public static function getImagePath()
    {
        $str = 'uploads/avatar/';
        return strlen($str) > 0 ? $str : null;
    }

    /**
     * 获取缩略图文件名
     * @param $thumb string 缩略图
     * @return string
     */
    public function getThumb($thumb = null)
    {
        if(empty($this->avatar)){
            return '';
        }

        if($thumb === null){
            return $this->avatar;
        }

        $baseName = basename($this->avatar);
        $path = dirname($this->avatar);
        return $path . '/' . $thumb . '/' . $baseName;
    }
}