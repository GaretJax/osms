#!/bin/bash

find . -name '*.php' | xargs grep $@
