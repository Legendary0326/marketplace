<?php
class User
{
    protected $cookie;
    protected $cookie_name;
    protected $cookie_value = array();
    protected $Name = '';
    protected $Data = [];
    protected $Username;
    protected $Registered;
    protected $UserID;
    protected $Currency;
    protected $reason;
    protected $twoFA = false;
    protected $OtherUserData = [];

    const LOGIN_SUCCESS             = 0;
    const LOGIN_NOTFOUND            = 1;
    const LOGIN_ERROR               = 2;
    const LOGIN_USERNOTFOUND        = 3;
    const LOGIN_WRONGPASSWORD       = 4;
    const LOGIN_ACCOUNTLOCKED       = 5;

    public function __construct()
    {
        try {
            $this->cookie_name = USER_COOKIE;
            if (isset($_COOKIE[$this->cookie_name])) {
                $this->cookie_value = $this->decryptData($_COOKIE[$this->cookie_name]);
                $this->cookie = true;
            } else {
                $this->cookie = false;
            }
        } catch(Exception $e) {
            $this->cookie = false;
        }
    }

    public function login($username, $password, $remember_login=false)
    {
        global $DB;
        if (!empty($username) && !empty($password)) {
            try {
                $assoc = $DB->getOne('user', $DB->field('Password') . ' IS NOT NULL AND Username = ' . $DB->string(strtolower($username)));
                if (is_array($assoc)) {
                    if (Password::verify($password, $assoc['Password'])) {
                        if ($assoc['BlockLogin'] == 1) {
                            $this->reason = self::LOGIN_ACCOUNTLOCKED;
                            return false;
                        } else {
                            $this->UserID = $assoc['UserID'];
                            $this->Username = $assoc['Username'];
                            $this->Registered = $assoc['Registered'];
                            $this->Currency = $assoc['Currency'];
                            $this->Data = $assoc;
                            $Session = $this->createSession();
                            $this->cookie_value = array(
                                'UserID'        => $assoc['UserID'],
                                'LoginKey'      => $assoc['LoginKey'],
                                'RememberLogin' => (bool) $remember_login,
                                'SessionKey'    => $Session
                            );
                            setcookie($this->cookie_name, $this->encryptData($this->cookie_value), ($remember_login ? time() + 604800 : 0), '/', preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']), USE_SSL, true);
                            $this->reason = self::LOGIN_SUCCESS;
                            return true;
                        }
                    } else {
                        $this->reason = self::LOGIN_WRONGPASSWORD;
                        return false;
                    }
                } else {
                    $this->reason = self::LOGIN_USERNOTFOUND;
                    return false;
                }
            } catch(Exception $e) {
                $this->reason = self::LOGIN_ERROR;
                return false;
            }
        } else {
            $this->reason = self::LOGIN_NOTFOUND;
            return false;
        }
    }

    public function logout()
    {
        global $DB;
        if (isset($this->cookie_value['UserID']) && isset($this->cookie_value['SessionKey'])) {
            $DB->update('user_sessions', ['Active' => 0], 'User = ' . $this->cookie_value['UserID'] . ' AND SessionKey = ' . $this->cookie_value['SessionKey']);
        }
        $this->deleteCookies();
        $this->cookie = false;
        $this->cookie_value = null;
        session_destroy();
    }

    function check_login()
    {
        global $DB;
        if ($this->cookie) {
            try {
                if (isset($this->cookie_value['UserID']) && isset($this->cookie_value['LoginKey']) && isset($this->cookie_value['SessionKey']) && isset($this->cookie_value['RememberLogin'])) {
                    $assoc = $DB->getOne('user', 'UserID = ' . $this->cookie_value['UserID']);
                    if (is_array($assoc)) {
                        if ($assoc['LoginKey'] == $this->cookie_value['LoginKey'] && $assoc['BlockLogin'] == 0) {
                            $assoc2 = $DB->getOne('user_sessions', 'User = ' . $DB->int($this->cookie_value['UserID']) . ' AND SessionKey = ' . $DB->string($this->cookie_value['SessionKey']));
                            if (isset($assoc2['Active']) && $assoc2['Active'] == 1) {
                                $SessionTimeout = (defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 300);
                                $this->UserID = $assoc['UserID'];
                                $this->Username = $assoc['Username'];
                                $this->Registered = $assoc['Registered'];
                                $this->Currency = $assoc['Currency'];
                                $this->Data = $assoc;
                                $this->twoFA = (bool) $assoc2['2FA'];
                                if ($assoc2['LastAction'] + $SessionTimeout <= time()) {
                                    $DB->update('user_sessions', ['Active' => 0], 'SessionID = ' . $DB->int($assoc2['SessionID']));
                                    $this->logout();
                                    return false;
                                } else {
                                    $DB->update('user_sessions', ['LastAction' => time()], 'SessionID = ' . $DB->int($assoc2['SessionID']));
                                }
                                return true;
                            } else {
                                $this->deleteCookies();
                                return false;
                            }
                        } else {
                            $this->deleteCookies();
                            return false;
                        }
                    } else {
                        $this->deleteCookies();
                        return false;
                    }
                } else {
                    $this->deleteCookies();
                    return false;
                }
            } catch(Exception $e) {
                $this->deleteCookies();
                return false;
            }
        } else {
            return false;
        }
    }

    function check_2fa()
    {
        if ($this->get('2FA')) {
            return $this->twoFA;
        } else {
            return true;
        }
    }

    function login_2fa($answer)
    {
        global $Pages;
        global $DB;
        if (!isset($this->cookie_value['SessionKey'])) {
            $this->logout();
            return false;
        }
        if (gpg::validateCode($answer)) {
            $DB->update('user_sessions', ['2FA' => 1], 'User = ' . $DB->int($this->cookie_value['UserID']) . ' AND SessionKey = ' . $DB->string($this->cookie_value['SessionKey']));
            $Pages->redirect('');
        } else {
            $this->logout();
            $Pages->redirect('login/error2fa');
        }
    }

    function deleteCookies()
    {
        setcookie($this->cookie_name, '', 0, '/', preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']), USE_SSL, true);
    }

    function createSession()
    {
        global $DB;
        $SessionKey = $this->generateSessionKey();
        $Session = [
            'User'          => $this->UserID,
            'SessionKey'    => $SessionKey,
            'ActiveSince'   => time(),
            'LastAction'    => time(),
            '2FA'           => ($this->twoFA ? 1 : 0)
        ];
        $DB->insert('user_sessions', $Session);
        return $SessionKey;
    }

    function generateSessionKey()
    {
        return md5(random_bytes(32) . '|' . microtime(true));
    }

    function getName()
    {
        return $this->Name;
    }

    function getUsername()
    {
        return $this->Username;
    }

    function getUserID()
    {
        return $this->UserID;
    }

    function getID()
    {
        return $this->UserID;
    }

    function getProfileComplete()
    {
        return $this->ProfileComplete;
    }

    function getDisplayName()
    {
        if (!empty($this->Name)) return $this->Name;
        return $this->Username;
    }

    function getReason()
    {
        return $this->reason;
    }

    function getCurrency()
    {
        return $this->Currency;
    }

    function hasRole($Role)
    {
        if (!$this->check_login()) return false;
        return ($Role == $this->Data['Role'] ? true: false);
    }

    function getAvatar($UserID = 0)
    {
        if ($UserID == 0) {
            if ($this->Data['Avatar'] == 'custom') {
                return $this->getCustomAvatar();
            } else {
                return '/img/avatar_' . $this->Data['Avatar'] . '.png';
            }
        } else {
            $Avatar = $this->getByUserID('Avatar', $UserID);
            if ($Avatar == 'custom') {
                return $this->getCustomAvatar($UserID);
            } else {
                return '/img/avatar_' . $Avatar . '.png';
            }
        }
    }

    function getCustomAvatar($UserID = 0)
    {
        if ($UserID == 0) {
            return 'data:image/jpeg;base64,' . base64_encode($this->Data['AvatarCustom']);
        } else {
            return 'data:image/jpeg;base64,' . base64_encode($this->getByUserID('AvatarCustom', $UserID));
        }
    }

    function getLastLogin($UserID = 0)
    {
        global $DB;
        if ($UserID == 0) $UserID = $this->UserID;
        $Data = $DB->fetch_assoc($DB->query('SELECT MAX(ActiveSince) AS LastLogin FROM user_sessions WHERE User = ' . $DB->int($UserID)));
        if (isset($Data['LastLogin'])) return $Data['LastLogin'];
        return 0;
    }

    function getByUserID($Key, $UserID)
    {
        global $DB;
        if (!isset($this->OtherUserData[$UserID])) {
            $this->OtherUserData[$UserID] = $DB->getOne('user', 'UserID = ' . $DB->int($UserID));
            unset($this->OtherUserData[$UserID]['Password']);
            unset($this->OtherUserData[$UserID]['LoginKey']);
            unset($this->OtherUserData[$UserID]['WC']);
            unset($this->OtherUserData[$UserID]['APP']);
            unset($this->OtherUserData[$UserID]['MPP']);
        }
        if (isset($this->OtherUserData[$UserID][$Key])) return $this->OtherUserData[$UserID][$Key];
        return false;
    }

    function exists($UserID)
    {
        global $DB;
        return $DB->exists('user', 'UserID = ' . $DB->int($UserID));
    }

    function get($Key)
    {
        if (isset($this->Data[$Key])) {
            return $this->Data[$Key];
        }
        return false;
    }

    function set($Key, $Val)
    {
        global $DB;
        $this->Data[$Key] = $Val;
        $DB->update('user', [$Key => $Val], 'UserID = ' . $DB->int($this->UserID));
        return true;
    }

    function encryptData($data)
    {
        return base64_encode(openssl_encrypt(serialize($data), 'BF-ECB', SECRET_COOKIE_KEY, OPENSSL_RAW_DATA));
    }

    function decryptData($data)
    {
        return @unserialize(@openssl_decrypt(@base64_decode($data), 'BF-ECB', SECRET_COOKIE_KEY, OPENSSL_RAW_DATA));
    }
}
