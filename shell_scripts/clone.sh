#! /bin/bash
# usage: clone.sh <remote repo> <local dir> <repo name> <sql_userid> [password]
remote_repo=$1
local_path=$2
repo_name=$3
sql_userid=$4
password=$5

# echo exit code before exiting
function quit {
    echo $1
    exit $1
}

# Check for valid usage
if [ $# -ne 4 ] && [ $# -ne 5 ]; then
    quit 1
fi

# Verify a valid local repo path
local_repo=$(/home/ec2-user/apache/htdocs/shell_scripts/get_local_path.sh $local_path $repo_name)
if [ $? -ne 0 ]; then
    quit 1
fi

# Clone the remote repository
/home/ec2-user/apache/htdocs/shell_scripts/git_clone.exp $remote_repo $local_repo $password > /dev/null
ret=$?
if [ $ret -ne 0 ]; then
    quit $ret
fi

# Load commit metadata into SQLPlus
#/home/ec2-user/apache/htdocs/shell_scripts/sql_load_commits.sh $local_repo $repo_name $sql_userid > /dev/null
quit $?
