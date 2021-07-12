<?php
class LTC
{
    static private $thisCurrency = 'LTC';
    static private $lastError = false;

    static private function node($method, array $params = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, LTC_IP);
        curl_setopt($ch, CURLOPT_PORT, LTC_PORT);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, LTC_USER . ':' . LTC_PASS);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'method' => $method,
            'params' => $params
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
        $Address = self::node('getnewaddress', [LTC_ACCOUNT]);
        if ($Address === false) return false;
        $AddressInfo = self::node('getaddressinfo', [$Address]);
        if ($AddressInfo === false) return false;

        if ($AddressInfo) {
            $PrivateKey = isset($AddressInfo['hex']) ? $AddressInfo['hex'] : self::node('dumpprivkey', [$Address]);
            $NewData = [];
            $NewData['Address'] = $Address;
            $NewData['PublicKey'] = $AddressInfo['pubkey'];
            $NewData['PrivateKey'] = $PrivateKey;
            $NewData['Created'] = time();
            $NewData['User'] = $User->getID();
            $NewData['Currency'] = self::$thisCurrency;

            $DB->insert('crypto_addresses', $NewData);
            return $Address;
        }
        return false;
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

    static public function checkAddress($Address)
    {
        $Result = self::node('validateaddress', [$Address]);
        if (isset($Result['isvalid']) && $Result['isvalid'] === true) {
            return true;
        }
        return false;
    }

    static public function sendMoney($Address, $Amount)
    {
        $Result = self::node('sendtoaddress', [$Address, $Amount]);
        if (isset($Result['txid']) || (is_string($Result) && strlen($Result) == 64)) {
            return true;
        }
        return false;
    }
}