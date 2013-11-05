RDS_snapshot_mgmt
============

This app allows you to easily store a chosen number of RDS snapshots and manage the deletion of those snapshots as you create more.


```
mkdir vendor
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

config file format is json (-c config_filename)
```
{
  "includes": ["_aws"],
  "services": {
      "default_settings": {
          "params": {
              "key": "YOURAWSKEY",
              "secret": "YOURAWSSECRETKEY",
              "region": "us-east-1",
              "scheme": "https"
          }
      }
  }
}
```
```
USAGE: php RDS_snapshot_management.php -c [config file in json format] -i [db instance identifier] -n [number of backups you'd like to keep]
```
