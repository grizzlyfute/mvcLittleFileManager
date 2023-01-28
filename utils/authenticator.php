<?php

// Regroup all authentification method

require_once('models/user.php');
require_once('config.php');

class Authenticator
{
	private const SESSIONNAME = 'filemanager';
	private static $users = array();

	public static function loadUsers(): void
	{
		self::$users = array();
		if (is_readable (APPDATAPATH . 'users.json'))
		{
			$users_json = json_decode(file_get_contents(APPDATAPATH . 'users.json'), true);
		}
		else
		{
			$users_json = null;
		}
		if ($users_json)
		{
			foreach ($users_json as $username => $user_data)
			{
				$user = new User();
				$user->fromArray($user_data);
				// strtolower:  Prevent user to make upper case in config
				$login = strtolower($username);
				self::$users[$login] = $user;
			}
		}
	}

	public static function getAllUsers(): array
	{
		return self::$users;
	}

	public static function saveUsers():bool
	{
		$users_json = array();
		foreach (self::$users as $user)
		{
			$users_json[$user->getLogin()] = $user->toArray();
		}
		if (!file_put_contents (APPDATAPATH . 'users.json', json_encode ($users_json, JSON_PRETTY_PRINT)))
		{
			return false;
		}
		return true;
	}

	public static function setEditUser(User $user) : void
	{
		self::$users[$user->getLogin()] = $user;
	}

	public static function deleteUser(User $user): void
	{
		$login = $user->getLogin();
		if (isset(self::$users[$login]))
		{
			unset(self::$users[$login]);
			self::$users = array_values(self::$users);
		}
	}

	public static function getUser($username) : ?User
	{
		$login = strtolower($username);
		if (isset(self::$users[$login]))
		{
			return self::$users[$login];
		}
		else
		{
			return null;
		}
	}

	public static function getCurrentUser(): ?User
	{
		if (empty(self::$users))
		{
			self::loadUsers();
		}

		$user = null;
		if (empty(self::$users))
		{
			// No users loaded
		}
		// By session id
		else if (isset($_SESSION[SESSIONNAME]['curuser']))
		{
			$login = $_SESSION[SESSIONNAME]['curuser']->getLogin();
			if ($login != null &&
				isset (self::$users[$login]))
			{
				$user = self::$users[$login];
			}
		}
		// Remember me
		else if (isset($_COOKIE['username'], $_COOKIE['usertoken']) &&
			$_COOKIE['username'] && $_COOKIE['usertoken'])
		{
			$login = strtolower($_COOKIE['username']);
			if (isset(self::$users[$login]))
			{
				$canditateUser = self::$users[$login];
				if (self::checkToken($canditateUser, $_COOKIE['usertoken']))
				{
					$user = $canditateUser;
					self::setCurrentUser($user);
					// True because getCurrentUser is called early by index.php
					self::refreshCookie($user, $_COOKIE['usertoken'], true);
				}
			}
		}
		return $user;
	}

	public static function setCurrentUser(User $user): void
	{
		$_SESSION[SESSIONNAME]['curuser'] = $user;
	}

	public static function refreshCookie(User $user, ?string $token, bool $rememberme): void
	{
		global $CONFIG;
		// Not remember me: Destroy when navigator session expires
		$expire_ts = 0;
		if ($token == null)
		{
			$token = crypt($user->getLogin() . $user->getPasswordHash(), '$5$' . bin2hex(openssl_random_pseudo_bytes(16)));
		}

		if ($rememberme)
		{
			$expire_ts = time() + $CONFIG['rememberme_ts'];
		}

		// Should be call before any output (see ob_start/ob_end_flush)
		// Use http-only for cookie
		// Warning : secure cookies is accepted only one secure site using https. Disable it

ob_start(); // for cookie
		setcookie('username', $user->getLogin(), $expire_ts, '/', '', false, true);
		setcookie('usertoken', $token, $expire_ts, '/', '', false, true);
ob_end_flush();
	}

	public static function checkToken(User $user, string $token): bool
	{
		// crypt username+passwordhash, using same salt as $token
		return hash_equals($token, crypt($user->getLogin() . $user->getPasswordHash(), $token));
	}

	public static function checkPassword(User $user, string $password): bool
	{
		$is_auth = false;
		if ($user->getIsActive())
		{
			$knowHash = $user->getPasswordHash();
			// openssl hash
			if (!$is_auth)
			{
				$userHash = crypt($password, $knowHash);
				$is_auth = hash_equals($knowHash, $userHash);
			}
			// password_hash
			if (!$is_auth)
			{
				$is_auth = password_verify($password, $knowHash);
			}
		}

		return $is_auth;
	}

	public static function getPasswordHashFormString($password) : string
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	public static function login($username, $password, $rememberme): bool
	{
		// Cookie authentication
		$curUser = self::getCurrentUser();

		// No cookie. Try standart method
		if (!$curUser && isset($username, $password))
		{
			$login = strtolower($username);
			if (isset (self::$users[$login]))
			{
				$curUser = self::$users[$login];
				if (!$curUser || !self::checkPassword($curUser, $password))
				{
					$curUser = null;
				}
			}
		}

		// Update cookie
		if ($curUser)
		{
			if (isset($_COOKIE['username'], $_COOKIE['usertoken']))
			{
				self::refreshCookie($curUser, $_COOKIE['usertoken'], $rememberme);
			}
			else
			{
				self::refreshCookie($curUser, null, $rememberme);
			}

			// Save in php session
			self::setCurrentUser($curUser);
		}

		return $curUser != null;
	}

	public static function logout(): void
	{
		$expire_ts = time() - 3600;
		// Php cookie
		if (isset($_COOKIE[SESSIONNAME]))
		{
			setcookie(SESSIONNAME, '', $expire_ts, '/', '', false, true);
		}
		// Clear session from globals
		$_SESSION = array();
		// Clear session from disk
		session_destroy();
		// Clean remember me parameter
		if (isset($_COOKIE['username']))
		{
			setcookie('username', '', $expire_ts, '/', '', false, true);
		}
		if (isset($_COOKIE['usertoken']))
		{
			setcookie('usertoken', '', $expire_ts, '/', '', false, true);
		}
	}
}
