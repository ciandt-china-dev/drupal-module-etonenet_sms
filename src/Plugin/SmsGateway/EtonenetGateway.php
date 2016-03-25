<?php

/**
 * @file
 * Contains \Drupal\sms\Plugin\SmsGateway\LogGateway
 */

namespace Drupal\etonenet_sms\Plugin\SmsGateway;

use Drupal\sms\Message\SmsDeliveryReport;
use Drupal\sms\Message\SmsDeliveryReportInterface;
use Drupal\sms\Plugin\SmsGatewayPluginBase;
use Drupal\sms\Message\SmsMessageInterface;
use Drupal\sms\Message\SmsMessageResult;
use Drupal\etonenet_sms\EtonenetSMS;
use Drupal\Core\Form\FormStateInterface;

/**
 * @SmsGateway(
 *   id = "etonenet",
 *   label = @Translation("Etone net"),
 * )
 */
class EtonenetGateway extends SmsGatewayPluginBase {

  /**
   * {@inheritdoc}
   */
  public function send(SmsMessageInterface $sms, array $options) {

    $etonenetSms = new EtonenetSMS($this->configuration['spid'], $this->configuration['spid_pass'], $this->configuration['service_url']);

    $message = $sms->getMessage();

    $return = ['status' => TRUE];
    $return['reports'] = [];

    foreach ($sms->getRecipients() as $number) {
      $data = $etonenetSms->sendMessage($number, $message);

      $this->logger()->notice('SMS message sent to %number with the text: @message',
            ['%number' => $number, '@message' => $message]);
      // $this->logger()->notice("Etonenet REQUEST:$result->request");
      $this->logger()->notice("Etonenet RESPONSE:$data");

      $return['reports'][$number] = new SmsDeliveryReport([
        'status' => SmsDeliveryReportInterface::STATUS_DELIVERED,
        'recipient' => $number,
        'gateway_status' => 'DELIVERED',
      ]);
    }

    return new SmsMessageResult($return);
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['service_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('SP Service Url'),
      '#default_value' => $this->configuration['service_url'],
      '#required' => TRUE,
      '#description' => t('The service url of Etonenet SMS provider'),
    );

    $form['spid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('SPID'),
      '#default_value' => $this->configuration['spid'],
      '#required' => TRUE,
      '#description' => t('Enter the SPID name.'),
    );
    $form['spid_pass'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('SPID Password'),
      '#default_value' => $this->configuration['spid_pass'],
      '#required' => TRUE,
      '#description' => t('Enter the SPID password.'),
    );
    return $form;
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['spid'] = $form_state->getValue('spid');
    $this->configuration['spid_pass'] = $form_state->getValue('spid_pass');
    $this->configuration['service_url'] = $form_state->getValue('service_url');
  }

}
