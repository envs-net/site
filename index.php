<?php
//  $additional_head = "
//    <meta http-equiv='refresh' content='60' />
//  ";

  $user_info = json_decode(file_get_contents('/var/www/envs.net/users_info.json'));
  $sys_info = json_decode(file_get_contents('/var/www/envs.net/sysinfo.json'));

  $date = new DateTime(null, new DateTimeZone('Etc/UTC'));
  $datetime = $date->format('l, d. F Y - h:i:s A (e)');

  $online_users = str_replace(PHP_EOL, '', shell_exec("online-users"));
  $total_users = $user_info->data->info->user_count;

  $local_os = shell_exec("lsb_release -ds");

include 'neoenvs_header.php';
?>

<body id="body" class="dark-mode">
<main>

<pre class="banner">
<?php include 'banner_html.txt'; ?>
<small id="header_sub"><em>environments - since 9/2019</em></small>
</pre>

<!-- maintenance info -->
<!-- <div class="alert">
	<p><strong><i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>&nbsp; srv01.envs.net is currently not reachable.</strong></p>
	<p>affected services are: cinny element-web hydrogen-web matrix mobilizon pleroma</p>
	<p>i am working at full speed on a problem solution.</p>
</div> -->

	<table>
	  <tr> <th class="tw18"></th> <th class="tw16"></th> <th></th> </tr>
	<?php
	  $exclude = ['bbj','cinny','drone','element-web','gophernicus','hydrogen-web','ipinfo','jetforce','ntfy','tt-rss','thelounge','znc'];
	  $clean = array('http://', 'https://', '/');

	  foreach ($sys_info->data->services as $service => $value) {
	    $url = $sys_info->data->services->$service->url;
	    $urlname = str_replace($clean,'',$url);
	    $desc = $sys_info->data->services->$service->desc;

	    if (! in_array($service, $exclude)) {
	      echo "  <tr>\n    <td><a rel=\"$service\" href=\"$url\" target=\"_blank\">$urlname</a></td>\n"
	         ."    <td>- $service</td> <td><em>$desc</em></td>\n  </tr>\n";
	    }
	  }
	?>
	</table>

	<p>envs.net is a minimalist, non-commercial
	shared linux system and will always be free to use.</p>

	<p>we are linux lovers, sysadmins, programmers and users who like to build
	webpages, write blogs, chat online, play cool console games and so much
	more.</p>

	<p>do you wish to join a small shell user space? use the <a href="/signup/">signup</a> form<br />
	to request an shell account.</p>

	<p>for more informations take a look to our <a href="https://help.envs.net/">help page</a>.</p>

	<p>follow us in the <a href="/chat">chat</a> if you like.</p>

	<div class="block">
		<p><strong><i class="fa fa-gear fa-fw" aria-hidden="true"></i> SYSTEM INFO</strong></p>
		<table>
			<tr><th class="tw10"></th> <th></th></tr>
			<tr><td>time:</td> <td><?=$datetime?></td></tr>
			<tr><td>os:</td> <td><?=$local_os?></td></tr>
		</table>
		<p>
			<em>more info about the system and the installed packages can
			be found on the <a href="/sysinfo/">sysinfo page</a>.</em>
		</p>
	</div>
</main>
<!-- END MAIN -->

<!-- SIDEBAR -->
<div id="sidebar">

<nav class="block">
<ul class="icon-list">
<?php
$menu = [
  'chat' => ['fa' => 'comments-o', 'url' => '/chat/'],
  'webmail' => ['fa' => 'envelope-o', 'url' => 'https://mail.envs.net/'],
  'mailing list' => ['fa' => 'bullhorn', 'url' => 'https://lists.envs.net/'],
  'forum' => ['fa' => 'forumbee', 'url' => 'https://bbj.envs.net/'],
  'webring' => ['fa' => 'user-circle', 'url' => '/ring/'],
  'gopher' => ['fa' => 'hand-o-right', 'url' => 'https://gopher.envs.net/'],
  'gemini' => ['fa' => 'hand-o-right', 'url' => 'https://gemini.envs.net/'],
  'pleroma' => ['fa' => 'pleroma', 'url' => 'https://pleroma.envs.net/~envs'],
  'code of conduct' => ['fa' => 'sticky-note-o', 'url' => '/coc/'],
  'terms of service' => ['fa' => 'handshake-o', 'url' => '/tos/'],
  'privacy policy' => ['fa' => 'eye', 'url' => '/privacy-policy/'],
  'help' => ['fa' => 'book', 'url' => 'https://help.envs.net/'],
  'faq' => ['fa' => 'question-circle-o', 'url' => 'https://help.envs.net/faq/'],
  'donate' => ['fa' => 'rocket', 'url' => '/donate/']
];

foreach ($menu as $name => $m_entry) {
	if ($name == 'code of conduct' || $name == 'donate')
		echo "\t</ul><ul class=\"icon-list\">\n";
?>
	<li>
		<a rel="<?=$name?>" target="_top" href="<?=$m_entry['url']?>"><i class="fa-<?=$m_entry['fa']?>"></i><?=$name?></a>
	</li>
<?php } ?>
</ul>
</nav>

<nav class="block">
	<p><b><i class="fa fa-users fa-fw" aria-hidden="true"></i> USERS</b></p>
	<p><small>online: <?=$online_users?> &#124; total: <?=$total_users?></small></p>
	<ul class="icon-list">
		<li><a href="/users_info.json"><i class="fa-info-circle"></i>users_info.json</a></li>
		<li><a href="/user_updates/"><i class="fa-clock-o"></i>recent updates</a></li>
	</ul>

	<details><summary class="menu">user list</summary><small> &gt; <a href="/users/">list all users</a></small>
	  <ul class="userlist">
	  <?php
	    foreach ($user_info->data->users as $user => $value) {
	      if ($user_info->data->users->$user->website != '') {
	        echo "\t<li><a rel=\"$user\" target=\"_blank\" href=\"/~$user\">&#126;$user</a></li>\n";
	      }
	    }
	  ?>
	  </ul>
	</details>
</nav>

</div>
<!-- END SIDEBAR -->

<?php include 'neoenvs_footer.php'; ?>
