#!/bin/bash

docker run -v $(pwd):/files hlpsl2 $1
file=${1/\.hlpsl/\.if}
docker run -v $(pwd):/files satmc /files/$file 
docker run -v $(pwd):/files -it ta4sp /files/$file
