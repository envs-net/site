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
our self-hosted matrix instance with web-client.

<i class="fa fa-matrix-org fa-fw" aria-hidden="true"></i> <a href="https://matrix.envs.net/" target="_blank">matrix.envs.net</a>
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
  <li>spaces - group of rooms and other spaces, begin also with tag <code>#</code></li>
</ul>
<pre>example:</pre>
<ul>
  <li>user creme: <a href="https://matrix.to/#/@creme:envs.net" target="_blank"><code>@creme:envs.net</code></a></li>
  <li>envs room: <a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a></li>
  <li>envs space: <a href="https://matrix.to/#/#space:envs.net" target="_blank"><code>#space:envs.net</code></a></li>
</ul>
<pre>
there's a convenient site, <a href="https://matrix.to" target="_blank">matrix.to</a>, which will
allow you to create urls linking to specific entities. when you visit these pages, you'll see links
to communicate with those entities. If you enter <code>#envs:envs.net</code> on <code>matrix.to</code>, you'll
get the URL <a href="https://matrix.to/#/#envs:envs.net" target="_blank">https://matrix.to/#/#envs:envs.net</a>.
</pre>
<br />

<h2>&#35; matrix&sol;element help &amp; documentation</h2>
<pre>
<a href="https://matrix-help.envs.net/" target="_blank">check out our matrix&sol;element help page.</a>
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
<h3>&#35; irc-bridge to irc.tilde.chat</h3>
<pre>
our envs.net matrix instance provides an federated irc bridge to <a href="https://tilde.chat/" target="_blank">irc.tilde.chat</a>.

<strong>bridge info:</strong>
</pre>
<p></p>
<table>
  <tr><th class="tw160"></th> <th></th></tr>
  <tr>
    <td>network name:</td> <td>irc.tilde.chat</td>
  </tr>
  <tr>
    <td>room alias format:</td> <td><code>#irc.tilde.chat_CHANNELNAME:envs.net</code></td>
  </tr>
  <tr>
    <td>appservice user:</td> <td><a href="https://matrix.to/#/@IRCBot:envs.net" target="_blank">@IRCBot:envs.net</a></td>
  </tr>
  <tr>
    <td>NickServ:</td> <td><a href="https://matrix.to/#/@irc.tilde.chat_NickServ:envs.net" target="_blank">@irc.tilde.chat_NickServ:envs.net</a></td>
  </tr>
  <tr>
    <td>ChanServ:</td> <td><a href="https://matrix.to/#/@irc.tilde.chat_ChanServ:envs.net" target="_blank">@irc.tilde.chat_ChanServ:envs.net</a></td>
  </tr>
  <tr>
    <td>room for support:</td> <td><a href="https://matrix.to/#/envs:envs.net" target="_blank">#envs:envs.net</a></td>
  </tr>
  <tr>
    <td>operator:</td> <td>envs.net (~creme)</td>
  </tr>
</table>
<p></p>
<pre><strong>documentation:</strong></pre>
<ul>
  <li><strong><a href="https://matrix-org.github.io/matrix-appservice-irc/latest/usage.html" target="_blank">matrix irc bridge documentation</a></strong></li>
  <li><strong><a href="https://github.com/matrix-org/matrix-appservice-irc/wiki/End-user-FAQ" target="_blank">end user faq</a></strong></li>
  <li><strong><a href="https://tilde.chat/wiki/" target="_blank">tilde.chat - wiki</a></strong></li>
</ul>
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
<a href="https://matrix.to/#/#space:envs.net" target="_blank"><code>#space:envs.net</code></a>

channel
<a href="https://matrix.to/#/#envs:envs.net" target="_blank"><code>#envs:envs.net</code></a>
<a href="https://matrix.to/#/#lounge:envs.net" target="_blank"><code>#lounge:envs.net</code></a>

abuse management
<a href="https://matrix.to/#/#abuse:envs.net" target="_blank"><code>#abuse:envs.net</code></a>
</pre>
<p></p>
</div>

<div class="block">
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://element.envs.net/';">
    <td><i class="fa fa-hand-o-right fa-fw" aria-hidden="true"></i></td> <td><a href="https://element.envs.net/">element-web client</a></td>
  </tr>
  <tr onclick="window.location='https://cinny.envs.net/';">
    <td><i class="fa fa-hand-o-right fa-fw" aria-hidden="true"></i></td> <td><a href="https://cinny.envs.net/">cinny webclient</a></td>
  </tr>
  <tr onclick="window.location='https://hydrogen.envs.net/';">
    <td><i class="fa fa-hand-o-right fa-fw" aria-hidden="true"></i></td> <td><a href="https://hydrogen.envs.net/">hydrogen-web client<br />(lightweight)</a></td>
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
<strong>rules &sol; guidelines</strong>
<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>
</pre>
<p></p>
</div>

      </div>

<?php include '../../footer.php'; ?>
