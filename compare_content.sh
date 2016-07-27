#!/usr/bin/env bash
list=""
list="$list about"
list="$list algebra"
list="$list books"
list="$list calculus"
list="$list ccalculus"
list="$list cpl"
list="$list de"
list="$list fcalculus"
list="$list forum"
list="$list geometry"
list="$list index"
list="$list links"
list="$list logic"
list="$list mechanics"
list="$list metacourse"
list="$list misc"
list="$list nt"
list="$list physics"
list="$list prog"
list="$list ptms"
list="$list rcalculus"
list="$list search"
list="$list tex"
list="$list varcalculus"
list="$list wanted"

for i in $list ; do
    echo $i
    diff "content/$i/!$i.tex" ".production/dmvn.mexmat.net/content/$i/!$i.tex" | iconv -f cp1251 -t utf-8
done