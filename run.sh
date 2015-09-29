#!/usr/bin/env bash

set -e

# php gimme-dot.php > symfony.dot.gv
# cat symfony.dot.gv | dot -Tsvg > symfony.dot.svg

# php gimme-neato.php > symfony.neato.gv
# cat symfony.neato.gv | neato -Tpng > symfony.neato.png
# cat symfony.neato.gv | neato -Tsvg > symfony.neato.svg

php gimme-fdp.php > symfony.fdp.gv
cat symfony.fdp.gv | fdp -Tsvg > symfony.fdp.svg
# cat symfony.fdp.gv | fdp -Tpng > symfony.fdp.png
inkscape -z -f symfony.fdp.svg -w 1200 -e symfony.fdp.png

