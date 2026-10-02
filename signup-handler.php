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

function get_rate_limit_remaining($ip) {
	$limit_dir = "/var/signup_limits/";
	$ip_file = $limit_dir . md5($ip);
	$limit_time = 3600;

	if (file_exists($ip_file)) {
		$last_submission = file_get_contents($ip_file);
		$elapsed = time() - $last_submission;

		if ($elapsed < $limit_time) {
			return $limit_time - $elapsed;
		}
	}

	return 0;
}

function starts_with($string, $prefix){
	return mb_substr($string, 0, mb_strlen($prefix)) === $prefix;
}

function is_ssh_pubkey($string): bool {
	// An authorized_keys entry must be exactly one line. Apart from being
	// malformed, allowing newlines here would also allow additional keys to be
	// smuggled into authorized_keys during account creation.
	if (str_contains($string, "\n") || str_contains($string, "\r"))
		return false;

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

	$parts = preg_split('/\s+/', trim($string), 3);
	if (count($parts) < 2 || !in_array($parts[0], $valid_pubkeys, true))
		return false;

	// Strict base64 validation catches most malformed public-key submissions.
	$key_blob = base64_decode($parts[1], true);
	return $key_blob !== false && strlen($key_blob) > 0;
}

function signup_request_path($username) {
	return "/var/signups/$username.json";
}

function write_verified_signup($path, $data): bool {
	$json = json_encode(
		$data,
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);
	if ($json === false)
		return false;

	// 'x' is an atomic create: a second verification for the same username
	// cannot overwrite an already queued request.
	$fh = @fopen($path, 'x');
	if ($fh === false)
		return false;

	$ok = fwrite($fh, $json . PHP_EOL) !== false;
	if ($ok)
		$ok = fflush($fh);
	fclose($fh);

	if (!$ok) {
		@unlink($path);
		return false;
	}

	@chmod($path, 0600);
	return true;
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
	$submitted = preg_split('/\s+/', trim($sshkey), 3);
	if (count($submitted) < 2)
		return false;

	$fsshkey = file("/var/banned_sshkeys.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($fsshkey as $line) {
		$parts = preg_split('/\s+/', trim($line), 3);
		if (count($parts) >= 2 && hash_equals($parts[1], $submitted[1]))
			return true;
	}

	return false;
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

			if (posix_getpwnam($username) || forbidden_name($username)) {
				echo "<p class='block alert'>Sorry, the username $username was taken in the meantime.</p>";
				unlink($file);
				return;
			}

			$email = $data['email'];
			$sshkey = $data['sshkey'];
			$user_ip = getUserIpAddr();
			$interest = $data['interest'];
			$verified_at = time();

			$request = [
				'version' => 1,
				'status' => 'verified',
				'username' => $username,
				'email' => $email,
				'sshkey' => $sshkey,
				'interest' => $interest,
				'submitted_ip' => $data['ip'] ?? null,
				'verified_ip' => $user_ip,
				'submitted_at' => $data['timestamp'] ?? null,
				'verified_at' => $verified_at,
			];

			$request_file = signup_request_path($username);
			if (!write_verified_signup($request_file, $request)) {
				echo "<p class='block alert'>We could not queue your verified signup. Please contact the admin.</p>";
				return;
			}

			if (file_put_contents("/var/signups_current", $username.PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
				@unlink($request_file);
				echo "<p class='block alert'>We could not queue your verified signup. Please contact the admin.</p>";
				return;
			}

			$mailTo = 'hostmaster@envs.net';
			$mailSubject = "Verified Signup: $username - envs.net";

			$msgbody = "--- NEW VERIFIED SIGNUP ---\n\n";
			$msgbody .= "Username: $username\n";
			$msgbody .= "Email:    $email\n\n";
			$msgbody .= "Reason/Interest:\n$interest\n\n";
			$msgbody .= "Signup IP:\n" . ($data['ip'] ?? 'unknown') . "\n\n";
			$msgbody .= "Verification IP:\n$user_ip\n\n";
			$msgbody .= "SSH key:\n$sshkey\n\n";
			$msgbody .= "Stored request:\n$request_file\n\n";
			$msgbody .= "Review on core:\n/usr/local/bin/envs_signups_review $username\n";

			$headers = "From: webserver@envs.net\r\n";
			$headers .= "Reply-To: $email\r\n";
			$headers .= "Content-Type: text/plain; charset=utf-8";

			if (!mail($mailTo, $mailSubject, $msgbody, $headers))
				error_log("envs signup: admin mail failed for verified request $username");

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
		$remaining_seconds = get_rate_limit_remaining($user_ip);

		if ($remaining_seconds > 0) {
			$display_minutes = floor($remaining_seconds / 60);
			$display_seconds = $remaining_seconds % 60;

			echo '<div class="block alert">
					<p>You have already requested a link recently.</p>
					<p>Please wait another <strong>' . 
					($display_minutes > 0 ? $display_minutes . ' minute(s) and ' : '') . 
					$display_seconds . ' second(s)</strong> before trying again.</p>
				  </div>';
		} else {
			$token = bin2hex(random_bytes(16));

			$signup_data = [
				'username' => $name,
				'email' => $email,
				'sshkey' => $sshkey,
				'interest' => $interest,
				'ip' => $user_ip,
				'timestamp' => time()
			];

			file_put_contents("/var/signups_pending/$token.json", json_encode($signup_data));

			$verification_url = "https://envs.net/signup.php?token=$token";
			$verify_subject = "Verify your envs.net signup";
			$verify_body = "Hi $name,\n\nPlease click the link below to verify your email and complete your signup:\n$verification_url\n\nIf you didn't request this, just ignore this mail.";

			$verify_headers = "From: hostmaster@envs.net\r\nContent-Type: text/plain; charset=utf-8";

			if (mail($email, $verify_subject, $verify_body, $verify_headers)) {
				$limit_dir = "/var/signup_limits/";
				file_put_contents($limit_dir . md5($user_ip), time());

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
