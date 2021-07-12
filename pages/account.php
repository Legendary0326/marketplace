<?php
$Countries = $Language->getCountries();
$currencies = Currencies::getCurrencies();
$HTMLHead->addNav('cat_search');
$avatars = ['red', 'yellow', 'blue', 'purple', 'green', 'grey', 'custom'];
$languages = ['en', 'nl', 'fr', 'es', 'de', 'ru', 'it', 'se', 'fi', 'no', 'he', 'ar', 'hi', 'zh', 'tr', 'pl', 'hu', 'vi', 'ja', 'ro', 'cs', 'uk', 'el', 'al'];
$AvatarSize = 128;

if ($Pages->inPath('enable2fa')) {
    if (isset($_POST['answer'])) {
        if (gpg::validateCode($_POST['answer'])) {
            $User->set('2FA', 1);
            $User->logout();
            $Pages->redirect('login/enabled2FA');
        } else {
            $Pages->redirect('account/errorPGP');
        }
    }
    $Pages->setLayout('small');
    $code = gpg::newCode();
    if ($code === false) {
        $Pages->redirect('account/errorPGP');
    }
?>
<h1 class="text-info mt-4"><lang>2FA-Verification</lang></h1>

<form action="/account/enable2fa" method="post">
    <div class="form-group">
        <label class="text-muted mt-2"><lang>Encrypted Verification Code</lang>:</label>
    </div>
    <div class="form-group">
        <div class="mt-3 mb-3" style="line-height: 1;">
            <span class="font-weight-light text-info"><lang>After you enable the 2FA verification please check if your PGP public key is correct by decrypting the displayed 2FA code. After a correct confirmation, you will be logged out for security reasons. Please log in again using 2FA!</lang></span>
        </div>
    </div>
    <br>
    <div class="form-group">
        <h6 class="text-primary" style="white-space:pre"><?php echo $code; ?></h6>
    </div>
    <div class="form-group">
        <label class="text-muted font-weight-bold mt-3"><lang>Please Enter Your Decrypted Verification Code</lang></label>
        <input type="text" class="form-control" name="answer" autocomplete="off"<?php devCodeGPG(); ?>>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><lang>Verify And Enable 2FA</lang></button>
</form>

<div class="form-group mt-5">
    <div class="col-md-12">
        <img class="mx-auto d-block" src="<?php
echo 'data:image/svg+xml;base64,' . base64_encode(QRcode::svg($code, md5($code), false, QR_ECLEVEL_L, false, false, 0));
?>" alt="" title="" style="max-width: 15%; min-width: 200px; max-height: auto; min-height: auto;"/>
    </div>
</div>
<?php
    return;
}

if (isset($_GET['setAvatar'])) {
    if (array_search($_GET['setAvatar'], $avatars) !== false && $_GET['setAvatar'] != 'custom') {
        $User->set('Avatar', $_GET['setAvatar']);
    }
    $Pages->redirect('account');
}

if (Forms::isPost()) {
    $errors = '';
    if (Forms::validateList('avatar', $avatars)) {
        $User->set('Avatar', $_POST['avatar']);
    }
    if (isset($_POST['PGP']) && strlen($_POST['PGP']) >= 1 && !$User->hasRole('user')) {
        $_POST['2FA'] = 1;
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
            $User->set('AvatarCustom', file_get_contents($tmpfile));
            $User->set('Avatar', 'custom');
            @unlink($tmpfile);
        } else if ($_FILES['uploadAvatar']['type'] == 'image/jpeg') {
            $srcimage = imagecreatefromjpeg($_FILES['uploadAvatar']['tmp_name']);
            $dstimage = imagecreatetruecolor($AvatarSize, $AvatarSize);
            imagecopyresampled($dstimage, $srcimage, 0, 0, 0, 0, $AvatarSize, $AvatarSize, imagesx($srcimage), imagesy($srcimage));
            $tmpfile = tempnam(TMPPATH, 'ava');
            imagejpeg($dstimage, $tmpfile, 80);
            imagedestroy($dstimage);
            $User->set('AvatarCustom', file_get_contents($tmpfile));
            $User->set('Avatar', 'custom');
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
        $User->set('Communicate', implode(',', $newLangs));
    }
    if (Forms::validateString('ICQ', ['min' => 1, 'max' => 20])) {
        $User->set('ICQ', trim($_POST['ICQ']));
    }
    if (Forms::validateString('AOL', ['min' => 1, 'max' => 50])) {
        $User->set('AOL', trim($_POST['AOL']));
    }
    if (Forms::validateString('Jabber', ['min' => 1, 'max' => 20])) {
        $User->set('Jabber', trim($_POST['Jabber']));
    }
    if (Forms::validateString('EMail', ['min' => 1, 'max' => 200])) {
        $User->set('EMail', trim($_POST['EMail']));
    }
    if (Forms::validateString('APP', ['min' => 4, 'max' => 100])) {
        $User->set('APP', trim($_POST['APP']));
    }
    if (Forms::validateList('Currency', array_keys($currencies))) {
        $User->set('Currency', $_POST['Currency']);
    }
    if (Forms::validateList('Location', array_keys($Countries))) {
        $User->set('Location', $_POST['Location']);
    }
    if (Forms::validateString('PGP', ['min' => 1])) {
        $User->set('PGP', trim($_POST['PGP']));
    }
    $oldTFA = $User->get('2FA');
    $TFA = (bool) (isset($_POST['2FA']) ? $_POST['2FA'] : false);
    if (empty($User->get('PGP'))) $TFA = false;
    if ($oldTFA != $TFA && $TFA == 1) {
        $Pages->redirect('account/enable2fa');
    } else if ($oldTFA != $TFA && $TFA == 0 && ($User->hasRole('vendor') || $User->hasRole('staff') || $User->hasRole('admin'))) {
        $Pages->redirect('account/2famandatory');
    } else {
        $User->set('2FA', $TFA ? 1 : 0);
        $Pages->redirect('account/saved');
    }
}
$Communicate = explode(',', $User->get('Communicate'));
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
<?php if ($User->hasRole('user')) { ?>
        <div class="col-md-6 px-2 text-nowrap" id="borderPageHeader">
            <a class="text-white" href="/changepassword" class="btn btn-link"><lang>Change Passwords</lang></a>
        </div>
        <div class="col-md-6 px-2 text-nowrap">
            <a class="text-white" href="/vendorform" class="btn btn-link"><lang>Become a Vendor</lang></a>
        </div>
<?php } else { ?>
        <div class="col-md-12 mx-auto text-nowrap">
            <a class="text-white" href="/changepassword" class="btn btn-link"><lang>Change Passwords</lang></a>
        </div>
<?php } ?>
    </div>
</div>

<?php
if ($Pages->inPath('saved')) echo Alerts::success('<lang>Settings Changed Successfully.</lang>', 'mt-3');
if ($Pages->inPath('errorPGP')) echo Alerts::danger('<lang>Sorry, There Was an Error! the Time Limit Has Been Exceeded, You Entered an Incorrect Code and/or Your Pgp-Key Is Invalid or 2fa Is Maybe Not Enable!</lang>', 'mt-3');
if ($Pages->inPath('2famandatory')) echo Alerts::danger('<lang>2FA Is Mandatory for Your Account!</lang>', 'mt-3');
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>To Change Your Account Settings</lang>...</h3>
        </div>
    </div>
</div>

<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            <lang>Please complete your profile below. Providing the following information is voluntary.</lang>
            <br>
            <lang>Required fields for vendors are marked in</lang>&nbsp;<span class="text-danger"><lang>Red</lang></span>!
            <br>
            <lang>For security reasons, we recommend you to change your password and your withdrawal code (WDC) regularly!</lang>
            <br>
            <lang>In addition to this, it is also advisable to change your APP regularly and to make sure that your APP is displayed whenever you log into your account.</lang>
        </div>
    </div>
</div>
<?php } ?>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <lang>Your Current Avatar</lang>...
        </div>
        <div style="width: 25%; height: 50px;">
        <img class="p-1 ml-3" src="<?php echo $User->getAvatar(); ?>" alt="item" title="" style="max-width: 80px; height: auto;"/>
        </div>
    </div>
</div>

<form action="" method="post" enctype="multipart/form-data">
    <div class="container mt-5">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Please Choose Your Avatar</lang>...
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
                <lang>Or Upload Your Own Avatar</lang>...
            </div>
            <div class="form-group ml-3">
                <input type="file" class="form-control-file" name="uploadAvatar">
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>I Can Communicate in the Following Languages</lang>...
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
                <lang>Enter Your ICQ</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="ICQ" placeholder="" value="<?php echo htmlentities($User->get('ICQ'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Enter Your AOL</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="AOL" placeholder="" value="<?php echo htmlentities($User->get('AOL'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Enter Your Jabber</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Jabber" placeholder="" value="<?php echo htmlentities($User->get('Jabber'));?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Enter Your Email</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="email" class="form-control" name="EMail" placeholder="" value="<?php echo htmlentities($User->get('EMail'));?>">
                </div>
            </div>
        </div>
    </div>

<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Change Your Anti-Phishing Phrase (APP)</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="APP" placeholder="" value="<?php echo htmlentities($User->get('APP'));?>">
                </div>
            </div>
        </div>
    </div>
<?php } ?>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Change Your Base Currency</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <select class="form-control" name="Currency">
<?php
foreach ($currencies as $currency) {
    echo '<option value="' . $currency['Code'] . '"' . Forms::selectedVal($User->get('Currency'), $currency['Code']) . '>' . $currency['Code'] . ' (' . $currency['Name'] . ')</option>' . nl;
}
?>
                    </select>
                </div>
            </div>
        </div>
    </div>

<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-danger mb-2">
                <lang>Choose Your Location (Country of Dispatch)</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <select class="form-control" id="Location" name="Location">
<?php
if (empty($User->get('Location'))) {
    echo '<option><lang>Choose a Country</lang></option>' . nl;
}
foreach ($Countries as $CountryID => $Country) {
    echo '<option value="' . $CountryID . '"' . Forms::selectedVal($User->get('Location'), $CountryID) . '>' . $Country . '</option>' . nl;
}
?>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

    <div class="container mt-3">
        <div class="row py-1">
<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
            <div class="col-md-12 font-weight-light text-danger mb-2">
<?php } else { ?>
            <div class="col-md-12 font-weight-light text-muted mb-2">
<?php } ?>
                <lang>Enter Your PGP Public Key</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control overflow-auto" rows="5" placeholder="" name="PGP"><?php echo htmlentities($User->get('PGP')); ?></textarea>
                </div>
            </div>
        </div>
    </div>

<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-danger mb-2">
                <lang>Enable 2FA Verification</lang>...&nbsp;<small class="font-weight-light text-muted">(<lang>Upload PGP Public Key First</lang>!)</small>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="2FA" value="1"<?php echo Forms::checkedVal($User->get('2FA'), 1);
if (!$User->hasRole('user') && $User->get('2FA') == 1) echo ' disabled';
?>>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block"><lang>Update Profile</lang></button>
            </div>
        </div>
    </div>
</form>