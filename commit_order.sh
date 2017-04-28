#! /bin/bash

review_id=$1
diff1=$2
diff2=$3

cd /tmp/git_clone/$review_id

#echo ancestor if commit #1 is ancestor or descendant if first commit is descendant

if git rev-list $diff1 | grep -q $diff2 ; then git diff $diff2 $diff1 #echo "DESCENDANT"
elif git rev-list $diff2 | grep -q $diff1 ; then git diff $diff1 $diff2 #echo "ANCESTOR"
fi
