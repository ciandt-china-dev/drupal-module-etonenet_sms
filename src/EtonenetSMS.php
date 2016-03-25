<?php

/**
 * @file
 * Contains \Drupal\etonenet_sms\EtonenetSMS
 */

namespace Drupal\etonenet_sms;

/**
 * Etonenet SMS
 */
class EtonenetSMS {
  private $spid;
  private $spid_pass;
  private $service_url;

  protected $logger;

  public function __construct($spid, $spid_pass, $service_url) {
    $this->spid = $spid;
    $this->spid_pass = function_exists('aes_decrypt') ? aes_decrypt($spid_pass) : $spid_pass;
    $this->service_url = $service_url;
  }

  /**
   * Check configuration
   *
   * @return bool
   */
  private function isValid() {
    return !(empty($this->service_url) || empty($this->spid) || empty($this->spid_pass));
  }

  /**
   * Send a message
   *
   * @param $number
   * @param $message
   *
   * @return bool
   */
  public function sendMessage($number, $message) {
    if ($this->isValid()) {

      $headers = [
        'User-Agent' => '',
        'Connection' => 'Close',
        'Content-Type' => 'text/plain',
      ];

      $query = [
        'command' => 'MT_REQUEST',
        'spid' => $this->spid,
        'sppassword' => $this->spid_pass,
        'da' => "86$number",
        'dc' => '15',
        'sm' => $this->encodedMessage($message),
      ];

      try {
        $response = \Drupal::httpClient()->get($this->service_url, compact('headers', 'query'));
        return (string) $response->getBody();
      }
      catch (Exception $e) {
        return FALSE;
      }
      return $TRUE;
    }

    return FALSE;
  }

  /**
   * Encode a message
   *
   * @param $message
   *
   * @return string
   */
  private function encodedMessage($message) {
    $message = iconv('UTF-8', 'GBK', $message);
    return bin2hex($message);
  }
}
