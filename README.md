#Memory Stat#
This is a simple which can be used to collect the PHP script memory usage information and to print all information. If you want to use it example on web page or command line script. Or even do another function for web pages and another for command line scripts.
Usage
-----
Install the latest version with `composer require vegagame/memory-stat`
```php
<?php

use vegagame\MemoryStat;

// Create new MemoryStat instance
$m = new MemoryStat(true);

// Example array
$a = array();
$b = array();
$c = array();

// Fill array with
for ($i = 0; $i < 10000; $i++) {
  $a[$i] = uniqid();
}
// add first checkpoint
$m->addCheckpoint('first checkpoint');

// get current memory limit from ini_get('memory_limit')
echo 'Memory limit raw: '.$m->getMemoryLimitRaw().PHP_EOL; // 536870912
// get current memory limit formatted from ini_get('memory_limit')
echo 'Memory limit: '.$m->getMemoryLimit().PHP_EOL; // 512M
// get current memory usage
echo 'Current raw: '.$m->getCurrentRaw().PHP_EOL; // 1835008
// get current memory usage formatted
echo 'Current: '.$m->getCurrent().PHP_EOL; // 1.75M
// Fill array 
for ($i = 0; $i < 30000; $i++) {
  $b[$i] = uniqid();
}

// get peak Raw
echo 'Peak raw: '.$m->getPeakRaw().PHP_EOL; // 6815744
// get peak usage formatted
echo 'Peak: '.$m->getPeak().PHP_EOL; //  6.5M
// add new checkpoint
$m->addCheckpoint('second checkpoint');

// Fill array 
for ($i = 0; $i < 50000; $i++) {
  $c[$i] = uniqid();
}

// get current raw information
print_r($m->getInfoRaw());
/**
 * Array
 * (
 * [time] => 1415461726
 * [time_format] => 2014-11-08 16:48:46
 * [total] => 536870912
 * [current] => 15466496
 * [increase_from_last_percent] => 55.93
 * [total_percent] => 2.88
 * 
 * )
 */
// get info formatted
echo 'Get current information: '.$m->getInfo().PHP_EOL; // Memory Stat [2014-11-08 16:48:46] | Usage: 14.75M (2.88%)
echo 'Get current information verbose : '.$m->getInfo(true).PHP_EOL; // Memory Stat [2014-11-08 16:48:46] | Memory Usage: 14.75M (2.88%)  | Memory Peak: 14.75M | Memory Total: 512M | Increase from last checkpoint 55.93%
echo 'Total information: '.PHP_EOL;
echo $m->getTotal(true); // verbose version
/**
 * Memory Stat [2014-11-08 16:48:40] | Info: Memory Stat Start | Memory Usage: 256K (0.05%)  | Memory Peak: 14.75M | Memory Total: 512M | Increase from last checkpoint 0%
 * Memory Stat [2014-11-08 16:48:41] | Info: first checkpoint | Memory Usage: 1.75M (0.34%)  | Memory Peak: 14.75M | Memory Total: 512M | Increase from last checkpoint 85.71%
 * Memory Stat [2014-11-08 16:48:43] | Info: second checkpoint | Memory Usage: 6.5M (1.27%)  | Memory Peak: 14.75M | Memory Total: 512M | Increase from last checkpoint 73.08%
 * Memory Stat [2014-11-08 16:48:46] | Info: Memory Stat End | Memory Usage: 14.75M (2.88%)  | Memory Peak: 14.75M | Memory Total: 512M | Increase from last checkpoint 55.93%
 */
// return all checkpoint array
print_r($m->getTotalRaw());
```
## Copyright

Copyright (c) 2014 Denis Casanuova. See LICENSE for details.