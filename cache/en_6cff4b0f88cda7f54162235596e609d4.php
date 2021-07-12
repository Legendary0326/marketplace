<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/account-support.php***/
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$Countries = $Language->getCountries();
$currencies = Currencies::getCurrencies();
$HTMLHead->addNav('cat_search');
$avatars = ['red', 'yellow', 'blue', 'purple', 'green', 'grey', 'custom'];
$languages = ['en', 'nl', 'fr', 'es', 'de', 'ru', 'it', 'se', 'fi', 'no', 'he', 'ar', 'hi', 'zh', 'tr', 'pl', 'hu', 'vi', 'ja', 'ro', 'cs', 'uk', 'el', 'al'];
$AvatarSize = 128;

$Path = $Pages->getPath();
$UserID = intval($Path[0]);
$Account = new AccountSupport($UserID);
if (!$Account->found()) {
    echo Alerts::danger('User Not Found!', 'mt-3');
    return;
}

if (isset($_GET['setAvatar'])) {
    if (array_search($_GET['setAvatar'], $avatars) !== false && $_GET['setAvatar'] != 'custom') {
        $Account->set('Avatar', $_GET['setAvatar']);
    }
    $Pages->redirect('account');
}

if (Forms::isPost()) {
    if (isset($_POST['VendorFee'])) {
        $Account->set('VendorFee', guessMoney($_POST['VendorFee']));
    } else if (isset($_POST['Scoring'])) {
        $Account->set('Scoring', min(guessMoney($_POST['Scoring']), 100));
        Ranking::recalculateRanks($UserID);
    } else {
        $errors = '';
        if (Forms::validateList('avatar', $avatars)) {
            $Account->set('Avatar', $_POST['avatar']);
        }
        if (isset($_FILES['uploadAvatar']['error']) && $_FILES['uploadAvatar']['error'] === 0) {
            $imageType = $_FILES['uploadAvatar']['type'];
            if ($_FILES['uploadAvatar']['type'] == 'image/png') {
                $srcimage = imagecreatefrompng($_FILES['uploadAvatar']['tmp_name']);
                imagealphablending($srcimage, true);
                imagesavealpha($srcimage, true);
                $dstimage = imagecreatetruecolor($AvatarSize, $AvatarSize);
                imagecopyresampled($dstimage, $srcimage, 0, 0, 0, 0, $AvatarSize, $AvatarSize, imagesx($srcimage), imagesy($srcimage));
                $tmpfile = tempnam(TMPPATH, 'ava');
                imagejpeg($dstimage, $tmpfile, 80);
                imagedestroy($dstimage);
                $Account->set('AvatarCustom', file_get_contents($tmpfile));
                $Account->set('Avatar', 'custom');
                @unlink($tmpfile);
            } else if ($_FILES['uploadAvatar']['type'] == 'image/jpeg') {
                $srcimage = imagecreatefromjpeg($_FILES['uploadAvatar']['tmp_name']);
                $dstimage = imagecreatetruecolor($AvatarSize, $AvatarSize);
                imagecopyresampled($dstimage, $srcimage, 0, 0, 0, 0, $AvatarSize, $AvatarSize, imagesx($srcimage), imagesy($srcimage));
                $tmpfile = tempnam(TMPPATH, 'ava');
                imagejpeg($dstimage, $tmpfile, 80);
                imagedestroy($dstimage);
                $Account->set('AvatarCustom', file_get_contents($tmpfile));
                $Account->set('Avatar', 'custom');
                @unlink($tmpfile);
            }
        }
        if (isset($_POST['language'])) {
            $newLangs = [];
            foreach ($_POST['language'] as $language) {
                if (array_search($language, $languages) !== false) {
                    $newLangs[] = $language;
                }
            }
            $Account->set('Communicate', implode(',', $newLangs));
        }
        if (Forms::validateString('ICQ', ['min' => 1, 'max' => 20])) {
            $Account->set('ICQ', trim($_POST['ICQ']));
        }
        if (Forms::validateString('AOL', ['min' => 1, 'max' => 50])) {
            $Account->set('AOL', trim($_POST['AOL']));
        }
        if (Forms::validateString('Jabber', ['min' => 1, 'max' => 20])) {
            $Account->set('Jabber', trim($_POST['Jabber']));
        }
        if (Forms::validateString('EMail', ['min' => 1, 'max' => 200])) {
            $Account->set('EMail', trim($_POST['EMail']));
        }
        if (Forms::validateString('APP', ['min' => 4, 'max' => 100])) {
            $Account->set('APP', trim($_POST['APP']));
        }
        if (Forms::validateList('Currency', array_keys($currencies))) {
            $Account->set('Currency', $_POST['Currency']);
        }
        if (Forms::validateList('Location', array_keys($Countries))) {
            $Account->set('Location', $_POST['Location']);
        }
        if (Forms::validateString('PGP', ['min' => 1])) {
            $Account->set('PGP', trim($_POST['PGP']));
        }
        $Account->set('2FA', isset($_POST['2FA']) ? 1 : 0);
        $Account->set('BlockTransactions', isset($_POST['BlockTransactions']) ? 1 : 0);
        $Account->set('BlockLogin', isset($_POST['BlockLogin']) ? 1 : 0);
    }
    $Pages->redirect('account-support/' . $UserID . '/saved');
}
$Communicate = explode(',', $Account->get('Communicate'));

if ($Pages->inPath('saved')) echo Alerts::success('Settings Changed Successfully.', 'mt-3');

if ($Account->get('Role') == 'user' || $Account->get('Role') == 'vendor') { ?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Change Account Parameters:</h3>
        </div>
    </div>
</div>

<?php if ($Account->get('Role') == 'vendor') { ?>
<form action="/account-support/<?php echo $UserID; ?>" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Current Commission Fee:
            </div>
            <div class="col-4 col-md-2">
                <div class="input-group mb-2">
                    <input type="text" class="form-control text-right" name="VendorFee" value="<?php echo htmlentities($Account->get('VendorFee'));?>"><span class="ml-2 mt-1">%</span>
                </div>
            </div>
            <div class="col-8 col-md-10">
                <button class="btn-sm btn-primary ml-1" type="submit">Edit</button>
            </div>
        </div>
    </div>
</form>
<?php } ?>

<form action="/account-support/<?php echo $UserID; ?>" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Current Score:
            </div>
            <div class="col-4 col-md-2">
                <div class="input-group mb-2">
                    <input type="text" class="form-control text-right" name="Scoring" value="<?php echo htmlentities($Account->get('Scoring'));?>"><span class="ml-2 mt-1">%</span>
                </div>
            </div>
            <div class="col-8 col-md-10">
                <button class="btn-sm btn-primary ml-1" type="submit">Edit</button>
            </div>
        </div>
    </div>
</form>
<?php } ?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>To Change Your Account Settings...</h3>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            Your Current Avatar...
        </div>
        <div style="width: 25%; height: 50px;">
        <img class="p-1 ml-3" src="<?php echo $Account->getAvatar(); ?>" alt="item" title="" style="max-width: 80px; height: auto;"/>
        </div>
    </div>
</div>

<form action="/account-support/<?php echo $UserID; ?>" method="post" enctype="multipart/form-data">
    <div class="container mt-5">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Please Choose Your Avatar...
            </div>
            <div class="col-md-12">
<?php
foreach ($avatars as $avatar) {
    if ($avatar == 'custom') continue;
    echo '<a href="?setAvatar=' . $avatar . '"><img class="p-1" src="/img/avatar_' . $avatar . '.png" alt="avatar" title="" style="width: 60px; height: 60px;"/></a>' . nl;
}
?>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Or Upload Your Own Avatar...
            </div>
            <div class="form-group ml-3">
                <input type="file" class="form-control-file" name="uploadAvatar">
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                I Can Communicate in the Following Languages...
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1" id="exchange">
            <div class="col-md-2">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="en" name="language[]" id="language_en"<?php echo Forms::checkedVal($Communicate, 'en'); ?>>
                    <label class="form-check-label" for="language_en">English</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="nl" name="language[]" id="language_nl"<?php echo Forms::checkedVal($Communicate, 'nl'); ?>>
                    <label class="form-check-label" for="language_nl">Nederlands</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="fr" name="language[]" id="language_fr"<?php echo Forms::checkedVal($Communicate, 'fr'); ?>>
                    <label class="form-check-label" for="language_fr">Français</label>
            </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="es" name="language[]" id="language_es"<?php echo Forms::checkedVal($Communicate, 'es'); ?>>
                    <label class="form-check-label" for="language_es">Español</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="de" name="language[]" id="language_de"<?php echo Forms::checkedVal($Communicate, 'de'); ?>>
                    <label class="form-check-label" for="language_de">Deutsch</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="ru" name="language[]" id="language_ru"<?php echo Forms::checkedVal($Communicate, 'ru'); ?>>
                    <label class="form-check-label" for="language_ru">Русский</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="it" name="language[]" id="language_it"<?php echo Forms::checkedVal($Communicate, 'it'); ?>>
                    <label class="form-check-label" for="language_it">Italiano</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="se" name="language[]" id="language_se"<?php echo Forms::checkedVal($Communicate, 'se'); ?>>
                    <label class="form-check-label" for="language_se">Svenska</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="fi" name="language[]" id="language_fi"<?php echo Forms::checkedVal($Communicate, 'fi'); ?>>
                    <label class="form-check-label" for="language_fi">Suomalainen</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="no" name="language[]" id="language_no"<?php echo Forms::checkedVal($Communicate, 'no'); ?>>
                    <label class="form-check-label" for="language_no">Norsk</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="he" name="language[]" id="language_he"<?php echo Forms::checkedVal($Communicate, 'he'); ?>>
                    <label class="form-check-label" for="language_he">עברית</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="ar" name="language[]" id="language_ar"<?php echo Forms::checkedVal($Communicate, 'ar'); ?>>
                    <label class="form-check-label" for="language_ar">العربية</label>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-0">
        <div class="row py-0" id="balance">
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="hi" name="language[]" id="language_hi"<?php echo Forms::checkedVal($Communicate, 'hi'); ?>>
                    <label class="form-check-label" for="language_hi">हिंदी</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="zh" name="language[]" id="language_zh"<?php echo Forms::checkedVal($Communicate, 'zh'); ?>>
                    <label class="form-check-label" for="language_zh">中文</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="tr" name="language[]" id="language_tr"<?php echo Forms::checkedVal($Communicate, 'tr'); ?>>
                    <label class="form-check-label" for="language_tr">Türkçe</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="pl" name="language[]" id="language_pl"<?php echo Forms::checkedVal($Communicate, 'pl'); ?>>
                    <label class="form-check-label" for="language_pl">Polski</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="hu" name="language[]" id="language_hu"<?php echo Forms::checkedVal($Communicate, 'hu'); ?>>
                    <label class="form-check-label" for="language_hu">Magyar</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="vi" name="language[]" id="language_vi"<?php echo Forms::checkedVal($Communicate, 'vi'); ?>>
                    <label class="form-check-label" for="language_vi">Tiếng Việt</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="ja" name="language[]" id="language_ja"<?php echo Forms::checkedVal($Communicate, 'ja'); ?>>
                    <label class="form-check-label" for="language_ja">日本語</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="ro" name="language[]" id="language_ro"<?php echo Forms::checkedVal($Communicate, 'ro'); ?>>
                    <label class="form-check-label" for="language_ro">Română</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="cs" name="language[]" id="language_cs"<?php echo Forms::checkedVal($Communicate, 'cs'); ?>>
                    <label class="form-check-label" for="language_cs">Čeština</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="uk" name="language[]" id="language_uk"<?php echo Forms::checkedVal($Communicate, 'uk'); ?>>
                    <label class="form-check-label" for="language_uk">Українською</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="el" name="language[]" id="language_el"<?php echo Forms::checkedVal($Communicate, 'el'); ?>>
                    <label class="form-check-label" for="language_el">Ελληνικά</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="al" name="language[]" id="language_al"<?php echo Forms::checkedVal($Communicate, 'al'); ?>>
                    <label class="form-check-label" for="language_al">Shqiptar</label>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your ICQ...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="ICQ" placeholder="" value="<?php echo htmlentities($Account->get('ICQ'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your AOL...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="AOL" placeholder="" value="<?php echo htmlentities($Account->get('AOL'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your Jabber...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Jabber" placeholder="" value="<?php echo htmlentities($Account->get('Jabber'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your Email...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="email" class="form-control" name="EMail" placeholder="" value="<?php echo htmlentities($Account->get('EMail'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Change Your Anti-Phishing Phrase (APP)...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="APP" placeholder="" value="<?php echo htmlentities($Account->get('APP'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Change Your Base Currency...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <select class="form-control" name="Currency">
<?php
foreach ($currencies as $currency) {
    echo '<option value="' . $currency['Code'] . '"' . Forms::selectedVal($Account->get('Currency'), $currency['Code']) . '>' . $currency['Code'] . ' (' . $currency['Name'] . ')</option>' . nl;
}
?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Choose Your Location (Country of Dispatch)...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <select class="form-control" id="Location" name="Location">
<?php
if (empty($Account->get('Location'))) {
    echo '<option>Choose a Country</option>' . nl;
}
foreach ($Countries as $CountryID => $Country) {
    echo '<option value="' . $CountryID . '"' . Forms::selectedVal($Account->get('Location'), $CountryID) . '>' . $Country . '</option>' . nl;
}
?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your PGP Public Key...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control overflow-auto" rows="5" placeholder="" name="PGP"><?php echo htmlentities($Account->get('PGP')); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enable 2FA Verification...&nbsp;<small class="font-weight-light text-muted">(Upload PGP Public Key First!)</small>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="2FA" value="1"<?php echo Forms::checkedVal($Account->get('2FA'), 1); ?>>
                </div>
            </div>
        </div>
    </div>

<?php if ($Account->get('Role') == 'user' || $Account->get('Role') == 'vendor') { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-bold text-danger mb-2">
                Limitations
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="BlockTransactions" value="1"<?php echo Forms::checkedVal($Account->get('BlockTransactions'), 1); ?>>
                    <span class="ml-4 text-danger">Block transactions</span>
                    <input class="form-check-input ml-1" type="checkbox" name="BlockLogin" value="1"<?php echo Forms::checkedVal($Account->get('BlockLogin'), 1); ?>>
                    <span class="ml-4 text-danger">Block login</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
            </div>
        </div>
    </div>
</form>