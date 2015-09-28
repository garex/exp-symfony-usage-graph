#!/usr/bin/env bash

set -e

# php gimme-dot.php > symfony.dot.gv
# cat symfony.dot.gv | dot -Tsvg > symfony.dot.svg

php gimme-neato.php > symfony.neato.gv
cat symfony.neato.gv | neato -Tpng > symfony.neato.png
cat symfony.neato.gv | neato -Tsvg > symfony.neato.svg

