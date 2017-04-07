<?php

namespace Drupal\dcamp_attendees\Entity;

use Drupal\dcamp\NicknameParserTrait;
use JsonSerializable;

class Attendee implements JsonSerializable {

  use NicknameParserTrait;

  /**
   * The full name.
   *
   * @var string
   */
  protected $name;

  /**
   * The headshot.
   *
   * @var string
   */
  protected $headshot;

  /**
   * The Twitter profile.
   * @var string
   */
  protected $twitter;

  /**
   * The Drupal.org profile.
   *
   * @var string
   */
  protected $drupal;

  /**
   * The company name.
   *
   * @var string
   */
  protected $company;

  /**
   * Attendee constructor.
   *
   * @param stdClass $attendee
   *   The raw object from EventBrite.
   */
  public function __construct($attendee) {
    $this->name = $attendee->profile->name;
    $this->company = !empty($attendee->profile->company) ? $attendee->profile->company : '';
    foreach ($attendee->answers as $answer) {
      if (!empty($answer->answer)) {
        if ($answer->question_id == '15019980') {
          $this->headshot = $answer->answer;
        }
        elseif ($answer->question_id == '15019982') {
          $this->twitter = $answer->answer;
        }
        elseif ($answer->question_id == '15019986') {
          $this->drupal = $answer->answer;
        }
      }
    }
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name) {
    $this->name = $name;
  }

  /**
   * Returns the headshot URL.
   *
   * Checks if the URL ends with .jpg or .png. Otherwise it assumes
   * that this is not an image and returns an empty string. Some people
   * submitted Google Drive images, which are not an actual image)
   * @return string
   */
  public function getHeadshot() {
    $extension = pathinfo(parse_url($this->headshot,PHP_URL_PATH),PATHINFO_EXTENSION);
    if (!in_array($extension, ['png', 'gif', 'jpg', 'jpeg'])) {
      return '';
    }
    return $this->headshot;
  }

  /**
   * @param string $headshot
   */
  public function setHeadshot(string $headshot) {
    $this->headshot = $headshot;
  }

  /**
   * @return string
   */
  public function getTwitter() {
    return $this->twitter;
  }

  /**
   * @param string $twitter
   */
  public function setTwitter(string $twitter) {
    $this->twitter = $twitter;
  }

  /**
   * @return string
   */
  public function getDrupal() {
    return $this->drupal;
  }

  /**
   * @param string $drupal
   */
  public function setDrupal(string $drupal) {
    $this->drupal = $drupal;
  }

  /**
   * @return string
   */
  public function getCompany() {
    return $this->company;
  }

  /**
   * @param string $company
   */
  public function setCompany($company) {
    $this->company = $company;
  }

  /**
   * Returns the Twitter or Drupal Link URL.
   *
   * @return string
   *   A URL to the profile or an empty string if there is no link.
   */
  public function getProfileUrl() {
    $url = '';

    if (!empty($this->getTwitter())) {
      $url = $this->getTwitterUrl($this->getTwitter());
    }
    elseif (!empty($this->getDrupal())) {
      $url = $this->getDrupalUrl($this->getDrupal());
    }

    return $url;
  }

  /**
   * Returns the Twitter or Drupal nickname.
   *
   * @return string
   *   A URL to the profile or an empty string if there is no nickname.
   */
  public function getNickname() {
    $nickname = '';

    if (!empty($this->getTwitter())) {
      $nickname = $this->extractNickname($this->getTwitter());
      $nickname = '@' . $nickname;
    }
    elseif (!empty($this->getDrupal())) {
      $nickname = $this->extractNickname($this->getDrupal());
    }

    return $nickname;
  }

  /**
   * Prepares object for conversion to JSON.
   */
  public function jsonSerialize() {
    return [
      'name' => $this->getName(),
      'company' => $this->getCompany(),
      'headshot' => $this->getHeadshot(),
      'twitter_url' => $this->getTwitter() ? $this->getTwitterUrl($this->getTwitter()) : '',
      'drupal_url' => $this->getDrupal() ? $this->getDrupalUrl($this->getDrupal()) : '',
    ];
  }

}
