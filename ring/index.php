<?php
$filepath = __FILE__;
$action = $_GET["action"] ?? "index";
$users = array_map(function ($f) { return basename(dirname($f)); }, glob("/home/*/.ring"));

if ($action !== "index") {
    // handle webring redirects
    $me = $_GET["me"] ?? "";
    if (!in_array($me, $users) && $action != "random") {
        header("Location: https://envs.net/ring/?error");
        die();
    }
    $i = array_search($me, $users);

    switch ($action) {
    case "random":
        $notme = array_diff($users, [$me]);
        $user = $notme[array_rand($notme)];
        break;
    case "next":
        $user = $users[$i + 1] ?? reset($users);
        break;
    case "prev":
        $user = $users[$i - 1] ?? end($users);
        break;
    default:
        die("invalid action");
    }

    // use custom link from second line of ~/.ring
    if (filesize("/home/$user/.ring") > 0) {
        $ringfile = file("/home/$user/.ring", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($ringfile) > 1 && $ringfile[1] !== "")
            $dest = trim($ringfile[1]);
    }
    $location = $dest ?? "/~$user/";
    header("Location: $location");
    die();
}
else {
    // keep this as a string so we can escape it easily with htmlspecialchars()
    $snippet = '<!-- envs.net ring fragment-->
<div id="newring">
    <div style="text-align: center;">
        [<a href="https://envs.net/ring/?action=prev&me=USERNAME">previous</a>]
        [<a href="https://envs.net/ring/?action=random&me=USERNAME">random</a>]
        [<a href="https://envs.net/ring/?action=next&me=USERNAME">next</a>]
        <br>
        <a href="https://envs.net/ring/">how to join this webring</a>
    </div>
</div>';

    $title = "envs.net | webring";
    $desc = "envs.net | webring - how to join";
    include __DIR__ . '/../header.php';
?>

<body id="body" class="dark-mode">
    <div>

      <div id="main">

<div class="block">
<h1>how to join the webring</h1>
<p></p>
</div>

<?php if (isset($_GET["error"])): ?>
    <pre class="alert"><strong>ERROR</strong> please be sure that me=USERNAME is set to your user and have created a <code>~/.ring</code> file.</pre>
<?php endif; ?>

<pre>
this webring can be joined by any user on envs.net.
first, add the following links to your page, replacing USERNAME by your username (no ~):

<code><?=htmlspecialchars($snippet)?></code>

you may edit the code however you want. it should work as long as the URLs are kept in place and have your user in them.

then create a .ring file in your ~ to be added in the webring:
<code>touch ~/.ring</code>

the first line of ~/.ring will be displayed here as a tagline.
the second line of the file is used as a custom link if your webring tags are not on your homepage.

<h2 id="explore">explore webring</h2>
<a href="?action=random">jump to random page</a>

<?=count($users)?> members in the webring:
</pre>

<br />

<ul>
<?php foreach ($users as $user):
    if (filesize("/home/$user/.ring") > 0) {
        $ringfile = file("/home/$user/.ring", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $tagline = htmlspecialchars($ringfile[0]);
        if (count($ringfile) > 1 && $ringfile[1] !== "")
            $link = htmlspecialchars($ringfile[1]);
    }
    else {
        unset($tagline);
        unset($link);
    } ?>

    <li><a href="<?=$link ?? "/~$user/"?>">~<?=$user?></a><?=(isset($tagline) ? " &mdash; $tagline" : "")?></li>
<?php endforeach; ?>
</ul>

<br />

<pre>
it will probably work if you copy it to your own <code>~/public_html</code> and adjust the links accordingly.
</pre>

      </div>
<?php
    include __DIR__ . '/../footer.php';
}
