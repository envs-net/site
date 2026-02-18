<?php
	$title = "envs.net | signup";
	$desc = "envs.net | signup";

include 'neoenvs_header.php';
?>

<body id="body">

<!-- Back button -->
<nav class="sidenav">
	<a href="/">
		<img src="https://envs.net/img/envs_logo_200x200.png" class="site-icon" title="Back to the envs.net homepage">
	</a>
</nav>

<!-- main panel -->
<main class="content">
	<div class="block">
		<h1>signup</h1>
		<h2>you wanna join - envs.net?</h2>
	</div>

	<p>fill out this form and i'll get back to you with your account informations.</p>

<?php include 'signup-handler.php'; ?>

	<form method="post">
		<label>desired username:<br />
		<input class="form-control" type="text" name="username" value="<?=$_REQUEST["username"] ?? ""?>" maxlength="32"></label>
		<div class="form-half-half-row">
			<label>e-mail for account informations:<br />
			<input class="form-control" type="text" name="email" value="<?=$_REQUEST["email"] ?? ""?>"></label>
			<label>confirm your e-mail:<br />
			<input class="form-control" type="text" name="emailconfirm" value="<?=$_REQUEST["emailconfirm"] ?? ""?>"></label>
		</div>
		<label>what interests you about envs.net?<br />
		<textarea class="form-control" name="interest" rows="8"><?=$_REQUEST["interest"] ?? ""?></textarea></label>
		<label>openssh public key:<br />
		<small>if you dont have a key, dont worry! check out our <a href="https://help.envs.net/help/#ssh" target="blank">help page</a> to ssh keys.</small><br />
		<textarea class="form-control" name="sshkey" rows="8"><?=$_REQUEST["sshkey"] ?? ""?></textarea></label>

		<p>signing up implies that you agree to abide by our <a href="/coc/" target="blank">code of conduct</a>, no drama. be respectful. have fun. we're all trying, and we're all in this together :)</p>

		<label><input class="form-control" type="checkbox" name="c_age" value="check" /> i am at least 16 years old.</label>
		<label><input class="form-control" type="checkbox" name="iagree" value="check" /> i agree! i have read and understood the <a href="/privacy-policy/" target="blank">terms and conditions</a>.</label>

		<input class="form-control" type="submit" value="submit">
	</form>

</main>

<?php include 'neoenvs_footer.php'; ?>
