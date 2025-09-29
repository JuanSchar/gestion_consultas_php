-- Create root user for any host
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

-- Create app user for any host
CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED BY 'userpass';
GRANT ALL PRIVILEGES ON gestion_consultas.* TO 'appuser'@'%';
FLUSH PRIVILEGES;