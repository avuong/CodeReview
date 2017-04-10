#! /bin/bash

# Check for valid usage
if [ $# -ne 2 ]; then
    echo "usage: ./get_local_path <local_repo_path> <local_repo_name>"
    exit 1
fi

# Collect input params
local_repo_path=$1
local_repo_file=$2

# Specified path must exist and be absolute
if [ ${local_repo_path:0:1} != "/" ]; then
    echo "error: Must clone into an absolute path"
    exit 1
elif [ ! -d $local_repo_path ]; then
    echo "error: Specified local path does not exist"
    exit 1
fi

# Create/format the local path
if [ ${local_repo_path: -1} != "/" ]; then
    local_repo_path+="/"
fi
local_repo=$local_repo_path$local_repo_file

# Return the valid local repository path
echo $local_repo
exit 0
