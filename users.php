<?php
  $title = "envs.net | users";
  $desc = "envs.net | full user list";

  $json_file = '/var/www/envs.net/users_info.json';

  // users
  $online_users = str_replace(PHP_EOL, '', shell_exec("online-users"));
  $users=array_filter(explode(PHP_EOL, shell_exec("jq -Mr '.data.users|keys[]' $json_file")));
  $total_users = count($users);

include 'header.php';
?>

  <body id="body" class="dark-mode">
    <div>

      <div class="button_back">
        <pre class="clean"><strong><a href="/">&lt; back</a></strong></pre>
      </div>

      <div id="main">
<div class="block">
<pre>
<h1><em>full user list</em></h1>

<small>online: <?=$online_users?> &#124; total: <?=$total_users?></small>
</pre>
<table>
  <tr onclick="window.location='/users_info.json';">
    <td><small><i class="fa fa-info-circle fa-fw" aria-hidden="true"></i></small></td> <td><small><a href="/users_info.json">users_info.json</a></small></td>
  </tr>
  <tr onclick="window.location='/user_updates/';">
    <td width="20px"><small><i class="fa fa-clock-o fa-fw" aria-hidden="true"></i></small></td> <td><small><a href="/user_updates/">recently updates</a></small></td>
  </tr>
</table>
</div>

<pre>
here's a full list of users (including those who haven't updated their page from the default).
<ul>
<?php
  foreach ($users as $user) {
    echo "<li><a rel=\"~$user\" target=\"_blank\" href=\"/~$user/\">&#126;$user</a></li>\n";
  }
?>
</ul>
</pre>
      </div>

<?php include 'footer.php'; ?>
