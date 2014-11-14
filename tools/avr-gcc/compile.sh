#!/bin/bash

if [ "$1" == "" ]; then
    echo Usage: compile.sh file.c
    exit
fi

DEVICE="atmega328p"    # Arduino Uno microcontroller
CLOCK="16000000"       # 16 MHz
avr-gcc $1 -Os -o $1.elf -DF_CPU=${CLOCK} -mmcu=${DEVICE}
avr-objcopy -j .text -j .data -O ihex $1.elf $1.hex
avr-size --format=avr --mcu=${DEVICE} $1.elf
