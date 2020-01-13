
# `authproxy` DokuWiki HTTP authentication plugin

* Copyright (c) 2013-2018 Pieter Hollants <pieter@hollants.com>
* Copyright (c) 2019 David Newhall II <captain@golift.tv>
* Licensed under the GNU Public License (GPL) version 3
* Original code: [https://github.com/pief/authhttp](https://github.com/pief/authhttp) (Pieter's code)
* Original Docs: [https://www.dokuwiki.org/plugin:authhttp](https://www.dokuwiki.org/plugin:authhttp)
  (Pieter's code's wiki)

## Description

This plugin is a copy of the HTTP Auth Plugin written by Pieter Hollants.
It has been modified only slightly to allow authentication against arbitrary HTTP
headers. I use this with nginx proxy auth behind Organizr. It allows users that have
already authenticated to Organizr to log into DokuWiki automatically.

Below you will find a sample Nginx config to use this with Organizr inside the
letsencrypt Docker container. You can adapt this to suit your app pretty easy.
The code will work with any proxy auth headers as long as you have an email
address and username.

**Note**: My wiki is currently private. I have not tested this with `/auth-999` yet
which will allow the wiki to become public. _I'll update this when I decide to make
my wiki public._

## NGINX Config

```shell
location /wiki {
  auth_request     /auth-1;
  auth_request_set $authuser  $upstream_http_x_organizr_user;
  auth_request_set $authemail $upstream_http_x_organizr_email;
  root             /config/www/;
  index            index.php;
  try_files        $uri $uri/ /index.php;

  location ~ /wiki/(conf|bin|inc|vendor)/ {
      deny all;
  }

  location ~ /wiki/data/ {
      internal;
  }

  location ~ \.php$ {
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    include                 /etc/nginx/fastcgi_params;
    fastcgi_param           X-WEBAUTH-USER  $authuser;
    fastcgi_param           X-WEBAUTH-EMAIL $authemail;
    fastcgi_index           index.php;
    fastcgi_pass            127.0.0.1:9000;
  }
}
```
