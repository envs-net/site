<?php
  $title = "envs.net | matrix chat";
  $desc = "envs.net | matrix chat";

include '../../header.php';
?>

  <body id="body" class="dark-mode">
    <div class="clear" style="min-width: 1280px;">

      <div class="button_back">
        <pre class="clean"><strong><a href="/chat">&lt; back</a></strong></pre>
      </div>

      <div id="main">
<div class="block">
<h1><em>matrix chat</em></h1>
<pre>
our self-hosted matrix instance with element-web client.

<a href="https://matrix.envs.net/" target="_blank">matrix.envs.net</a>
</pre>
<p></p>
</div>

<pre>
an open network for secure, decentralized communication.

<a href="https://element.envs.net/#/register" target="_blank"><strong>&gt; create your matrix account on envs. &lt;</strong></a>

</pre>

<h2>&#35; identifiers/entities</h2>
<pre>
schema:
&nbsp;&nbsp;<code>&lt;tag&gt;&lt;id&gt;:&lt;instance&gt;</code>
</pre>
<br />
<ul>
  <li>user, which begin with tag <code>@</code></li>
  <li>chat-rooms (channel), which begin with tag <code>#</code></li>
</ul>
<pre>example:</pre>
<ul>
  <li>user creme: <a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a></li>
  <li>envs room: <a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a></li>
  <li>envs space: <a href="https://matrix.to/#/#community:envs.net" target="_blank"><code>#community:envs.net</code></a></li>
</ul>
<pre>
there's a convenient site, <a href="https://matrix.to" target="_blank">matrix.to</a>, which will
allow you to create urls linking to specific entities. when you visit these pages, you'll see links
to communicate with those entities. If you enter <code>+envs:envs.net</code> on <code>matrix.to</code>, you'll
get the URL <a href="https://matrix.to/#/+envs:envs.net" target="_blank">https://matrix.to/#/+envs:envs.net</a>.
</pre>
<br />

<h2>&#35; matrix client software</h2>
<pre>
<a href="https://element.io/get-started" target="_blank">check out the official element client!</a>
an extended client overview can be found at: <a href="https://matrix.org/clients/" target="_blank">https://matrix.org/clients/</a>
</pre>
<br />

<h2>&#35; features</h2>
<p></p>
<details><summary class="menu" id="abuse"><strong>&#35; abuse management</strong></summary>
<pre>
our matrix instance has a moderation tool <a href="https://github.com/matrix-org/mjolnir" target="_blank">mjolnir</a>.
to protect your own room from known spammers and evil accounts, you can simply give <strong><a href="https://matrix.to/#/@mjolnir:envs.net" target="_blank">@mjolnir:envs.net</a></strong>
an <strong>invite</strong> and <strong>admin</strong> Permissions in the room.
the <a href="https://matrix.to/#/+abuse:envs.net" target="_blank">+abuse:envs.net</a> moderators then receive the invitation and have to confirm it.
after this step, the room is protected.

you are welcome to join <a href="https://matrix.to/#/#abuse:envs.net" target="_blank">#abuse:envs.net</a> to report known spammers and evil peoples.

<em>notice</em>:
you can also subscribe to our banlist to ignore the banned users even in unprotected rooms.

to do this, you need to allow the <code>showLabsSettings</code> feature in your element config (<a href="https://element.envs.net/config.json" target="_blank">see sample config</a>)
and then activate in <code>settings -> labs -> Try out new ways to ignore people</code>.

in the last step you need to subscribe our banlist: <a href="https://matrix.to/#/#envs-ban-list:envs.net" target="_blank">#envs-ban-list:envs.net</a> (<code>!UyrSHIwWgbGsHjabGe:envs.net</code>)
under <code>settings -> ignored users</code>.

<em>official matrix.org banlists:</em>
<a href="https://matrix.to/#/#matrix-org-coc-bl:matrix.org" target="_blank">#matrix-org-coc-bl:matrix.org</a> (coc violations)
<a href="https://matrix.to/#/#matrix-org-hs-tos-bl:matrix.org" target="_blank">#matrix-org-hs-tos-bl:matrix.org</a> (toc violations)
</pre>
</details>
<p></p>
<details><summary class="menu" id="bots"><strong>&#35; bot's</strong></summary>
<pre>feel free to use our bot's in your room! (<a href="https://github.com/maubot/maubot" target="_blank">maubot plugin overview</a>)</pre>
<p></p>
<?php
  $menu = [
    'RSS Bot' => ['mxid' => '@rss:envs.net'],
    'Reminder' => ['mxid' => '@redminder:envs.net'],
    'sed Bot' => ['mxid' => '@sed:envs.net'],
    'Poll Bot' => ['mxid' => '@poll:envs.net'],
    'Karma Bot' => ['mxid' => '@reminder:envs.net'],
    'urbandictionary Bot' => ['mxid' => '@urban:envs.net'],
    'Translator' => ['mxid' => '@tranlate:envs.net'],
    'Wolfram Alpha Bot' => ['mxid' => '@wolframalpha:envs.net'],
    'Factorial Bot' => ['mxid' => '@factorial:envs.net'],
    'Dice Bot' => ['mxid' => '@dice:envs.net'],
    'XKCD' => ['mxid' => '@xkcd:envs.net'],
    'CommitStrip' => ['mxid' => '@commitstrip:envs.net'],
    'Cat Disruptor' => ['mxid' => '@cat:envs.net'],
    'echobot [envs]' => ['mxid' => '@echo:envs.net']
  ];
  echo "<pre>\n";
  foreach ($menu as $name => $m_entry) {
    if ($name == 'Wolfram Alpha Bot') echo "\n";
    echo "<a href=\"https://matrix.to/#/${m_entry['mxid']}\" target=\"_blank\">${name}</a>";
    if ($name != 'echobot [envs]') echo " | ";
  };
  echo "\n</pre>\n";
?>

<pre>you can try all the bots in <a href="https://matrix.to/#/#test:envs.net" target="_blank">#test:envs.net</a>.</pre>
</details>
<br/>

<h2>&#35; links</h2>
<pre>
<a href="https://matrix.org/" target="_blank">matrix.org</a>
<a href="https://element.io/" target="_blank">element.io</a>
</pre>

      </div>

      <div id="sidebar">

<div class="block">
<pre>server admin
<a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a>

space
<a href="https://matrix.to/#/#community:envs.net" target="_blank"><code>#community:envs.net</code></a>

channel
<a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a>
<a href="https://matrix.to/#/#lounge:envs.net" target="_blank"><code>#lounge:envs.net</code></a>
</pre>
<p></p>
</div>

<div class="block">
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://element.envs.net/';">
    <td><i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i></td> <td><a href="https://element.envs.net/">element-web client</a></td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr onclick="window.location='https://dimension.envs.net/';">
    <td><i class="fa fa-object-ungroup fa-fw" aria-hidden="true"></i></td> <td><a href="https://dimension.envs.net/">dimension -<br />integrations manager</a></td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr onclick="window.location='https://lag.envs.net/';">
    <td><i class="fa fa-arrows-h fa-fw" aria-hidden="true"></i></td> <td><a href="https://lag.envs.net/">latency monitor</a></td>
  </tr>
</table>
</div>

<div class="block">
<pre>
<strong>rules / guidelines</strong>
<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
</pre>
<p></p>
</div>

      </div>

<?php include '../../footer.php'; ?>
