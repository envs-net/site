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
    elseif (substr($sshkey, 0, 4) !== "ssh-" && substr($sshkey, 0, 3) !== "sk-" && substr($sshkey, 0, 5) !== "ecdsa")
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
Sent your message <big><em>successfully</em></big>!
Please allow up to 24 hours for a response with login instructions!</pre>';

        } else {
            echo '<pre class="alert">
something went wrong... :(
please send an email to <a href="mailto:hostmaster@envs.net">hostmaster&#64;envs.net</a> with details of what happened.</pre>';
        }

    } else {
        ?>
<div class="alert">
<h3><i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i> notice:</h3>
<p></p>
<ul>
    <?=$message?>
</ul>
</div>
        <?php
    }
}
?>
