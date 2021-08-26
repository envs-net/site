<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?=$title ?? "envs.net | environments"?></title>
    <meta name="description" content="<?=$desc ?? "envs.net | environments for linux lovers - since 9/2019"?>" />
    <meta name="url" content="https://envs.net/" />
    <meta name="author" content="Sven Kinne" />
    <meta name="robots" content="index, follow" />
    <meta name="revisit-after" content="7 days" />
    <meta name="keywords" content="envs,enviroments,env,tilde,tildeverse,tldr,userspace,space,real-time,tiny,minimalist,shared,non-commercial,linux,lxc,debian,shell,bash,programmer,hackers,console,hosting,selfhosting,openknowledge,decentralize,communication,ffsync,fediverse,federated,social,webpage,blog,gemini,gopher,forum,bbj,search,searx,news,feed,rss,atom,collaborative,hedgedoc,codimd,pad,cryptpad,code,codepad,dot,git,gitea,irc,paste,pastebin,privatebin,pb,file,curl,nullpointer,0x0,shorten,shorter,shortener,share,upload,twtxt,getwtxt,tt-rss,mobilizon,pleroma,matrix,element,riot,hydrogen,dimension,ip,ifconfig,ipconfig,showip,whois,znc" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/css/css_style.css" />
    <link rel="stylesheet" href="/css/fork-awesome.min.css" />
    <?=$additional_head ?? ""?>
    <?php unset($title); unset($desc); unset($additional_head); ?>

  </head>
