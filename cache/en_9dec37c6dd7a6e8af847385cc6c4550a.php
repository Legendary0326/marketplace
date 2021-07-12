<?php /***REALFILE: D:\xampp\htdocs\marketplace/nav/top_guest.php***/
$ls = $Language->getLanguages();
?><div class="container-fluid">
<div class="d-flex justify-content-end" style="border-top: 5px solid #378BE5;">
    <div><a style="color: #378BE5;" href="/login" class="btn btn-link top-guest">Login</a></div>
    <div><a style="color: #378BE5;" href="/register" class="btn btn-link top-guest">Register</a></div>
    <div class="dropdown dropbtnMenu">
        <div class="dropdown-toggle" style="color: #378BE5;">
            <button class="btn btn-link top-guest" style="color: #378BE5;">
            <?php echo $ls[$Language->getLanguage()]; ?> <span class="caret ml-n3"></span>
            </button>
        </div>
        <div class="dropdown-content">
<?php
$ls = $Language->getLanguages();
foreach ($ls as $ID => $Name) {
    echo '<a href="?setLanguage=' . $ID . '">' . $Name . '</a>' .nl;
}
?>
        </div>
    </div>
</div>
<div class="d-flex justify-content-start">
    <div><a href="<?php echo MAINURI; ?>"><img style="width: 100%; min-width: 270px; margin-bottom: 25px;" src="/img/logo.png" alt="Logo" title=""/></a></div>
</div>
</div>
