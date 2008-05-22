#!/bin/bash -u

for table in posts users attachments topics
do
    mysql --host=127.0.0.1 -uadmin -pdemo --database=zfdemo -e 'DESCRIBE posts'
done
