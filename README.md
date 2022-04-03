sudo chmod 777 ./ -R
sudo chmod 0444 conf/my.cnf

define('cs_db_host', 				'db');
define('cs_db_user',				'user');
define('cs_db_password',			'test');
define('cs_db_name',				'programareonline');
define('cs_db_charset',				'utf8');

docker stop medlistdocker_www_1 medlistdocker_phpmyadmin_1 medlistdocker_db_1
docker rm medlistdocker_www_1 medlistdocker_phpmyadmin_1 medlistdocker_db_1
docker volume rm medlistdocker_persistent

docker-compose up --build --force-recreate

Open phpmyadmin at [http://localhost:8000](http://localhost:8000)
Open web browser to look at a simple php example at [http://localhost:8001](http://localhost:8001)