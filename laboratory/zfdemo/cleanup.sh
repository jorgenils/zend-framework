#!/bin/bash -u

find . -iname 'sess_*' -o -iname 'zend_cache*' -o -iname 'admin.email.txt' -o -iname 'zfdemo.log*' -o -iname '*~' | grep -Ev '(svn)|(correct)' | xargs rm
find ./section*/temporary -type f | xargs rm
