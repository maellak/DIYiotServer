# web socket for DIYiotServer

## install

 run /bin/bash  ./install.wsocket
 cd ..
 php ../composer.phar install

## Config

Follow these steps:

1. config Haproxy

 /bin/cp /etc/haproxy/haproxy.cfg /etc/haproxy/haproxy.cfg.myconfig
 /bin/cp /haproxy.cfg /etc/haproxy/haproxy.cfg
 edit the file to fit your needs

2. restart httpd
 
 systemctl restart  httpd

 info: 
	1. change Listen port from 80 to 8888
	2. your httpd server must run on 127.0.0.1 
	   see /etc/haproxy/haproxy.cfg for more information

3. start Haproxy

 systemctl enable haproxy
 systemctl start  haproxy

4.  Disable the XDebug extension. 
 Make sure it is commented out of your php.ini file. 
 XDebug is fantastic for development, but destroys performance in production.


5. config  Supervisor  [Optional Supervisor is a daemon that launches other processes and ensures they stay running]

 echo_supervisord_conf > supervisor.conf
 edit supervisor.conf
 put the following lines and the end of file

	[program:ratchet]
	command                 = bash -c "ulimit -n 10000 && /usr/bin/php path to /diyiot/ws/ws.php"
	process_name            = Ratchet
	numprocs                = 1
	autostart               = true
	autorestart             = true
	user                    = root
	stdout_logfile          = /var/log/httpd/ws.log
	stdout_logfile_maxbytes = 1MB
	stderr_logfile          = /var/log/httpd/ws-error.log
	stderr_logfile_maxbytes = 1MB

 see supervisor.conf for more congif information

 run supervisord -c supervisor.conf
 


## run problems
 see problems

# License
	See LICENSE
