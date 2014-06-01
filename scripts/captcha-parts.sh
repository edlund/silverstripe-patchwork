#!/bin/bash

letters="abcdefghijklmopqrstuvwxyz0123456789"
for (( i=0 ; i<${#letters} ; i++ )) ; do
	l=${letters:$i:1}
	espeak -w ${l}.wav "${l}"
done

