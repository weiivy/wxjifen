<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

// Place here all of your cron jobs

// This command will execute financial-report/collection every day at 1:00
$schedule->command('financial-report/collection')->dailyAt('1:00');

// This command will execute financial-report/orderReport every tuesdays at 1:00
$schedule->command('financial-report/orderReport')->weeklyOn(2, '1:00');

$schedule->command('compare-price/price')->weeklyOn(5, '8:00');
$schedule->command('compare-price/email')->weeklyOn(2, '8:00');

//脚本监控 5分钟一次
$schedule->command('index/monitor')->everyFiveMinutes();

// This command will execute report-system/orderRelated every day at 6:00
$schedule->command('report-system/orderRelated')->dailyAt('6:00');

// This command will execute product/tour_pv every day at 1:00
$schedule->command('product/tour_pv')->dailyAt('1:00');
