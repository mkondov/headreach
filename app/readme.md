# Headreach project  
  
1. Install project + dependencies :  
  * git clone git@bitbucket.org:daniel_minchev/headreach-app.git headreach  
  * cd headreach  
  * composer install  
  * pip install -r requirements.txt  
  
2. Setup project  
  * Copy config/db.php.dist to config/db.php  
  * Fill in your database credentials in config/db.php  
  * Copy your own config.ini file in / directory
  
3. Run project  
  * run "php yii serve" or put the whole headreach folder under your webserver root  
  * point your browser to either http://localhost:8080/ or http://localhost/headreach , depending on the above decision