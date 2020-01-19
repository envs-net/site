<?php
  $title = "envs.net | irc chat";
  $desc = "envs.net | irc chat";

include '../../header.php';
?>

  <body id="body" class="dark-mode">
    <div class="clear" style="min-width: 1280px;">

      <div class="button_back">
        <pre class="clean"><strong><a href="/chat">&lt; back</a></strong></pre>
      </div>

      <div id="main">
<div class="block">
<h1><em>irc chat</em></h1>
<pre>
envs.net does not have its own irc server but we are a member of the <a href="https://tildeverse.org/" target="_blank">tildeverse</a> - irc group.
our channel is <strong>#envs</strong> on <a href="https://tilde.chat/" target="_blank">tilde.chat</a> irc.

&gt; <a href="https://tilde.chat/stats/" target="_blank">channel list and server stats</a>
</pre>
</div>

<pre>
the tilde.chat irc network is available at <code>irc.tilde.chat:6697</code> (with ssl)
as a round robin of <a href="https://tilde.chat/wiki/?page=servers" target="_blank">available nodes</a>.

tilde.chat is meant to be a space for all tilde members to hang out, share, teach, and learn.
Hang out in <strong>#meta</strong> the tilde general channel. =)

<strong>note</strong> you must register with <code>nickserv</code> to talk in <code>#meta</code>:
<code>/msg nickserv register &lt;password&gt; &lt;email&gt;</code>

</pre>

<h3>&#35; here are some options to connect</h3>
<pre>
<ul>
<li><code>weechat</code> (<em>when logged in with ssh</em>) / <code>irssi</code> (<em>needs to configure</em>)</li>
<li><code><a href="https://znc.envs.net/" target="_blank">znc</a></code> - please contact <a href="https://envs.net/~creme/">creme</a> (via irc/matrix or email) to request an account,<br />then log in with your email password and configure as needed.</li>
<li>run <code>webirc</code> to register your thelounge account for <a href="https://webirc.envs.net/" target="_blank">our webchat</a><br /><em>note that thelounge does not authenticate with your shell/mail password.</em></li>
<li>any other client that you like: connect to our node at <code>irc.tilde.chat:6697</code> with ssl</li>
</ul>
</pre>

<h3>&#35; znc (irc bouncer)</h3>
<pre>
the system znc authenticates using your email credantials (<em>user@envs.net</em> | <em>your email pw</em>).

the znc interface is available at <a href="https://znc.envs.net/" target="_blank">znc.envs.net</a>.
client connections are on port <code>6667</code> and <code>6697</code> (both with ssl).

you can add network connections (up to 10) via webadmin or via a direct client connection.
see the <a href="https://wiki.znc.in/Connecting_to_ZNC" target="_blank">znc wiki</a> for more information.

</pre>

<h3>&#35; weechat relays</h3>
<pre>
weechat introduced <a href="https://weechat.org/files/doc/stable/weechat_user.en.html#relay_unix_socket" target="_blank">unix socket relays</a> in version 2.5 which is a much easier way to
offer per-user relay access.
<code><em>username.envs.net/weechat</em></code> is configured to proxy to the default unix relay
socket location (<code>~/.weechat/relay_socket</code>). to get started using it, follow these steps:
<ol>
<li>in weechat:</li>
<ul class="sublist">
<li><code>/relay add unix.weechat %h/relay_socket</code></li>
<li><code>/set relay.network.password mysupersecretpassword</code></li>
</ul>
<li>at your shell:</li>
<ul class="sublist">
<li><code>chmod o+rw ~/.weechat/relay_socket</code> - <em>this allows nginx to connect to your socket on your behalf</em></li>
</ul>
<li>in your relay client:</li>
<ul class="sublist">
<li>weechat-android:</li>
<ul class="sublist">
<li>connection type: websocket (ssl)</li>
<li>websocket path: weechat</li>
<li>relay host: username.envs.net</li>
<li>relay port: 443</li>
<li>your relay supersecretpassword</li>
</ul>
</ul>
</ol>
</pre>

      </div>

      <div id="sidebar">

<div class="block">
<pre>
channel <strong>#envs</strong> on
<code>irc.tilde.chat:6697</code>
</pre>
</div>

<div class="block">
<table>
  <tr><th class="tw25"></th> <th></th></tr>
  <tr onclick="window.location='https://webirc.envs.net/';">
    <td><i class="fa fa-commenting-o fa-fw" aria-hidden="true"></i></td> <td><a href="https://webirc.envs.net/">webirc-client</a></td>
  </tr>
  <tr onclick="window.location='https://znc.envs.net/';">
    <td><i class="fa fa-compress fa-fw" aria-hidden="true"></i></td> <td><a href="https://znc.envs.net/">znc interface</a></td>
  </tr>
</table>
</div>

<div class="block">
<h3>rules / guidelines</h3>
<pre>
<em>please see the <a href="/chat/etiquette">etiquette guide</a>.</em>

</pre>

<h3>wiki</h3>
<pre>
<em>see the <a href="https://tilde.chat/wiki/" target="_blank">tilde.chat wiki</a> for
info on bots and other specifics.</em>
</pre>
</div>

      </div>

<?php include '../../footer.php'; ?>
