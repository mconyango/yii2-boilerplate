#!/usr/bin/env bash
# set permissions for yii project
# Only folders are affected (-type d). For files, (-type f)
PROJECT_ROOT=$PWD
BACKEND_RUNTIME=${PROJECT_ROOT}/_protected/backend/runtime
CONSOLE_RUNTIME=${PROJECT_ROOT}/_protected/console/runtime
API_RUNTIME=${PROJECT_ROOT}/_protected/api/runtime
UPLOADS_DIR=${PROJECT_ROOT}/uploads
ASSETS_DIR=${PROJECT_ROOT}/assets

# sort permissions, creating the dirs specified if they don't exist
function sortPerms()
{
    # check if this arg is a dir
    if [[ -d $1 ]]; then
        # create it, if its not there
        if [[ ! -d $1 ]]; then
            echo "creating the ${1} folder..."
            mkdir "${1}"
        fi
        # change its permissions
        echo "setting permissions to ${2} for ${1} ..."
        find "${1}" -type d -print0 | xargs -0 chmod ${2}
    fi
}

# do checks
function performChecks()
{
    # sort permissions for this folders
    sortPerms "${PROJECT_ROOT}" 755
    sortPerms "${BACKEND_RUNTIME}" 755
    sortPerms "${CONSOLE_RUNTIME}" 755
    sortPerms "${API_RUNTIME}" 755
    sortPerms "${ASSETS_DIR}" 755
    sortPerms "${UPLOADS_DIR}" 755

    echo "Done setting permissions..."

}

# to prevent permission errors, we enforce sudo access. so, here we check if user is sudo
if [ "$EUID" -ne 0 ]; then
  echo "You need to run this as a super user"
  exit 1
else
    # user selection
    echo "As $USER do you want to correct permissions for all directories under ${PROJECT_ROOT} ?. select 1 or 2"
    select yn in "Yes" "No"; do
    case ${yn} in
        Yes ) performChecks;

        break;;
        No ) exit;;
    esac
    done
fi
