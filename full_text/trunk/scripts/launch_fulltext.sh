#!/bin/bash
file='/var/www/html/maarch_trunk_secure/modules/full_text/lucene_full_text_engine.php'
cd /var/www/html/maarch_trunk_secure/modules/full_text/
php $file /var/www/html/maarch_trunk_secure/modules/full_text/xml/config_batch_letterbox.xml
