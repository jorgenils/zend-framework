#!/bin/bash

# Simple script to purge the cache files in the temporary/ directory.
# Caching is nice for performance, but annoying when developing ;)

path=`pwd`
find "$path/temporary" -type f | xargs rm
