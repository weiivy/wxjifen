<?php
/**
 * 脚本监控配置
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
return [
    ['logName' => 'email-reminder#flight', 'interTime' => 1 * 24 * 60],
    ['logName' => 'email-reminder#comment', 'interTime' => 1 * 24 * 60],
    ['logName' => 'email-reminder#itinerary', 'interTime' => 1 * 24 * 60],
    ['logName' => 'compare-price#price', 'interTime' => 7 * 24 * 60],
    ['logName' => 'compare-price#email', 'interTime' => 7 * 24 * 60],
    ['logName' => 'financial-report#collection', 'interTime' => 1 * 24 * 60],
    ['logName' => 'financial-report#orderReport', 'interTime' => 7 * 24 * 60],
    ['logName' => 'report-system#orderRelated', 'interTime' => 1 * 24 * 60],
    ['logName' => 'product#tour_pv', 'interTime' => 1 * 24 * 60],
];