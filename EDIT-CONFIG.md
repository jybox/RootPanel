# 系统
## /etc/sysctl.conf

修改：

    net.ipv4.ip_forward=1

## /etc/sudoers

新增：

    rpadmin ALL=(ALL) NOPASSWD: ALL

## php.ini

* /etc/php5/apache2/php.ini
* /etc/php5/fpm/php.ini
* /etc/php5/cgi/php.ini
* /etc/php5/cli/php.ini

修改：

    display_errors = On
    post_max_size = 128M
    upload_max_filesize = 128M

新增：

    extension = apc.so
    extension = mongo.so
    extension = memcache.so

## /etc/nginx/nginx.conf

改为：

    user www-data www-data;
    pid /var/run/nginx.pid;

    error_log  /var/log/nginx/error.log info;

    worker_processes 4;
    worker_rlimit_nofile 10240;

    events {
        use epoll;
        worker_connections 1024;
    }

    http {
        include /etc/nginx/mime.types;
        default_type application/octet-stream;

        log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                        '$status $body_bytes_sent "$http_referer" '
                        '"$http_user_agent" "$http_x_forwarded_for" '
                        '"$upstream_cache_status"';

        access_log /var/log/nginx/access.log main;

        sendfile on;
        tcp_nopush on;
        tcp_nodelay on;
        keepalive_timeout 60;

        gzip on;
        gzip_disable "msie6";
        gzip_buffers 4 16k;
        gzip_comp_level 2;
        gzip_http_version 1.1;
        gzip_min_length 1k;
        gzip_types text/plain application/x-javascript text/css application/xml;
        gzip_vary on;

        open_file_cache max=10240 inactive=30s;
        open_file_cache_valid  60s;
        open_file_cache_min_uses 1;

        server_tokens off;
        server_name_in_redirect off;
        server_names_hash_bucket_size 128;

        client_body_buffer_size 16k;
        client_body_timeout 60;
        client_header_buffer_size 2k;
        large_client_header_buffers 4 8k;
        client_header_timeout 60;
        client_max_body_size 10m;

        proxy_buffer_size 16k;
        proxy_buffers 8 32k;
        proxy_busy_buffers_size 64k;
        proxy_cache_path /var/tmp/nginx/cache levels=1:2 keys_zone=lanmp:32m inactive=1h max_size=512m;
        proxy_connect_timeout 30;
        proxy_ignore_headers Set-Cookie Cache-Control Expires;
        proxy_read_timeout  60;
        proxy_send_timeout  30;
        proxy_temp_file_write_size 64k;

        proxy_set_header Host $host;
        proxy_set_header Accept-Encoding '';
        proxy_set_header Referer $http_referer;
        proxy_set_header Cookie $http_cookie;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        fastcgi_intercept_errors on;
        types_hash_max_size 8192;

        include /etc/nginx/conf.d/*.conf;
        include /etc/nginx/sites-enabled/*;
    }


## 删除

* /etc/php5/fpm/pool.d/www.conf
* /etc/nginx/sites-enabled/default
* /etc/apache2/sites-enabled/000-default

# RootPanel
## /etc/php5/fpm/pool.d/rpadmin.conf

新建：

    [rpadmin]

    user = rpadmin
    group = rpadmin

    listen = /tmp/rpadmin-fpm.sock
    listen.owner = rpadmin
    listen.group = rpadmin
    listen.mode = 0660

    pm = dynamic
    pm.max_children = 50
    pm.start_servers = 4
    pm.min_spare_servers = 1
    pm.max_spare_servers = 4

## /etc/nginx/sites-enabled/00000-rpadmin

新建(请替换NODE)：

    server {
        listen 80 default_server;
        rewrite ^/(.*)$ http://NODE.rpvhost.net/#redirect permanent;
    }
    server {
        listen 80;
        server_name NODE.rpvhost.net;

        root /RootPanel/web;
        index index.html index.php;

        access_log /root/nginx.access.log;
        error_log /root/nginx.error.log;

        location / {
            try_files $uri $uri/ /index.php?$args;
        }

        location ~ \.php$ {
            fastcgi_pass unix:/tmp/rpadmin-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
        }

        location ~ /\.(ht|git) {
            deny  all;
        }

    	location /nginx-status {
    		stub_status on;
    		access_log  off;
    	}

    	location /apache2-status {
    	    proxy_pass http://127.0.0.1:8080;
    	}
    }

## /etc/apache2/ports.conf

改为：

    NameVirtualHost *:8080
    Listen 8080

## /etc/apache2/sites-enabled/00000-rpadmin

新建(请替换NODE)：

    <Directory />
        Options +FollowSymLinks
        AllowOverride All
    </Directory>

    ExtendedStatus On

    <VirtualHost *:8080>
        ServerName NODE.rpvhost.net

        <Location /apache2-status>
    		SetHandler server-status
    		Order deny,allow
    		Allow from all
    	</Location>
    </VirtualHost>

# PPTP
## /etc/pptpd.conf

新增：

    localip 10.89.64.1
    remoteip 10.89.64.100-150

## /etc/rc.local

新增：

    iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE