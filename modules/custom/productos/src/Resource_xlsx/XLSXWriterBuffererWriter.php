<?php

namespace Drupal\ab_report_core\Resource_xlsx;

/**
 * {@inheritdoc}
 */
class XLSXWriterBuffererWriter {
  /**
   * {@inheritdoc}
   */
  protected $fd = NULL;
  /**
   * {@inheritdoc}
   */
  protected $buffer = '';
  /**
   * {@inheritdoc}
   */
  protected $checkUtf8 = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct($filename, $fd_fopen_flags = 'w', $checkUtf8 = FALSE) {
    $this->check_utf8 = $checkUtf8;
    $this->fd = fopen($filename, $fd_fopen_flags);
    if ($this->fd === FALSE) {
      XLSXWriter::log("Unable to open $filename for writing.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function write($string) {
    $this->buffer .= $string;
    if (isset($this->buffer[8191])) {
      $this->purge();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function purge() {
    if ($this->fd) {
      if ($this->check_utf8 && !self::isValidUtf8($this->buffer)) {
        XLSXWriter::log("Error, invalid UTF8 encoding detected.");
        $this->check_utf8 = FALSE;
      }
      fwrite($this->fd, $this->buffer);
      $this->buffer = '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    $this->purge();
    if ($this->fd) {
      fclose($this->fd);
      $this->fd = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __destruct() {
    $this->close();
  }

  /**
   * {@inheritdoc}
   */
  public function ftell() {
    if ($this->fd) {
      $this->purge();
      return ftell($this->fd);
    }
    return -1;
  }

  /**
   * {@inheritdoc}
   */
  public function fseek($pos) {
    if ($this->fd) {
      $this->purge();
      return fseek($this->fd, $pos);
    }
    return -1;
  }

  /**
   * {@inheritdoc}
   */
  protected static function isValidUtf8($string) {
    if (function_exists('mb_check_encoding')) {
      return mb_check_encoding($string, 'UTF-8') ? TRUE : FALSE;
    }
    return preg_match("//u", $string) ? TRUE : FALSE;
  }

}
