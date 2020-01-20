<?php
	$title = "envs.net | users";
	$desc = "envs.net | full user list";

	// json files
	$user_info = json_decode(file_get_contents('/var/www/envs.net/users_info.json'));

	// users
	$online_users = str_replace(PHP_EOL, '', shell_exec("online-users"));
	$total_users = $user_info->data->info->user_count;

include 'header.php';
?>

	<body id="body" class="dark-mode">
		<div>

			<div class="button_back">
				<pre class="clean"><strong><a href="/">&lt; back</a></strong></pre>
			</div>

			<div id="main">
<div class="block">
<h1><em>full user list</em></h1>
<pre>online: <?=$online_users?> &#124; total: <?=$total_users?></pre>
<p></p>
<table>
	<tr><th class="tw20"></th> <th></th></tr>
	<tr onclick="window.location='/users_info.json';">
		<td><small><i class="fa fa-info-circle fa-fw" aria-hidden="true"></i></small></td> <td><small><a href="/users_info.json">users_info.json</a></small></td>
	</tr>
	<tr onclick="window.location='/user_updates/';">
		<td><small><i class="fa fa-clock-o fa-fw" aria-hidden="true"></i></small></td> <td><small><a href="/user_updates/">recently updates</a></small></td>
	</tr>
</table>
<p></p>
</div>

<pre>here's a full list of users (including those who haven't updated their page from the default).</pre>
<p></p>
<ul>
<?php
	foreach ($user_info->data->users as $user => $value) {
		echo "\t<li><a rel=\"$user\" target=\"_blank\" href=\"/~$user\">&#126;$user</a></li>\n";
	}
?>
</ul>
			</div>

<?php include 'footer.php'; ?>
