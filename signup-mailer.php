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

    // Check the name.
    $name = trim($_REQUEST["username"]);
    if ($name == "")
        $message .= "<li>fill in your desired username</li>\n";

    if (strlen($name) > 32)
        $message .= "<li>username too long (32 character max)</li>\n";

    if ($name != "" && strlen($name) < 2)
        $message .= "<li>username is too short (2 character min)</li>\n";

    if (strlen($name) > 1 && !preg_match('/^[a-z][a-z0-9]{1,31}$/', $name))
        $message .= "<li>username contains invalid characters (lowercase only, must start with a letter).</li>\n";

    if (posix_getpwnam($name) || forbidden_name($name))
        $message .= "<li>sorry, the username $name is unavailable</li>\n";

    // Check the e-mail address.
    $email = trim($_REQUEST["email"]);
    if ($email == "")
        $message .= "<li>fill in your email address</li>\n";
    else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $message .= "<li>Invalid email format</li>\n";

        elseif ($name != "" && forbidden_email($email)) {
            $user_ip = getUserIpAddr();
            $user_info = "$name - $email - $user_ip";
            $message .= "<li>your email is banned!</li>\n";
            file_put_contents("/var/signups_banned", $user_info.PHP_EOL, FILE_APPEND);
        }
    }

    if ($_REQUEST["interest"] == "")
        $message .= "<li>explain why youre interested so we can make sure youre a real human being</li>\n";

    if ($_REQUEST["sshkey"] == "" || substr($_REQUEST["sshkey"], 0, 4) !== "ssh-")
        $message .= "<li>ssh key required: please submit the public key.</li>\n";

    // no validation errors
    if ($message == "") {

        $sshkey = trim($_REQUEST["sshkey"]);
        $makeuser = "/usr/local/bin/envs_user_manage add {$_REQUEST["username"]} {$_REQUEST["email"]} \"{$sshkey}\"";

        $msgbody = "
username: {$_REQUEST["username"]}
email: {$_REQUEST["email"]}
reason:
{$_REQUEST["interest"]}

$makeuser
";
        $mailSent = @mail($mailTo, $mailSubject, $msgbody, implode("\r\n", $headers));

        if($mailSent == TRUE) {
            // temp. add to forbidden to prevent double signups (cleanup after user creation)
            file_put_contents("/var/signups_current", $name.PHP_EOL, FILE_APPEND);
            // save signup
            file_put_contents("/var/signups", $makeuser.PHP_EOL, FILE_APPEND);

            echo '<pre class="alert">
Send your message <big><em>successfully</em></big>!
Please allow up to 24 hours for a response with login instructions!</pre>';

        } else {
            echo '<pre class="alert">
something went wrong... :(
please send an email to <a href="mailto:hostmaster@envs.net">hostmaster&#64;envs.net</a> with details of what happened.</pre>';
        }

    } else {
        ?>
<pre class="alert">
<h3><i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i> notice:</h3>
<ul>
<?=$message?>
</ul>
</pre>
        <?php
    }
}
?>
