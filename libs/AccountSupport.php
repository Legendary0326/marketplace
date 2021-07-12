<?php
class AccountSupport
{
    protected $Data = [];
    protected $UserID;

    public function __construct($UserID)
    {
        global $User;
        global $DB;
        $this->UserID = $UserID;
        if ($User->hasRole('admin')) {
            $this->Data = $DB->getOne('user', 'UserID = ' . $DB->int($UserID));
        } else {
            $this->Data = $DB->getOne('user', 'UserID = ' . $DB->int($UserID) . ' AND (Role = ' . $DB->string('user') . ' OR Role = ' . $DB->string('vendor') . ')');
        }
    }

    function found()
    {
        return !is_null($this->Data);
    }

    function getAvatar()
    {
        if (!$this->found()) return false;
        if ($this->Data['Avatar'] == 'custom') {
            return $this->getCustomAvatar();
        } else {
            return '/img/avatar_' . $this->Data['Avatar'] . '.png';
        }
    }

    function getCustomAvatar()
    {
        if (!$this->found()) return false;
        return 'data:image/jpeg;base64,' . base64_encode($this->Data['AvatarCustom']);
    }

    function getLastLogin()
    {
        if (!$this->found()) return false;
        global $DB;
        $Data = $DB->fetch_assoc($DB->query('SELECT MAX(ActiveSince) AS LastLogin FROM user_sessions WHERE User = ' . $DB->int($this->UserID)));
        if (isset($Data['LastLogin'])) return $Data['LastLogin'];
        return 0;
    }

    function get($Key)
    {
        if (!$this->found()) return false;
        if (isset($this->Data[$Key])) {
            return $this->Data[$Key];
        }
        return false;
    }

    function set($Key, $Val)
    {
        if (!$this->found()) return false;
        global $DB;
        $this->Data[$Key] = $Val;
        $DB->update('user', [$Key => $Val], 'UserID = ' . $DB->int($this->UserID));
        return true;
    }
}
