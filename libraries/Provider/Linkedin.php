<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');

/**
 * LinkedIn OAuth2 Provider
 * https://developer.linkedin.com/documents/authentication
 *
 * @package    CodeIgniter/OAuth2
 * @category   Provider
 * @author     Benjamin Hill
 * @copyright  (c) None
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */
class OAuth2_Provider_Linkedin extends OAuth2_Provider {

  public $method = 'POST';
  public $scope_seperator = ' ';

  public function __construct(array $options = array()) {
    if (empty($options['scope'])) {
      $options['scope'] = array(
          'r_liteprofile',
          'r_emailaddress'
      );
    }

    // Array it if its string
    $options['scope'] = (array) $options['scope'];
    parent::__construct($options);
  }

  public function url_authorize() {
    return 'https://www.linkedin.com/uas/oauth2/authorization';
  }

  public function url_access_token() {
    return 'https://www.linkedin.com/uas/oauth2/accessToken';
  }

  public function get_user_info(OAuth2_Token_Access $token) {

    $url_profile = 'https://api.linkedin.com/v2/me?' . http_build_query([
      'oauth2_access_token' => $token->access_token,
    ]);
    $user_data = json_decode(file_get_contents($url_profile), true);

    $url_email = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))&' . http_build_query([
      'oauth2_access_token' => $token->access_token,
    ]);
    $user_email_data = json_decode(file_get_contents($url_email), true);

    $user_id = @$user_data['id'];

    /* User Info */
    $firstName = @$user_data['firstName'];
    $lastName = @$user_data['lastName'];
    $preferred_locale = @$firstName['preferredLocale'];
    $str_preferred_locale = @$preferred_locale['language'] . '_' . @$preferred_locale['country'];
    $first_name = $firstName['localized'][$str_preferred_locale];
    $last_name = $lastName['localized'][$str_preferred_locale];

    $str_user_email = @$user_email_data['elements'][0]['handle~']['emailAddress'];

    return [
      'id' => $user_id,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'name' => $first_name . ' ' . $last_name,
      'email' => $str_user_email,
    ];
  }
}
