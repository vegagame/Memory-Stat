<?php
/*
 * This file is part of the Memory Stat package.
 *
 * (c) Denis Casanuova  <denis.casanuova@sviluppare.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * See: http://github.com/vegagame/Memory-Stat
 */

namespace vegagame;

/**
 * Memory Stat class
 *
 * @author Denis Casanuova <denis.casanuova@sviluppare.net>
 */
class MemoryStat
{
  /**
   * @var boolean
   * @link http://php.net/manual/en/function.memory-get-usage.php
   * Set this to TRUE to get the real size of memory allocated from system.
   * If not set or FALSE only the memory used by emalloc() is reported.
   */
  private $realUsage = false;

  /**
   * @var integer
   * The maximum amount of memory in bytes that a script is allowed to allocate.
   * http://php.net/manual/en/ini.core.php#ini.memory-limit
   */
  private $memoryLimit = 0;

  /**
   * @array
   * Checkpoint collector
   */
  private $log = array();

  /**
   * @string
   * sprintf template
   */
  private $infoTemplate = "Memory Stat [%s] %s| Usage: %s (%s%%)";

  /**
   * @string
   * sprintf verbose template
   */
  private $infoTemplateVerbose = "Memory Stat [%s] %s| Memory Usage: %s (%s%%)  | Memory Peak: %s | Memory Total: %s | Increase from last checkpoint %s%%  ";

  /**
   * Build the instance of Memory Usage
   *
   * @param boolean $realUsage
   */
  public function __construct($realUsage = false)
  {
    $this->realUsage = (bool) $realUsage;
    $this->memoryLimit = $this->getMemoryFromIni();
    $this->addCheckpoint('Memory Stat Start');
  }

  /**
   * Get current memory limit from ini_get('memory_limit')
   *
   * @return integer
   */
  public function getMemoryLimitRaw()
  {
    return $this->memoryLimit;
  }

  /**
   * Get current memory limit
   *
   * @return string
   */
  public function getMemoryLimit()
  {
    return $this->format($this->memoryLimit);
  }

  /**
   * Get current memory usage raw
   *
   * @return integer
   */
  public function getCurrentRaw()
  {
    return memory_get_usage($this->realUsage);
  }

  /**
   * Get current memory usage formatted
   *
   * @return string
   */
  public function getCurrent()
  {
    return $this->format($this->getCurrentRaw());
  }

  /**
   * Get peak of memory usage Raw
   *
   * @return integer
   */
  public function getPeakRaw()
  {
    return memory_get_peak_usage($this->realUsage);
  }

  /**
   * Get peak of memory usage formatted
   *
   * @return string
   */
  public function getPeak()
  {
    return $this->format($this->getPeakRaw());
  }

  /**
   * Add new Checkpoint
   *
   * @param string $message
   */
  public function addCheckpoint($message = '')
  {
    $a = $this->getInfoRaw();
    $a['message'] = (string) $message;
    $this->log[] = $a;
  }

  /**
   * Get current raw information about memory
   *
   * @return array
   */
  public function getInfoRaw()
  {
    end($this->log);

    $now = new \DateTime('now');

    return array(
        'time' => $now->getTimestamp(),
        'time_format' => $now->format('Y-m-d H:i:s'),
        'total' => $this->memoryLimit,
        'current' => $this->getCurrentRaw(),
        'increase_from_last_percent' => is_int(key($this->log)) ? 100 - $this->getPercentage($this->getCurrentRaw(), $this->log[key($this->log)]['current']) : 0,
        'total_percent' => $this->getPercentage($this->memoryLimit, $this->getCurrentRaw()),
    );
  }

  /**
   * Get current information about memory
   *
   * @param boolean $verbose
   * @return string
   */
  public function getInfo($verbose = false)
  {
    $info = $this->getInfoRaw();

    if ((bool) $verbose) {

      return sprintf(
          $this->infoTemplateVerbose, $info['time_format'], '', $this->format($info['current']), $info['total_percent'], $this->format($this->getPeakRaw()), $this->format($this->memoryLimit), $info['increase_from_last_percent']
      );
    }

    return sprintf(
        $this->infoTemplate, $info['time_format'], '', $this->format($info['current']), $info['total_percent']
    );
  }

  /**
   * Get all checkpoint information about memory
   *
   * @return array
   */
  public function getTotalRaw()
  {
    $this->addCheckpoint('Memory Stat End');
    return $this->log;
  }

  /**
   * Get all checkpoint information about memory string
   *
   * @param boolean $verbose
   * @return string
   */
  public function getTotal($verbose = false)
  {
    $total = '';

    foreach ($this->getTotalRaw() as $log) {

      if ($verbose) {
        $total .= sprintf($this->infoTemplateVerbose, $log['time_format'], '| Info: '.$log['message'].' ', $this->format($log['current']), $log['total_percent'], $this->format($this->getPeakRaw()), $this->format($this->memoryLimit), $log['increase_from_last_percent']).PHP_EOL;
      } else {
        $total .= sprintf($this->infoTemplate, $log['time_format'], '| '.$log['message'].' ', $this->format($log['current']), $log['total_percent']).PHP_EOL;
      }
    }

    return $total;
  }

  /**
   * Set current memory limit from ini_get('memory_limit')
   *
   * @return integer
   */
  private function getMemoryFromIni()
  {
    if (-1 != $memoryLimit = ini_get('memory_limit')) {
      return $this->toInteger($memoryLimit);
    }

    return 0;
  }

  /**
   * Convert values like 128M, 1G, 512K in bytes
   *
   * @param string $string
   * @return mixed Float or formatted string
   */
  private function toInteger($string = '')
  {
    sscanf(strtoupper($string), '%u%c', $number, $suffix);
    if (isset($suffix)) {
      $number = $number * pow(1024, strpos(' KMG', $suffix));
    }
    return $number;
  }

  /**
   * Byte formatting
   *
   * @param int $size
   * @return string Formatted string
   */
  private function format($size = 0)
  {
    if ($size == 0) {
      return 'n-a';
    }
    $unit = array('B', 'K', 'M', 'G', 'T', 'P');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).$unit[$i];
  }

  /**
   * Calculate percentage
   *
   * @param float $total
   * @param float $number
   * @return float
   */
  private function getPercentage($total, $number)
  {
    if ((float) $total == 0) {
      return 0;
    }
    return round((($number / $total) * 100), 2);
  }
}