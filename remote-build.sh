#!/bin/bash
# Trigger rebuild + redeploy on VPS, detached, logging to /tmp/deploy.log
ssh brilink 'cd ~/brilink-h12 && nohup sh -c "sudo docker build -t brilink-h12 . && bash deploy.sh" > /tmp/deploy.log 2>&1 < /dev/null & disown; sleep 1; echo TRIGGERED'
