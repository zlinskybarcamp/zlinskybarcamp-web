#!/bin/bash

REMOTE_DIR="/var/www/zlinskybarcamp.cz/www"
LOCAL_DIR=""
SERVER_NAME="mlh"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SSH="ssh ${SERVER_NAME}"

echo "Uploading data to SSH…"
rsync -rcP --delete --exclude-from="${DIR}/.rsync-exclude" "${DIR}${LOCAL_DIR}/" "${SERVER_NAME}:$REMOTE_DIR/"

echo "Replace file permissions…"
$SSH sudo fixwww $REMOTE_DIR

echo "Remove temporary files…"
$SSH find $REMOTE_DIR/temp -mindepth 2 -type f -delete

echo -n "Remove nette email-sent marker… "
ssh sbl /bin/bash << EOF
    if [ -f ${REMOTE_DIR}/log/email-sent ]
    then
        rm ${REMOTE_DIR}/log/email-sent
        echo "removed"
    else
        echo "no exists"
    fi
EOF
