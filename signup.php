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
<h1><em>Signup</em></h1>
<h2><em>you wanna join - envs.net?</em></h2>
<br />
</div>
<pre>fill out this form and i'll get back to you with your account informations.</pre>
<br />

<?php include 'signup-mailer.php'; ?>

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
<label>ssh public key:<br />
<textarea class="form-control" name="sshkey" rows="8" style="width: 560px;"><?=$_REQUEST["sshkey"] ?? ""?></textarea></label>

<br />

<pre>
signing up implies that you agree to abide by our <a href="/coc/" target="blank">code of conduct</a>,
no drama. be respectful. have fun. we're all trying, and we're all in this together :)

<label><input class="form-control" type="checkbox" name="iagree" value="check" /> i agree! i have read and understood the terms and conditions.</label>

<input class="form-control" type="submit" value="submit">
</pre>
</form>

      </div>

<?php include 'footer.php'; ?>
