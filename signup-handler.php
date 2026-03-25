<?php

function getUserIpAddr() {
	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		//ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//ip pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function is_rate_limited($ip) {
	$limit_dir = "/var/signup_limits/";

	$ip_file = $limit_dir . md5($ip);
	$limit_time = 86400;

	if (file_exists($ip_file)) {
		$last_submission = file_get_contents($ip_file);
		if (time() - $last_submission < $limit_time) {
			return true;
		}
	}

	file_put_contents($ip_file, time());
	return false;
}

function starts_with($string, $prefix){
	return mb_substr($string, 0, mb_strlen($prefix)) === $prefix;
}

function is_ssh_pubkey($string): bool {
	$valid_pubkeys = [
		'sk-ecdsa-sha2-nistp256@openssh.com',
		'ecdsa-sha2-nistp256',
		'ecdsa-sha2-nistp384',
		'ecdsa-sha2-nistp521',
		'sk-ssh-ed25519@openssh.com',
		'ssh-ed25519',
		'ssh-dss',
		'ssh-rsa',
	];

	foreach ($valid_pubkeys as $pub)
		if (starts_with($string, $pub)) return true;

	return false;
}

function add_ban_info($name, $email) {
	$user_ip = getUserIpAddr();
	$user_info = "$name - $email - $user_ip";
	file_put_contents("/var/signups_banned", $user_info.PHP_EOL, FILE_APPEND);
}

function forbidden_name($name) {
	return in_array(
		$name,
		array_merge(
			file("/var/signups_forbidden", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
			file("/var/signups_current", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
			file("/var/banned_names.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
		)
	);
}

function forbidden_email($email) {
	$femail = file("/var/banned_emails.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	return in_array($email, $femail);
}

function forbidden_sshkey($sshkey) {
	$fsshkey = file("/var/banned_sshkeys.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($fsshkey as $line_num => $line) {
		$fsk_line = explode(' ',trim($line));
		$fsk[] = $fsk_line[1];
	}

	$sk = explode(' ',trim($sshkey));

	return in_array($sk[1], $fsk);
}


if (isset($_GET['token'])) {
	$token = preg_replace('/[^a-f0-9]/', '', $_GET['token']);
	$file = "/var/signups_pending/$token.json";

	if (file_exists($file)) {
		$data = json_decode(file_get_contents($file), true);

		if (time() - $data['timestamp'] > 86400) {
			echo "<p class='block alert'>Token expired. Please sign up again.</p>";
			unlink($file);
		} else {
			$username = $data['username'];
			$email = $data['email'];
			$sshkey = $data['sshkey'];
			$user_ip = getUserIpAddr();
			$interest = $data['interest'];

			$makeuser = "/usr/local/bin/envs_user_manage add $username $email \"$sshkey\"";

			file_put_contents("/var/signups_current", $username.PHP_EOL, FILE_APPEND);
			file_put_contents("/var/signups", $makeuser.PHP_EOL, FILE_APPEND);

			$mailTo = 'hostmaster@envs.net';
			$mailSubject = "Verified Signup: $username - envs.net";

			$msgbody = "--- NEW VERIFIED SIGNUP ---\n\n";
			$msgbody .= "Username: $username\n";
			$msgbody .= "Email:    $email\n\n";
			$msgbody .= "Reason/Interest:\n$interest\n\n";
			$msgbody .= "IP:\n$user_ip\n\n";
			$msgbody .= "Command:\n$makeuser\n";

			$headers = "From: webserver@envs.net\r\n";
			$headers .= "Reply-To: $email\r\n";
			$headers .= "Content-Type: text/plain; charset=utf-8";

			mail($mailTo, $mailSubject, $msgbody, $headers);

			echo "<div class='block success'>
					<h3>Email verified!</h3>
					<p>Thanks, <b>$username</b>. Your request has been forwarded to the admin.</p>
				  </div>";

			unlink($file);
		}
	} else {
		echo "<p class='block alert'>Invalid or already used token.</p>";
	}
}


$message = '';
if (isset($_REQUEST["username"]) && isset($_REQUEST["email"])) {

	$email = $_REQUEST["email"];
	$mailTo = 'hostmaster@envs.net';
	$mailFrom = "$email";
	$mailSubject = 'Signup User Space - envs.net';

	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/plain; charset=utf-8';
	$headers[] = "From: $mailFrom";
	//$headers[] = "Cc: $mailFrom";


	$name = trim($_REQUEST["username"]);
	if ($name == "")
		$message .= "<li>fill in your desired username</li>\n";
	else {
		if (strlen($name) < 2)
			$message .= "<li>username is too short (2 character min)</li>\n";

		if (strlen($name) > 32)
			$message .= "<li>username too long (32 character max)</li>\n";

		if (strlen($name) > 1 && !preg_match('/^[a-z][a-z0-9]{1,31}$/', $name))
			$message .= "<li>username contains invalid characters (lowercase only, must start with a letter).</li>\n";

		if (posix_getpwnam($name) || forbidden_name($name))
			$message .= "<li>sorry, the username $name is unavailable</li>\n";
	}


	$email = trim($_REQUEST["email"]);
	$emailconfirm = trim($_REQUEST["emailconfirm"]);
	if ($email == "")
		$message .= "<li>fill in your email address</li>\n";
	else {
		if ($email != $emailconfirm)
			$message .= "<li>email does not match</li>\n";
		elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
			$message .= "<li>invalid email format</li>\n";

		elseif ($name != "" && forbidden_email($email)) {
			$message .= "<li>your email is banned!</li>\n";
			add_ban_info($name, $email);
		}
	}


	$interest = $_REQUEST["interest"];
	if ($interest == "")
		$message .= "<li>explain why you're interested so we can make sure you're a real human being</li>\n";
	else {
		if (strlen($interest) < 50)
			$message .= "<li>interests explanation is too short (50 character min)</li>\n";
	}


	$sshkey = trim($_REQUEST["sshkey"]);
	if ($sshkey == "")
		$message .= "<li>ssh pubkey required: please submit the public key.</li>\n";
	elseif (!is_ssh_pubkey($sshkey))
		$message .= "<li>ssh pubkey looks not correct.</li>\n";
	else {
		if ($name != "" && $email != "") {
			if (forbidden_sshkey($sshkey)) {
				$message .= "<li>your sshkey is banned!</li>\n";
				add_ban_info($name, $email);
			}
		}
	}


	if ($_REQUEST["c_age"] == "")
		$message .= "<li>you must be at least 16 years old to use this service.</li>\n";

	if ($_REQUEST["iagree"] == "")
		$message .= "<li>you need to agree to our terms.</li>\n";


	// no validation errors
	if ($message == "") {
		$user_ip = getUserIpAddr();

		if (is_rate_limited($user_ip)) {
			echo '<div class="block alert">
					<p>Please wait 1 day before trying again.</p>
				  </div>';
		} else {
			$token = bin2hex(random_bytes(16));

			$signup_data = [
				'username' => $name,
				'email' => $email,
				'sshkey' => $sshkey,
				'interest' => $interest,
				'timestamp' => time()
			];

			file_put_contents("/var/signups_pending/$token.json", json_encode($signup_data));

			$verification_url = "https://envs.net/signup.php?token=$token";
			$verify_subject = "Verify your envs.net signup";
			$verify_body = "Hi $name,\n\nPlease click the link below to verify your email and complete your signup:\n$verification_url\n\nIf you didn't request this, just ignore this mail.";

			$verify_headers = "From: hostmaster@envs.net\r\nContent-Type: text/plain; charset=utf-8";

			if (mail($email, $verify_subject, $verify_body, $verify_headers)) {
				echo '<div class="block success">
				<p>A verification link has been sent to your email. Please check your inbox (and spam folder) to complete the signup!</p>
				</div>';
			} else {
				echo '<p class="block alert">Failed to send verification email.</p>';
			}
		}
	} else {
		?>
<div class="block alert">
	<h3 class="fa-pfx fa-exclamation-triangle">notice:</h3>
	<ul>
		<?=$message?>
	</ul>
</div>
		<?php
	}
}
?>
