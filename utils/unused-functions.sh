#!/bin/bash

for f in `git grep -E "function \w+\s*\(" | egrep -o "function \w+" | cut -d ' ' -f 2 | sort -u`
do
	#echo -n "$f : "
	count=`git grep -ch "$f("`
	if [[ ! -n $count ]]
	then
		echo "$f is unused"
	fi
done
