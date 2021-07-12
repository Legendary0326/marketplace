<?php
class Messages
{
    static function countUnreadMessages()
    {
        global $DB;
        global $User;
        $Data = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM messages WHERE Recipient = ' . $DB->int($User->getID()) . ' AND RecipientDeleted = 0 AND Readed IS NULL'));
        if (isset($Data['Num'])) return $Data['Num'];
        return false;
    }

    static function getMessagesIn()
    {
        global $DB;
        global $User;
        return $DB->fetch_all($DB->query('SELECT *, (SELECT Username FROM user WHERE UserID = Sender) AS SenderName, (SELECT Username FROM user WHERE UserID = Recipient) AS RecipientName FROM messages WHERE Recipient = ' . $DB->int($User->getID()) . ' AND RecipientDeleted = 0 ORDER BY Created DESC'), MYSQLI_ASSOC);
    }

    static function getMessagesOut()
    {
        global $DB;
        global $User;
        return $DB->fetch_all($DB->query('SELECT *, (SELECT Username FROM user WHERE UserID = Recipient) AS SenderName, (SELECT Username FROM user WHERE UserID = Recipient) AS RecipientName FROM messages WHERE Sender = ' . $DB->int($User->getID()) . ' AND SenderDeleted = 0 ORDER BY Created DESC'), MYSQLI_ASSOC);
    }

    static function delete($MessageID)
    {
        global $DB;
        global $User;
        $DB->query('UPDATE messages SET SenderDeleted = IF(Sender = ' . $DB->int($User->getID()) . ', 1, SenderDeleted), RecipientDeleted = IF(Recipient = ' . $DB->int($User->getID()) . ', 1, RecipientDeleted)' . ($User->hasRole('staff') || $User->hasRole('admin') ? ', ModeratorDeleted = 1' : '') . ' WHERE MessageID = ' . $DB->int($MessageID));
    }

    static function getMessage($MessageID)
    {
        global $DB;
        global $User;
        $Data = $DB->fetch_assoc($DB->query('SELECT *, (SELECT Username FROM user WHERE UserID = Sender) AS SenderName, (SELECT Username FROM user WHERE UserID = Recipient) AS RecipientName FROM messages WHERE MessageID = ' . $DB->int($MessageID) . ' AND ((Sender = ' . $DB->int($User->getID()) . ' AND SenderDeleted = 0) OR (Recipient = ' . $DB->int($User->getID()) . ' AND RecipientDeleted = 0)' . ($User->hasRole('staff') || $User->hasRole('admin') ? ' OR (Recipient = 0 AND ModeratorDeleted = 0)' : '') . ') ORDER BY Created DESC'));
        if (($Data['Recipient'] == $User->getID() || $Data['Recipient'] == 0) && is_null($Data['Readed'])) {
            $DB->query('UPDATE messages SET Readed = UNIX_TIMESTAMP() WHERE MessageID = ' . $DB->int($MessageID));
        }
        $Data['History'] = [];
        if (!is_null($Data['InReplyTo'])) {
            self::getHistory($Data['History'], $Data['InReplyTo']);
        }
        return $Data;
    }

    static private function getHistory(&$History, $MessageID)
    {
        global $DB;
        $Data = $DB->fetch_assoc($DB->query('SELECT *, (SELECT Username FROM user WHERE UserID = Sender) AS SenderName, (SELECT Username FROM user WHERE UserID = Recipient) AS RecipientName FROM messages WHERE MessageID = ' . $DB->int($MessageID)));
        $History[] = $Data;
        if (!is_null($Data['InReplyTo'])) {
            self::getHistory($Data['History'], $Data['InReplyTo']);
        }
    }

    static function exists($MessageID)
    {
        global $DB;
        global $User;
        return $DB->exists('messages', '((Sender = ' . $DB->int($User->getID()) . ' AND SenderDeleted = 0) OR (Recipient = ' . $DB->int($User->getID()) . ' AND RecipientDeleted = 0)' . ($User->hasRole('staff') || $User->hasRole('admin') ? ' OR (Recipient = 0 AND ModeratorDeleted = 0)' : '') . ') AND MessageID = ' . $DB->int($MessageID));
    }
}