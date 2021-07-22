<?php
  $title = "envs.net | etiquette";
  $desc = "envs.net | etiquette guidelines";

include '../../header.php';
?>

  <body id="body" class="dark-mode">
    <div class="clear">

      <div class="button_back">
        <pre class="clean"><strong><a href="/chat">&lt; back</a></strong></pre>
      </div>

      <div id="main">
<div class="block">
<h1><em>etiquette guidelines</em></h1>
<pre>
some rules adapted from: <a href="https://github.com/fizerkhan/irc-etiquette" target="_blank">fizerkhan/irc-etiquette</a>
</pre>
<p></p>
</div>

<h2>&#35; no flooding/spamming/trolling in main channels</h2>
<pre>
multiline messages belong in a pastebin. feel free to use <a href="https://envs.sh" target="_blank">https://envs.sh</a>
( <code>echo "text here" | curl -F'file=@-;' https://envs.sh</code> ) or
<a href="https://pb.envs.net" target="_blank">https://pb.envs.net</a> for a graphical pastebin.

bot commands belong in <a href="https://matrix.to/#/#bots:envs.net" target="_blank">#bots:envs.net</a>. please limit yourself to 2 or 3 commands at a time.
spamming commands is unacceptable and subject to the consequences below.
</pre>
<br />

<h2>&#35; don't be lazy - read the documentation</h2>
<pre>
if people tell you to read the documentation then you should do so.
never say: &quot;<em>i'm too lazy. come on. you all know what i need to do. just
tell me what i need to do.</em>&quot; this will get you ignored for all eternity.
however you can expect to be told where to find the documentation. but if you
get an url then get it and read it. if the documentation is too technical or you
don't understand certain sections then say: &quot;<em>i have now read chapter 3.1 of
the url you gave me and i understand how virtual domains work in general. but
how would i use both virtual and non-virtual domains together?</em>&quot;
</pre>
<br />

<h2>&#35; unintended rudeness</h2>
<pre>
sometimes you may feel that others are rude. you may get a response like
&quot;<em>reinstall the package. restart the service. read the
/usr/share/doc/mysoftware/readme.</em>&quot; although this is a very brief reply it
is likely not meant to be rude. chats are often like human interaction without
all the friendly bits. other people have probably answered your question a dozen
of times today and they just want to help you solve the problem - not become
close friends with you. don't be offended by it. the people don't mean it.
</pre>
<br />

<h2>&#35; try to not repeat yourself</h2>
<pre>
asking the same question every minute is annoying. if anyone in the channel
isn't paying attention then they will neither read you the second and third
time. and those who came back from lunch or sleep will likely see in their
&quot;lastlog&quot; what has happened lately. this is too demanding.
</pre>
<br />

<h2>&#35; avoid obnoxious behavior</h2>
<pre>
envs is a purpose-driven community, intended to provide an environment
where people can feel comfortable and supported in learning, practicing, or
teaching about technology. while this purpose permits for a wide variety of
activities on envs irc and matrix channels, obnoxious or intentionally annoying
behavior is not allowed. be considerate of the others on the IRC board; think
about how they will perceive your chat messages. if people ask you to stop
something, it's probably because you're being  obnoxious, and you should cut it
out. examples of obnoxious behavior include posting your stream of consciousness
ideas when you are not actually interacting with others, repeating variations of
phrases over and over, intentional join/part spam, or interacting with bots to
the point of crowding out interactions from others.
</pre>
<br />

<h2>&#35; don't assume pronouns</h2>
<pre>if you know someone's pronouns, use them. try to default to they/them if you don't know.</pre>
<ul>
  <li>check with tildebot to see if the person in question has set pronouns: <code>/query tildebot pronouns <nick></code></li>
  <li>ask them!</li>
</ul>
<br />

<h2>&#35; last but not least</h2>
<pre>
be considerate to your fellow tildizens and don't forget to have fun!
</pre>
<br />

<h2>&#35; consequences</h2>
<br />
<ul>
	<li>first offenses will be handled with a kick from the channel with an<br>
explanation of the undesirable channel. at this point, it's acceptable to rejoin.</li>
	<li>second offenses will be handled with a 1 hour tempban. following the<br>
tempban, an chat admin will check in and let you know what kinds of behaviors to avoid.</li>
</ul>
<p></p>

<pre>
if the undesirable behavior continues, a permanent ban is applied.
note that specific actions will also result in a permanent ban.

if you have something that requires admin attention, please send an email to
sudoers at envs dot net or join the matrix room <a href="https://matrix.to/#/#abuse:envs.net" target="_blank">#abuse:envs.net</a>.
</pre>
<br />

<h2>&#35; code of conduct & terms of service</h2>
<pre>
please also note our <a href="/coc/">code of conduct</a> and <a href="/tos/">terms of service</a>.
</pre>
      </div>

<?php include '../../footer.php'; ?>
