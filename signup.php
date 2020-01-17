<?php
  $title = "envs.net | signup";
  $desc = "envs.net | signup";

include 'header.php';
?>

  <body id="body" class="dark-mode">
    <div>

      <div class="button_back">
        <pre class="clean"><strong><a href="/">&lt; back</a></strong></pre>
      </div>

      <div id="main" style="padding-bottom: 4em;">
<div class="block">
<pre>
<h1><em>Signup</em></h1>
<h2><em>you wanna join - envs.net?</em></h2>
</pre>
</div>
<pre>
fill out this form and i'll get back to you with your account informations.
</pre>

<?php include 'signup-mailer.php'; ?>

<form method="post"><label>desired username:<br />
<input class="form-control" name="username" value="<?=$_REQUEST["username"] ?? ""?>" type="text" maxlength="32" style="width: 260px;"></label>
<br /><br />
<label>e-mail for account informations: &nbsp;&nbsp;&nbsp;&nbsp; confirm:<br />
<input class="form-control" name="email" value="<?=$_REQUEST["email"] ?? ""?>" type="text" style="width: 260px;">
<input class="form-control" name="emailconfirm" value="<?=$_REQUEST["emailconfirm"] ?? ""?>" type="text" style="width: 260px;"></label>
<br /><br />
<label>what interests you about envs.net?<br />
<textarea class="form-control" name="interest" rows="8" style="width: 560px;"><?=$_REQUEST["interest"] ?? ""?></textarea></label>
<br /><br />
<label>ssh public key:<br />
<textarea class="form-control" name="sshkey" rows="8" style="width: 560px;"><?=$_REQUEST["sshkey"] ?? ""?></textarea></label>
<pre>
if you don't have a key, don't worry! check out our <a href="https://help.envs.net/help/#ssh" target="blank">help page</a>
to ssh keys and make sure that you only put your pubkey here.

signing up implies that you agree to abide by our <a href="/coc/" target="blank">code of conduct</a>
no drama. be respectful. have fun. we're all trying, and we're all in this together :)

<input class="form-control" type="submit" value="submit">
</pre>
</form>

      </div>

<?php include 'footer.php'; ?>
