<?php
	$title = "envs.net | users";
	$desc = "envs.net | full user list";

	// json files
	$user_info = json_decode(file_get_contents('/var/www/envs.net/users_info.json'));

	// users
	$online_users = str_replace(PHP_EOL, '', shell_exec("online-users"));
	$total_users = $user_info->data->info->user_count;

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
<main>
	<div class="block">
		<h1>full user list</h1>
		<p>online: <?=$online_users?> &#124; total: <?=$total_users?></p>
		<ul class="icon-list">
			<li><a href="/users_info.json"><i class="fa-info-circle"></i>users_info.json</a></li>
			<li><a href="/user_updates/"><i class="fa-clock-o"></i>recent updates</a></li>
		</ul>
	</div>

	<p>here's a full list of users (including those who haven't updated their page from the default (displayed as small text)).</p>

	<ul class="user-list">
<?php
	foreach ($user_info->data->users as $user => $value) {
		if ($user_info->data->users->$user->website != '') {
			echo "\t<li><strong><a rel=\"$user\" target=\"_blank\" href=\"/~$user\">&#126;$user</a></strong></li>\n";
		}
		else {
			echo "\t<li><small><a rel=\"$user\" target=\"_blank\" href=\"/~$user\">&#126;$user</a></small></li>\n";
		}
	}
?>
	</ul>
</main>

<?php include 'neoenvs_footer.php'; ?>
