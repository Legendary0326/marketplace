<?php
class XMR
{
    static private $thisCurrency = 'XMR';
    static private $lastError = false;

    static private function node($method, array $params = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://' . XMR_IP . ':' . XMR_PORT . '/json_rpc');
        curl_setopt($ch, CURLOPT_HTTPAUTH,  CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, XMR_USER . ':' . XMR_PASS);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'jsonrpc'   => '2.0',
            'id'        => '0',
            'method'    => $method,
            'params'    => $params
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch) >= 1) {
            self::$lastError = 'CURL-Error ' . curl_errno($ch) . ': ' . curl_error($ch);
            return false;
        }
        curl_close($ch);
        $info = json_decode($response, true);

        if (!empty($info["error"])) {
            self::$lastError = 'API-Error ' . $info['error']['code'] . ': ' . $info['error']['message'];
            return false;
        }
        return $info['result'];
    }

    static public function getLastError()
    {
        return self::$lastError;
    }

    static public function raw($method, array $params = [])
    {
        return self::node($method, $params);
    }

    static public function addAddress()
    {
        global $DB;
        global $User;
        $Address = self::node('create_address', ['account_index' => 0]);
        if ($Address === false || is_null($Address)) return false;
        $NewData = [];
        $NewData['Address'] = $Address['address'];
        $NewData['Created'] = time();
        $NewData['User'] = $User->getID();
        $NewData['Identifier'] = $Address['address_index'];
        $NewData['Currency'] = self::$thisCurrency;

        $DB->insert('crypto_addresses', $NewData);
        return $Address['address'];
    }

    static public function getLastReceived()
    {
        global $DB;
        global $User;
        return $DB->get('crypto_transactions', 'User = ' . $DB->int($User->getID()) . ' AND Currency = ' . $DB->string(self::$thisCurrency), 'ORDER BY Received DESC LIMIT 10');
    }

    static public function getLastWithdrawals()
    {
        global $DB;
        global $User;
        return $DB->get('crypto_withdrawals', 'User = ' . $DB->int($User->getID()) . ' AND Currency = ' . $DB->string(self::$thisCurrency), 'ORDER BY Date DESC LIMIT 10');
    }

    static public function toFloat($Amount)
    {
        return (float) (floor($Amount / 10000) / 100000000);
    }

    static public function toInt($Amount)
    {
        return (int) ($Amount * 1000000000000);
    }

    static public function checkAddress($Address)
    {
        $Result = self::node('validate_address', ['address' => $Address]);
        if (isset($Result['valid']) && $Result['valid'] === true) {
            return true;
        }
        return false;
    }

    static public function sendMoney($Address, $Amount)
    {
        $Result = self::node('transfer', ['destinations' => [['amount' => self::toInt($Amount), 'address' => $Address]], 'priority' => 0, 'ring_size' => 7]);
        if (isset($Result['amount'])) {
            return true;
        }
        return false;
    }
}