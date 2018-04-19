<?php
/**
 * 路由组件
 * @copyright (c) 2017, lulutrip.com
 * @author  martin ren<martin@lulutrip.com>
 */

namespace common\components;

use yii\web\Request;
use yii\web\UrlRule;
use Yii;

class RouteUrl extends UrlRule
{
    private $_routeParams;
    private $_paramRules;
    private $_routeRule;

    /**
     * 参数链接符
     * @var string
     */
    public  $paramSeparator = '/';

    public function parseRequest($manager, $request)
    {
        $url    = $request->getPathInfo();
        $match  = explode($this->paramSeparator, $url);
        $url    = '';

        if($this->paramSeparator != '/') {
            $requestUrl = $match[0];
            array_shift($match);
            $match = array_merge(explode('/', $requestUrl), $match);
        }

        foreach ($match as $k => $v) {
            if(strstr($v, '-')) {
                $s = explode('-', $v);
                $this->defaults[$s[0]] = $s[1];
            } else {
                $url .= $v.'/';
            }
        }

        $url = trim($url, '/');
        foreach ($this->defaults as $k => $v) {
            $url .= $this->paramSeparator . $k . '-' . $v;
        }

        $url = trim($url, $this->paramSeparator);
        $request->setPathInfo($url);
        return $this->_parseRequest($manager, $request);

    }

    private function _parseRequest($manager, Request $request)
    {
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        $pathInfo = $request->getPathInfo();

        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
        if ($suffix !== '' && $pathInfo !== '') {
            $n = strlen($suffix);
            if (substr_compare($pathInfo, $suffix, -$n, $n) === 0) {
                $pathInfo = substr($pathInfo, 0, -$n);
                if ($pathInfo === '') {
                    // suffix alone is not allowed
                    return false;
                }
            } else {
                return false;
            }
        }

        if ($this->host !== null) {
            $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
        }


        if (!preg_match($this->pattern, $pathInfo, $matches)) {
            return false;
        }

        $matches = $this->substitutePlaceholderNames($matches);

        foreach ($this->defaults as $name => $value) {
            if (!isset($matches[$name]) || $matches[$name] === '') {
                $matches[$name] = $value;
            }
        }
        $params = $this->defaults;
        $tr = [];
        foreach ($matches as $name => $value) {
            if (isset($this->_routeParams[$name])) {
                $tr[$this->_routeParams[$name]] = $value;
                unset($params[$name]);
            } elseif (isset($this->_paramRules[$name])) {
                $params[$name] = $value;
            }
        }
        if ($this->_routeRule !== null) {
            $route = strtr($this->route, $tr);
        } else {
            $route = $this->route;
        }

        Yii::trace("Request parsed with URL rule: {$this->name}", __METHOD__);

        return [$route, $params];
    }
}