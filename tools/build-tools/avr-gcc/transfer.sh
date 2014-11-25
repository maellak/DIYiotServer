#!/bin/bash

if [ "$1" == "" ]; then
    echo Usage: transfer.sh file.hex
    exit
fi

avrdude -F -V -c arduino -p ATMEGA328P -P /dev/ttyACM0 -b 115200 -U flash:w:$1
