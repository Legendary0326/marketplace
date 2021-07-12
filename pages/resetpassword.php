<?php
$Pages->setLayout('small');

if (Forms::isPost()) {
    $errors = [];
    if (!Forms::validateString('MPP', ['min' => 32, 'max' => 32])) {
        $errors['Username_MPP'] = true;
    }
    if (!Forms::validateUsername('Username')) {
        $errors['Username_MPP'] = true;
    } else {
        $UserData = $DB->getOne('user', 'Username = ' . $DB->string($_POST['Username']));
        if (!isset($UserData) || $UserData === false) {
            $errors['Username_MPP'] = true;
        } else if (!Password::verify($_POST['MPP'], $UserData['MPP'])) {
            $errors['Username_MPP'] = true;
        }
    }
    if (!Forms::validatePassword('Password', 'PasswordRepeat')) {
        $errors['Password'] = true;
    }
    if (!Captcha::verify()) {
        $errors['Captcha'] = true;
    }
    if (count($errors) == 0) {
        $NewData = [
            'Password'      =>  Password::hash($_POST['Password'])
        ];
        $DB->update('user', $NewData , 'UserID = ' . $DB->int($UserData['UserID']));
        $Pages->redirect('login/resetpasswordsuccess');
    }
}
?>
<h1 class="text-info"><lang>Reset Password</lang></h1>
<?php
if (isset($errors['Username_MPP'])) {
    echo Alerts::danger('<lang>Sorry, Your Username and/or Mpp Is Incorrect. Please Try Again.</lang>');
}
?>
<form action="/resetpassword" method="post">
    <div class="form-group mt-4">
        <label for="Username"><lang>Username</lang></label>
        <input type="text" class="form-control<?php if (isset($errors['Username_MPP'])) echo ' is-invalid'; ?>" id="Username" name="Username">
    </div>
    <div class="form-group">
        <label for="MPP"><lang>MPP</lang></label>
        <input type="text" class="form-control<?php if (isset($errors['Username_MPP'])) echo ' is-invalid'; ?>" id="MPP" name="MPP" autocomplete="off">
    </div>
    <div class="form-group">
        <label for="Password"><lang>New Password</lang></label>
        <input type="password" class="form-control<?php if (isset($errors['Password'])) echo ' is-invalid'; ?>" id="Password" name="Password" aria-describedby="PasswordInfo">
        <small id="PasswordInfo" class="form-text text-muted"><?php printf('<lang>Please use atleast %1$d characters. Use atleast one letter, number and special character.</lang>', 8); ?></small>
    </div>
    <div class="form-group">
        <label for="PasswordRepeat"><lang>Repeat Password</lang></label>
        <input type="password" class="form-control<?php if (isset($errors['Password'])) echo ' is-invalid'; ?>" id="PasswordRepeat" name="PasswordRepeat" aria-describedby="PasswordRepeatInfo">
        <small id="PasswordRepeatInfo" class="form-text text-muted"><lang>Please Reapeat the Password.</lang></small>
    </div>
    <div class="form-group">
        <img src="<?php echo Captcha::get(); ?>" alt="" title="Captcha" />
    </div>
    <div class="form-group">
        <label for="Captcha">Captcha</label>
        <input type="text" class="form-control" id="Captcha" name="Captcha" autocomplete="off">
    </div>
    <button type="submit" class="btn btn-primary btn-block"><lang>Reset Password</lang></button>
</form>
<p class="pt-3 text-center"><lang>Do You Need a New Account?</lang> <a href="/register"><lang>Click Here To Create It</lang></a></p>
