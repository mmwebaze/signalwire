<?php


namespace Drupal\signalwire\Plugin\EncryptionMethod;

use Drupal\encrypt\EncryptionMethodInterface;
use Drupal\encrypt\Plugin\EncryptionMethod\EncryptionMethodBase;
use mukto90\Ncrypt;

/**
 * Class SignalwireEncryption.
 *
 * @package Drupal\encrypt\Plugin\EncryptionMethod
 *
 * @EncryptionMethod(
 *   id = "signalwire_encrpt",
 *   title = @Translation("Signalwire Basic Encryption"),
 *   description = "Encrypts token and api keys",
 *   key_type = {"encryption"}
 * )
 */
class SignalwireEncryption extends EncryptionMethodBase implements EncryptionMethodInterface {

  /**
   * @var Ncrypt
   */
  private $ncrypt;

  /**
   * {@inheritdoc}
   */
  public function encrypt($text, $key, $options = []) {
    $this->ncrypt->set_secret_key($key);

    return $this->ncrypt->encrypt($text);
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, $key, $options = []) {
    $this->ncrypt->set_secret_key($key);
    return $this->ncrypt->decrypt($text);
  }

  /**
   * Check dependencies for the encryption method.
   *
   * @param string $text
   *   The text to be checked.
   * @param string $key
   *   The key to be checked.
   *
   * @return array
   *   An array of error messages, providing info on missing dependencies.
   */
  public function checkDependencies($text = NULL, $key = NULL) {
    $errors = [];

    return $errors;
  }
}
