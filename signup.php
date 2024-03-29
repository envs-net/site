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

      <div id="main">
<div class="block">
<h1><em>signup</em></h1>
<h2><em>you wanna join - envs.net?</em></h2>
<p></p>
</div>
<pre>fill out this form and i'll get back to you with your account informations.</pre>
<br />

<?php include 'signup-handler.php'; ?>

<form method="post">
<label>desired username:<br />
<input class="form-control" type="text" name="username" value="<?=$_REQUEST["username"] ?? ""?>" maxlength="32" style="width: 260px;"></label>
<p></p>
<div id="left_box">
	<label>e-mail for account informations:<br />
	<input class="form-control" type="text" name="email" value="<?=$_REQUEST["email"] ?? ""?>" style="width: 260px;"></label>
</div>
<div>
	<label>confirm:<br />
	<input class="form-control" type="text" name="emailconfirm" value="<?=$_REQUEST["emailconfirm"] ?? ""?>" style="width: 260px;"></label>
</div>
<p></p>
<label>what interests you about envs.net?<br />
<textarea class="form-control" name="interest" rows="8" style="width: 560px;"><?=$_REQUEST["interest"] ?? ""?></textarea></label>
<p></p>
<label>openssh public key:<br />
<small>if you dont have a key, dont worry! check out our <a href="https://help.envs.net/help/#ssh" target="blank">help page</a> to ssh keys.</small><br />
<textarea class="form-control" name="sshkey" rows="8" style="width: 560px;"><?=$_REQUEST["sshkey"] ?? ""?></textarea></label>
<br />

<br />
<pre>
signing up implies that you agree to abide by our <a href="/coc/" target="blank">code of conduct</a>,
no drama. be respectful. have fun. we're all trying, and we're all in this together :)

<label><input class="form-control" type="checkbox" name="c_age" value="check" /> i am at least 16 years old.</label>
<label><input class="form-control" type="checkbox" name="iagree" value="check" /> i agree! i have read and understood the <a href="/privacy-policy/" target="blank">terms and conditions</a>.</label>

<input class="form-control" type="submit" value="submit">
</pre>
</form>

      </div>

<?php include 'footer.php'; ?>
