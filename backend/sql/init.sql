CREATE DATABASE IF NOT EXISTS banking_app;
CREATE DATABASE IF NOT EXISTS banking_app_test;

-- Inutile de le faire pour banking_app, car docker s'en charge via docker-compose.yml et les var dans MYSQL_DATABASE, USER,... 
GRANT ALL PRIVILEGES ON banking_app_test.* TO 'santa'@'%';
FLUSH PRIVILEGES;