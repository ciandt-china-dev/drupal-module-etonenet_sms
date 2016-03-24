<?php

/**
 * @file etonenet_sms.sendcore.inc
 *

/**
 * Etonenet SMS
 */
class EtonenetSMS {
  private $spid;
  private $spid_pass;
  private $service_url;

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
    watchdog('sms', 'Etonenet SMS message sent to %number with the text: @message', array('%number' => $number, '@message' => $message), WATCHDOG_INFO);
    if ($this->isValid()) {
      $config = array(
        'command' => 'MT_REQUEST',
        'spid' => $this->spid,
        'sppassword' => $this->spid_pass,
        'da' => "86$number",
        'dc' => '15',
        'sm' => $this->encodedMessage($message),
      );

      $data = array();
      foreach ($config as $key => $value) {
        $data[] = "$key=$value";
      }
      $url = $this->service_url . '?' . implode('&', $data);

      $options = array(
        'headers' => array(
          'User-Agent' => '',
          'Connection' => 'Close',
          'Content-Type' => 'text/plain',
        ),
      );

      $result = drupal_http_request($url, $options);

      watchdog('sms', "Etonenet REQUEST:$result->request", array(), WATCHDOG_INFO);
      watchdog('sms', "Etonenet RESPONSE:$result->data", array(), WATCHDOG_INFO);

      return array('status' => TRUE);
    }

    return array('status' => FALSE);
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
