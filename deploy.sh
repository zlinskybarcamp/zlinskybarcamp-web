#!/bin/bash
set -e

if [ ! $# == 1 ]; then
  echo "Usage: $0 <test|prod>"
  exit
fi

#if [ "$1" == "test" ] ; then
#  REMOTE_DIR="/var/www/barcampkolin.cz/www"
#  LOCAL_DIR=""
#  SERVER_NAME="mlh"
#  echo "Deploying to TEST environment"
#else
  REMOTE_DIR="/home/www/zlinskybarcamp.cz/subdomains/www"
  LOCAL_DIR=""
  SERVER_NAME="cst-zlinskybarcamp.cz@redbit-1-www4.superhosting.cz"
  echo "Deploying to LIVE environment"
#fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SSH="ssh ${SERVER_NAME}"

echo "Uploading data to SSH…"
rsync -rcP --delete --exclude-from="${DIR}/.rsync-exclude" "${DIR}${LOCAL_DIR}/" "${SERVER_NAME}:$REMOTE_DIR/"

echo "Remove temporary files…"
${SSH} find ${REMOTE_DIR}/temp -mindepth 2 -type f -delete

echo -n "Remove nette email-sent marker… "
${SSH} /bin/bash << EOF
	if [ -f ${REMOTE_DIR}/log/email-sent ]
	then
		rm ${REMOTE_DIR}/log/email-sent
		echo "removed"
	else
		echo "no exists"
	fi
EOF
