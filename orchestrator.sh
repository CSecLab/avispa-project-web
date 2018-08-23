#!/bin/bash

docker run -v $(pwd):/files hlpsl2if $1
file=${1/\.hlpsl/\.if}
docker run -v $(pwd):/files avispaproject/satmc:1.0 /files/$file
docker run -v $(pwd):/files turuani/cl-atse:latest /files/$file
docker run -v $(pwd):/files ofmc /files/$file
docker run -v $(pwd):/files -it ta4sp /files/$file
