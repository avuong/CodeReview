#! /bin/bash

# Check for valid usage
if [ $# -ne 3 ]; then
    echo "usage: ./sql_load_commits.sh <local_repo> <review_id> <sql_userid>"
    exit 1
fi

# Collect input params
local_repo=$1
review_id=$2
sql_userid=$3

# Specified path must be a git repository
git -C $local_repo rev-parse 2> /dev/null
if [ $? -ne 0 ]; then
    echo "error: Specified path is not a git repository"
    exit 1
fi

# Add the review to the database
sql_cmd="INSERT INTO reviews (id, owner) \
	VALUES ('$review_id', '$sql_userid');"
echo $sql_cmd | sqlplus -S guest/guest

# cd to the cloned repo
cd $local_repo

# Get the HEAD commit id
root_commit=$(git rev-parse HEAD)
curr_commit=$root_commit

# Loop through commit hierarchy and populate database with commit info
parent_commit=true
while [ -n "$parent_commit" ]; do
	# Grab metadata & insert
    c_msg=$(git log --format=%B -n 1 $curr_commit)
    c_auth=$(git --no-pager show -s --format='%an <%ae>' $curr_commit)
    sql_cmd="INSERT INTO commits (id, author, message, review_id) \
        VALUES ('$curr_commit', '$c_auth', '$c_msg', '$review_id');"
    echo $sql_cmd | sqlplus -S guest/guest

	# Get next parent
	parent_commit=$(git log --pretty=%P -n 1 $curr_commit)
	curr_commit=$parent_commit
done
