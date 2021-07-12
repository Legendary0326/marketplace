<?php
if (!defined('CRON_INCLUDED')) die('Permission denied');
$DeleteOlder = strtotime('-3 days');

// Start BTC
require_once '../libs/BTC.php';
$DB->delete('crypto_addresses', 'Created < ' . $DB->int($DeleteOlder));
$Addresses = $DB->get('crypto_addresses', 'Currency = ' . $DB->string('BTC'));
echo count($Addresses) . ' BTC address(es) to check' . nl;
if (count($Addresses) >= 1) {
    foreach ($Addresses as $Address) {
        //Looking for transactions
        $Info = BTC::raw('listreceivedbyaddress', [0, true, false, $Address['Address']]); //Get informations about transactions from node
        if (isset($Info[0]['txids']) && count($Info[0]['txids']) >= 1) { //Check for transactions
            foreach ($Info[0]['txids'] as $TXID) { //Get every txid
                $Transaction = BTC::raw('gettransaction', [$TXID]); //Get informations about the transaction
                if (isset($Transaction['details']) && count($Transaction['details']) >= 1) { //Check for details
                    foreach ($Transaction['details'] as $Detail) { //Get every detail
                        if ($Detail['category'] == 'receive') { //We only care about receiving transactions
                            $SQLDetail = $DB->getOne('crypto_transactions', 'Currency = ' . $DB->string('BTC') . ' AND Identifier = ' . $DB->string($TXID) . ' AND Address = ' . $DB->string($Detail['address'])); //Ask DB to check if we already know this detail
                            if ($SQLDetail === false) {
                                echo 'Error in database!' . nl;
                            } else if (is_null($SQLDetail)) { //Check if this is a new transaction
                                $NewData = [
                                    'User'          =>  $Address['User'],
                                    'Currency'      =>  'BTC',
                                    'Identifier'    =>  $TXID,
                                    'Received'      =>  $Transaction['timereceived'],
                                    'Address'       =>  $Detail['address'],
                                    'Amount'        =>  $Detail['amount'],
                                    'Confirmations' =>  $Transaction['confirmations']
                                ];
                                $DB->insert('crypto_transactions', $NewData); //Insert into Database
                                if ($Transaction['confirmations'] >= 3) { //Check if we can trust this transaction
                                    $DB->query('UPDATE user SET BTC = BTC + ' . $DB->float($Detail['amount']) . ' WHERE UserID = ' . $Address['User']); //Give user the received amount of coins
                                }
                            } else {
                                $NewData = [
                                    'Confirmations' =>  $Transaction['confirmations']
                                ];
                                $DB->update('crypto_transactions', $NewData, 'TransactionID = ' . $DB->int($SQLDetail['TransactionID'])); //Update Database
                                if ($SQLDetail['Confirmations'] <= 2 && $Transaction['confirmations'] >= 3) { //Check if we can trust this transaction
                                    $DB->query('UPDATE user SET BTC = BTC + ' . $DB->float($Detail['amount']) . ' WHERE UserID = ' . $Address['User']); //Give user the received amount of coins
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
// End BTC

// Start LTC
require_once '../libs/LTC.php';
$DB->delete('crypto_addresses', 'Created < ' . $DB->int($DeleteOlder));
$Addresses = $DB->get('crypto_addresses', 'Currency = ' . $DB->string('LTC'));
echo count($Addresses) . ' LTC address(es) to check' . nl;
if (count($Addresses) >= 1) {
    foreach ($Addresses as $Address) {
        //Looking for transactions
        $Info = LTC::raw('listreceivedbyaddress', [0, true, false, $Address['Address']]); //Get informations about transactions from node
        if (isset($Info[0]['txids']) && count($Info[0]['txids']) >= 1) { //Check for transactions
            foreach ($Info[0]['txids'] as $TXID) { //Get every txid
                $Transaction = LTC::raw('gettransaction', [$TXID]); //Get informations about the transaction
                if (isset($Transaction['details']) && count($Transaction['details']) >= 1) { //Check for details
                    foreach ($Transaction['details'] as $Detail) { //Get every detail
                        if ($Detail['category'] == 'receive') { //We only care about receiving transactions
                            $SQLDetail = $DB->getOne('crypto_transactions', 'Currency = ' . $DB->string('LTC') . ' AND Identifier = ' . $DB->string($TXID) . ' AND Address = ' . $DB->string($Detail['address'])); //Ask DB to check if we already know this detail
                            if ($SQLDetail === false) {
                                echo 'Error in database!' . nl;
                            } else if (is_null($SQLDetail)) { //Check if this is a new transaction
                                $NewData = [
                                    'User'          =>  $Address['User'],
                                    'Currency'      =>  'LTC',
                                    'Identifier'    =>  $TXID,
                                    'Received'      =>  $Transaction['timereceived'],
                                    'Address'       =>  $Detail['address'],
                                    'Amount'        =>  $Detail['amount'],
                                    'Confirmations' =>  $Transaction['confirmations']
                                ];
                                $DB->insert('crypto_transactions', $NewData); //Insert into Database
                                if ($Transaction['confirmations'] >= 3) { //Check if we can trust this transaction
                                    $DB->query('UPDATE user SET LTC = LTC + ' . $DB->float($Detail['amount']) . ' WHERE UserID = ' . $Address['User']); //Give user the received amount of coins
                                }
                            } else {
                                $NewData = [
                                    'Confirmations' =>  $Transaction['confirmations']
                                ];
                                $DB->update('crypto_transactions', $NewData, 'TransactionID = ' . $DB->int($SQLDetail['TransactionID'])); //Update Database
                                if ($SQLDetail['Confirmations'] <= 2 && $Transaction['confirmations'] >= 3) { //Check if we can trust this transaction
                                    $DB->query('UPDATE user SET LTC = LTC + ' . $DB->float($Detail['amount']) . ' WHERE UserID = ' . $Address['User']); //Give user the received amount of coins
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
// End LTC

// Start XMR
require_once '../libs/XMR.php';
$DB->delete('crypto_addresses', 'Created < ' . $DB->int($DeleteOlder));
$Addresses = $DB->get('crypto_addresses', 'Currency = ' . $DB->string('XMR'));
echo count($Addresses) . ' XMR address(es) to check' . nl;
if (count($Addresses) >= 1) {
    foreach ($Addresses as $Address) {
        //Looking for transactions
        $Transfers = XMR::raw('get_transfers', ['in' => true, 'subaddr_indices' => [(int) $Address['Identifier']]]); //Get informations about transactions from node
            if (isset($Transfers['in']) && count($Transfers['in']) >= 1) { //Check for transactions
            foreach ($Transfers['in'] as $Transfer) { //Get every transfer
                $Amount = XMR::toFloat($Transfer['amount']); //Change amount to float
                $SQLDetail = $DB->getOne('crypto_transactions', 'Currency = ' . $DB->string('XMR') . ' AND Identifier = ' . $DB->string($Transfer['txid']) . ' AND Address = ' . $DB->string($Transfer['address'])); //Ask DB to check if we already know this detail
                if ($SQLDetail === false) {
                    echo 'Error in database!' . nl;
                } else if (is_null($SQLDetail)) { //Check if this is a new transaction
                    $NewData = [
                        'User'          =>  $Address['User'],
                        'Currency'      =>  'XMR',
                        'Identifier'    =>  $Transfer['txid'],
                        'Received'      =>  $Transfer['timestamp'],
                        'Address'       =>  $Transfer['address'],
                        'Amount'        =>  $Amount,
                        'Confirmations' =>  $Transfer['confirmations']
                    ];
                    $DB->insert('crypto_transactions', $NewData); //Insert into Database
                    if ($Transfer['confirmations'] >= 10) { //Check if we can trust this transaction
                        $DB->query('UPDATE user SET XMR = XMR + ' . $DB->float($Amount) . ' WHERE UserID = ' . $Address['User']); //Give user the received amount of coins
                    }
                } else {
                    $NewData = [
                        'Confirmations' =>  $Transfer['confirmations']
                    ];
                    $DB->update('crypto_transactions', $NewData, 'TransactionID = ' . $DB->int($SQLDetail['TransactionID'])); //Update Database
                    if ($SQLDetail['Confirmations'] <= 9 && $Transfer['confirmations'] >= 10) { //Check if we can trust this transaction
                        $DB->query('UPDATE user SET XMR = XMR + ' . $DB->float($Amount) . ' WHERE UserID = ' . $Address['User']); //Give user the received amount of coins
                    }
                }
            }
        }
    }
}
// End XMR
