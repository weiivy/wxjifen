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
    const GRADE_1 = 1; //会员
    const GRADE_2 = 2; //代理
    const GRADE_3 = 3; //股东

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
            static::GRADE_1 => '会员',
            static::GRADE_2 => '代理',
            static::GRADE_3 => '股东'
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
}