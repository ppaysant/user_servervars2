<?php
/**
 * ownCloud - Context
 *
 * @author Marc DeXeT
 * @copyright 2014 DSI CNRS https://www.dsi.cnrs.fr
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\User_Servervars2\Service\Impl;

use OCA\User_Servervars2\Service\Tokens;
use OCA\User_Servervars2\Lib\CustomConfig;

class ConfigurableTokens implements Tokens {

	/**
	 * undocumented class variable
	 *
	 * @var appConfig
	 **/
	var $appConfig;

	function __construct(CustomConfig $appConfig) {
		$this->appConfig = $appConfig;
	}

	private function getParam($key, $default=null) {
		return $this->appConfig->getValue($key);
	}

 	/**
 	 * Return the identity provider ( as 'https://idp.example.org/idp/shibboleth')
 	 * @return provider name or false if none
 	 */
 	public function getProviderId(){
 		return $this->getParam('tokens_provider_id'); 
 	}
 	/**
 	 * undocumented function
 	 *
 	 * @return user id or false is none
 	 * @author 
 	 **/
 	public function getUserId() {
 		return $this->evalMapping('tokens_user_id'); //, 'foo');
}

public function getDisplayName(){
	 		return $this->evalMapping('tokens_display_name'); //, 'bar');
}

public function getEmail() {
	return $this->evalMapping('tokens_email'); //, 'bar@foo.org');
}

public function getGroupsArray() {
	return $this->evalMapping('tokens_groups');
}

/*
If eval() is the answer, you're almost certainly asking the
wrong question. -- Rasmus Lerdorf, BDFL of PHP
*/
public function evalMapping($param) {
	$mapping = $this->getParam($param, null);

	// if is a eval expression
	if ( ! is_string($mapping) ) return;
	if ( strpos($mapping, 'eval:') === 0 ) {
		$mapping=substr($mapping, 5);
		$f = create_function('', sprintf('return %s;', $mapping));
		try {
			$value = $f();
		}
		catch(Exception $e) {
			\OC_Log::write('servervars',
			'EVALMAPPING' . $mapping.' '.$e->getMessage(),
			\OC_Log::ERROR);

			return false;
		} 
		return $value;

	} else {
		return $mapping;
	}
	

	
}

}
