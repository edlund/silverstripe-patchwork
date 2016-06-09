#!/bin/bash

script_path="`readlink -f "$0"`"
script_dir="`dirname "${script_path}"`"

server_dir="`dirname "${script_dir}"`/scripts/server"

export PATH="${PATH}:${script_dir}:${server_dir}"

bash

