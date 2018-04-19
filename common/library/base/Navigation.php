<?php
/**
 * 导航配置
 * @copyright (c) 2017, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */

namespace common\library\base;

use yii\base\Component;
use yii\base\Event;

class Navigation extends Component
{
    private $_topHover;
    private $_search301;
    private $_searchNav;
    private $_IpPhone;
    public function init()
    {
        $this->_topHover = require_once(__DIR__ . "/../../data/topHover.php");
        $this->_search301 = require_once(__DIR__ . "/../../data/search/search_301.php");
        $this->_searchNav = require_once(__DIR__ . "/../../data/search/search_nav.php");
        $this->_IpPhone = require_once(__DIR__ . "/../../data/IpPhone.php");
    }

    /**
     * 获取导航静态配置数据
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2017-04-14
     *
     * @return array 返回数据
     */
    public function getStaticData()
    {
        return [
           'topHover' => $this->_topHover,
           'search301' => $this->_search301,
           'searchNav' => $this->_searchNav,
           'IpPhone'   => $this->_IpPhone['phones']
        ];
    }

} 