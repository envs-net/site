<?php
  $additional_head = "
    <meta http-equiv='refresh' content='60' />
  ";

  // json files
  $user_info = json_decode(file_get_contents('/var/www/envs.net/users_info.json'));
  $sys_info = json_decode(file_get_contents('/var/www/envs.net/sysinfo.json'));

  // date
  $date = new DateTime(null, new DateTimeZone('Etc/UTC'));
  $datetime = $date->format('l, d. F Y - h:i:s A (e)');

  // users
  $online_users = str_replace(PHP_EOL, '', shell_exec("online-users"));
  $total_users = $user_info->data->info->user_count;

  // server system info
  $load = '';
  foreach (sys_getloadavg() as $value) { $load .= number_format($value, 2) . " "; } ;
  $load = trim($load);
  $ds = number_format(disk_total_space("/") / 1073741824, 2);
  $ds_free = number_format(disk_free_space("/") / 1073741824, 2);
  $ds_used = "$ds" - "$ds_free";
  $conntracks = shell_exec("tail -1 /var/log/envs_conntrack.log | awk '{printf $2}'");

include 'header.php';
?>

  <body id="body" class="dark-mode">
<!-- BODYDIV (closed in footer.php) -->
    <div class="clear" style="min-width: 1050px;">

  <!-- MAIN -->
      <div id="main" style="padding-bottom: 2em;">

<pre class="banner">
<?php include 'banner_html.txt'; ?>
        <small><em>environments - since 9/2019</em></small>

</pre>

<!-- maintenance info -->
<!--<pre class="alert">
<i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>&nbsp; maintenance!

</pre>-->

<table>
  <tr> <th class="tw130"></th> <th class="tw110"></th> <th class="tw480"></th> </tr>
<?php
  $exclude = ['bbj','gophernicus','jetforce','riot-web','thelounge','znc'];
  $clean = array('http://', 'https://', '/');

  foreach ($sys_info->data->services as $service => $value) {
    $url = $sys_info->data->services->$service->url;
    $urlname = str_replace($clean,'',$url);
    $desc = $sys_info->data->services->$service->desc;

    if ($service == 'getwtxt') { $desc .= ' - see <a href="https://help.envs.net/blog/#with-twtxt">twtxt</a> help'; }

    if (! in_array($service, $exclude)) {
      echo "  <tr>\n    <td><a rel=\"$service\" href=\"$url\" target=\"_blank\">$urlname</a></td>\n"
        ."    <td>- $service</td> <td><em>$desc</em></td>\n  </tr>\n";
    }
  }
?>
</table>

  <!-- ABOUT -->
<pre>

envs.net is a minimalist, non-commercial
shared linux system and will always be free to use.

we are linux lovers, sysadmins, programmer and users who like build
webpages, write blogs, chat online, play cool console games and so much
more. you wish to join with an small user space? send a email
to <a href="mailto:hostmaster@envs.net?subject=signup%20user%20space%20-%20envs.net&body=desired%20username:%20%0A%0A%0Awhat%20interests%20you%20about%20envs.net?%0A%0A%0Assh%20public%20key:%0A%20--paste%20here--%0A" target="_blank">hostmaster&#64;envs.net</a> or use the <a href="/signup/">signup</a> form.

follow us in the <a href="/chat">chat</a> and let's start talking.
</pre>
  <!-- END ABOUT -->

  <!-- SERVER INFO -->
<div class="block">
<pre><strong><i class="fa fa-gear fa-fw" aria-hidden="true"></i> SYSTEM INFO</strong></pre>
<table>
  <tr><th class="tw110"></th> <th></th></tr>
  <tr><td >time:</td> <td><?=$datetime?></td></tr>
  <tr><td>&nbsp;</td> <td></td></tr>
  <tr><td>os:</td> <td>Debian GNU/Linux 10 (buster)</td></tr>
  <tr><td>load:</td> <td><?=$load?></td></tr>
  <tr><td>disk space:</td> <td>used: <?=$ds_used?>GB &#124; free: <?=$ds_free?>GB</td></tr>
  <tr><td>conntracks:</td> <td><?=$conntracks?></td></tr>
</table>
<p></p>
<pre>
<em>more infos about the system and the installed packages can
you find on the <a href="/sysinfo/">sysinfo page</a>.</em>
</pre>
<p></p>
</div>

<pre><small><em>inspired by <a href="https://tilde.team/" target="_blank">~team</a> and a member of <a href="https://tildeverse.org/" target="_blank">tildeverse</a>.</em></small></pre>
  <!-- END SERVER INFO -->

      </div>
<!-- END MAIN -->

<!-- SIDEBAR -->
      <div id="sidebar">

  <!-- MENU -->
<div class="block">
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='/chat/';">
    <td><i class="fa fa-comments-o fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="chat" target="_top" href="/chat/">chat</a></strong></td>
  </tr>
  <tr onclick="window.location='https://mail.envs.net/';">
    <td><i class="fa fa-envelope-o fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="mail" target="_top" href="https://mail.envs.net/">webmail</a></strong></td>
  </tr>
  <tr onclick="window.location='https://lists.envs.net/';">
    <td><i class="fa fa-bullhorn fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="lists" target="_top" href="https://lists.envs.net/">mailing lists</a></strong></td>
  </tr>
  <tr onclick="window.location='https://bbj.envs.net/';">
    <td><i class="fa fa-forumbee fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="forum" target="_top" href="https://bbj.envs.net/">forum</a></strong></td>
  </tr>
  <tr onclick="window.location='https://gopher.envs.net/';">
    <td><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="gopher" target="_top" href="https://gopher.envs.net/">gopher</a></strong></td>
  </tr>
  <tr onclick="window.location='https://tilde.zone/@envs';">
    <td><i class="fa fa-mastodon fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="me" target="_top" href="https://tilde.zone/@envs">mastodon</a></strong></td>
  </tr>
</table>
<br />
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='/coc/';">
    <td><i class="fa fa-sticky-note-o fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="coc" target="_top" href="/coc/">code of conduct</a></strong></td>
  </tr>
  <tr onclick="window.location='/tos/';">
    <td><i class="fa fa-handshake-o fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="tos" target="_top" href="/tos/">terms of service</a></strong></td>
  </tr>
  <tr onclick="window.location='/privacy-policy/';">
    <td><i class="fa fa-eye fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="pp" target="_top" href="/privacy-policy/">privacy policy</a></strong></td>
  </tr>
  <tr onclick="window.location='https://help.envs.net/';">
    <td><i class="fa fa-book fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="help" target="_top" href="https://help.envs.net/">help</a></strong></td>
  </tr>
  <tr onclick="window.location='https://help.envs.net/faq/';">
    <td><i class="fa fa-question-circle-o fa-fw" aria-hidden="true"></i></td>
    <td><strong><a rel="faq" target="_top" href="https://help.envs.net/faq/">faq</a></strong></td>
  </tr>
</table>
</div>
  <!-- END MENU -->

<p></p>

  <!-- DONATION -->
<div class="block">
<pre>
<em>donate via liberapay</em>
<a href="https://en.liberapay.com/creme/donate" target="_blank" style="border-bottom-color: transparent;"><img src="https://img.shields.io/liberapay/receives/creme.svg?logo=liberapay" alt="Donate using Liberapay"></a>
</pre>
<p></p>
</div>
  <!-- END DONATION -->

  <!-- USERS -->
<div class="block">
<pre>
<strong><i class="fa fa-users fa-fw" aria-hidden="true"></i> USERS</strong>
<small>online: <?=$online_users?> &#124; total: <?=$total_users?></small>
</pre>
<p></p>
<table>
  <tr><th class="tw20"></th> <th></th></tr>
  <tr onclick="window.location='/users_info.json';">
    <td><small><i class="fa fa-info-circle fa-fw" aria-hidden="true"></i></small></td>
    <td><small><a href="/users_info.json">users_info.json</a></small></td>
  </tr>
  <tr onclick="window.location='/user_updates/';">
    <td><small><i class="fa fa-clock-o fa-fw" aria-hidden="true"></i></small></td>
    <td><small><a href="/user_updates/">recently updates</a></small></td>
  </tr>
</table>

<p></p>

<details><summary class="menu">user list</summary><small> &gt; <a href="/users/">list all users</a></small>
<p></p>
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
<p></p>
</div>
  <!-- END USERS -->

      </div>
<!-- END SIDEBAR -->

<?php include 'footer.php'; ?>
