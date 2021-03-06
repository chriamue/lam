<?php
namespace LAM\REMOTE;
use \LAMException;
use \phpseclib\Net\SSH2;
use \phpseclib\Crypt\RSA;
/*
$Id$

  This code is part of LDAP Account Manager (http://www.ldap-account-manager.org/)
  Copyright (C) 2017  Roland Gruber

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
* This file includes functions to control LAM remote executions.
*
* @author Roland Gruber
*
* @package modules
*/

/**
 * Runs remote commands.
 *
 * @author Roland Gruber
 */
class Remote {

	/** SSH2 server handle */
	private $server = null;

	/**
	 * Constructor, include SSH library.
	 */
	public function __construct() {
		$this->includeSshLibrary();
	}

	/**
	* Sends commands to remote script.
	*
	* @param string $command command to execute
	* @return string output of remote script
	*/
	public function execute($command) {
		if ($this->server == null) {
			return array();
		}
		return $this->server->exec("sudo " . $_SESSION['config']->get_scriptPath() . ' ' . escapeshellarg($command));
	}

	/**
	 * Connects to the given SSH server.
	 *
	 * @param String $server server name (e.g. localhost or localhost,1234)
	 */
	public function connect($server) {
		$serverNameParts = explode(",", $server);
		$handle = false;
		if (sizeof($serverNameParts) > 1) {
			$handle = @new SSH2($serverNameParts[0], $serverNameParts[1]);
		}
		else {
			$handle = @new SSH2($server);
		}
		if (!$handle) {
			throw new LAMException(_("Unable to connect to remote server!"));
		}
		$this->loginSSH($handle);
		$this->server = $handle;
	}

	/**
	 * Closes the connection.
	 */
	public function disconnect() {
		if ($this->server == null) {
			return;
		}
		$this->server->disconnect();
	}

	/**
	 * Performs a login to the provided SSH handle.
	 *
	 * @param SSH2 $handle SSH handle
	 * @throws Exception login failed
	 */
	private function loginSSH($handle) {
		$username = $_SESSION['config']->getScriptUserName();
		$credentials = $_SESSION['ldap']->decrypt_login();
		if (empty($username)) {
			// get user name from current LAM user
			$sr = @ldap_read($_SESSION['ldap']->server(), $credentials[0], "objectClass=posixAccount", array('uid'), 0, 0, 0, LDAP_DEREF_NEVER);
			if ($sr) {
				$entry = @ldap_get_entries($_SESSION['ldap']->server(), $sr);
				$username = $entry[0]['uid'][0];
			}
			if (empty($username)) {
				throw new LAMException(sprintf(_("Your LAM admin user (%s) must be a valid Unix account to work with lamdaemon!"), getAbstractDN($credentials[0])));
			}
		}
		$password = $credentials[1];
		$keyPath = $_SESSION['config']->getScriptSSHKey();
		if (!empty($keyPath)) {
			// use key authentication
			if (!file_exists($keyPath) || !is_readable($keyPath)) {
				throw new LAMException(sprintf(_("Unable to read %s."), htmlspecialchars($keyPath)));
			}
			$key = file_get_contents($keyPath);
			$rsa = new RSA();
			$keyPassword = $_SESSION['config']->getScriptSSHKeyPassword();
			if (!empty($keyPassword)) {
				$rsa->setPassword($keyPassword);
			}
			if (!$rsa->loadKey($key)) {
				throw new LAMException(sprintf(_("Unable to load key %s."), htmlspecialchars($keyPath)));
			}
			$password = $rsa;
		}
		$login = @$handle->login($username, $password);
		if (!$login) {
			throw new LAMException(_("Unable to login to remote server!"));
		}
	}

	/**
	 * Include the SSH files.
	 */
	private function includeSshLibrary() {
		$prefix = dirname(__FILE__) . '/3rdParty/phpseclib/';
		require_once($prefix . 'Crypt/Base.php');
		require_once($prefix . 'Crypt/Blowfish.php');
		require_once($prefix . 'Crypt/Hash.php');
		require_once($prefix . 'Crypt/Random.php');
		require_once($prefix . 'Crypt/RC4.php');
		require_once($prefix . 'Crypt/Rijndael.php');
		require_once($prefix . 'Crypt/AES.php');
		require_once($prefix . 'Crypt/RSA.php');
		require_once($prefix . 'Crypt/DES.php');
		require_once($prefix . 'Crypt/TripleDES.php');
		require_once($prefix . 'Crypt/Twofish.php');
		require_once($prefix . 'Math/BigInteger.php');
		require_once($prefix . 'System/SSH/Agent.php');
		require_once($prefix . 'Net/SSH2.php');
	}

}

?>
