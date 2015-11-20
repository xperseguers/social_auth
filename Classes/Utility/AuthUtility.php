<?php
namespace MV\SocialAuth\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 VANCLOOSTER Mickael <vanclooster.mickael@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class AuthUtility
 *
 * @package MV\SocialAuth\Utility
 */
class AuthUtility  {

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $extConfig;

    /**
     * initializeObject
     */
    public function initializeObject() {
        $this->extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['social_auth']);
        $this->config = array(
            'base_url' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . '?type=1316773682',
            'providers' => array (
                'Facebook' => array (
                    'enabled' =>  $this->extConfig['providers.']['facebook.']['enabled'],
                    'keys'    => array (
                        'id' => $this->extConfig['providers.']['facebook.']['keys.']['id'],
                        'secret' => $this->extConfig['providers.']['facebook.']['keys.']['secret']
                    ),
                    'scope'   => $this->extConfig['providers.']['facebook.']['scope'],
                    'display' => ($this->extConfig['providers.']['facebook.']['display']) ? $this->extConfig['provider.']['facebook.']['display'] : 'page'
                ),
                'Google' => array (
                    'enabled' =>  $this->extConfig['providers.']['google.']['enabled'],
                    'keys'    => array (
                        'id' => $this->extConfig['providers.']['google.']['keys.']['id'],
                        'secret' => $this->extConfig['providers.']['google.']['keys.']['secret']
                    ),
                    'scope'   => $this->extConfig['providers.']['google.']['scope']
                ),
                'Twitter' => array (
                    'enabled' =>  $this->extConfig['providers.']['twitter.']['enabled'],
                    'keys'    => array (
                        'key' => $this->extConfig['providers.']['twitter.']['keys.']['key'],
                        'secret' => $this->extConfig['providers.']['twitter.']['keys.']['secret']
                    )
                ),
            ),
            'debug_mode' => FALSE,
            'debug_file' => '',
        );
    }

    /**
     * @param string $provider
     * @param string $returnTo
     *
     *  @return \Hybrid_User_Profile|FALSE
     */
    public function authenticate($provider) {
        try{
            $hybridauth = new \Hybrid_Auth($this->config);
            $service = $hybridauth->authenticate($provider);
            $socialUser = $service->getUserProfile();
        } catch( \Exception $exception ){
            switch ($exception->getCode()) {
                case 0:
                    $error = 'Unspecified error.';
                    break;
                case 1:
                    $error = 'Hybriauth configuration error.';
                    break;
                case 2:
                    $error = 'Provider not properly configured.';
                    break;
                case 3:
                    $error = 'Unknown or disabled provider.';
                    break;
                case 4:
                    $error = 'Missing provider application credentials.';
                    break;
                case 5:
                    $error = 'User has cancelled the authentication or the provider refused the connection.';
                    break;
                case 6:
                    $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
                    break;
                case 7:
                    $error = 'User not connected to the provider.';
                    break;
            }
            HttpUtility::redirect(GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . '?tx_socialauth_pi1[error]='.$exception->getCode());
        }
        if ($socialUser) {
            return $socialUser;
        } else {
            return FALSE;
        }
    }

    /**
     * logout from all providers when typo3 logout takes place
     */
    public function logout() {
        (new \Hybrid_Auth($this->config))->logoutAllProviders();
    }

}