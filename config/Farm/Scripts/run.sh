#!/bin/sh

WIKI="${1}"
SLEEP_SECONDS="${2}"
BATCH_SIZE="${3}"

JOBRUNNER_CMD="$MW_INSTALL_PATH/maintenance/run runJobs"

# https://www.mediawiki.org/wiki/Manual:Job_queue#Create_script

echo Starting job service...
echo Started.
while true; do
	# Job types that need to be run ASAP no matter how many of them are in the queue
	# Those jobs should be very "cheap" to run
	stdbuf -oL -eL $JOBRUNNER_CMD --wiki="$WIKI" --type="enotifNotify"
	# Everything else, limit the number of jobs on each batch
	# The --wait parameter will pause the execution here until new jobs are added, to avoid running the loop without anything to do
	stdbuf -oL -eL $JOBRUNNER_CMD --wiki="$WIKI" --wait --maxjobs="$BATCH_SIZE"
	# Wait some seconds to let the CPU do other things, like handling web requests, etc
	echo Waiting for "$SLEEP_SECONDS" seconds...
	sleep "$SLEEP_SECONDS"
done
