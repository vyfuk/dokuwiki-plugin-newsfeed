input fks
verbatimtex
%&latex
\documentclass[9pt]{extarticle}
\usepackage[utf8]{inputenc}
\usepackage{fkssugar}
\begin{document}
etex

input metaobj;
prologues :=3;




beginfig(1);

fill fullcircle scaled 2.5u shifted (2u,5u) withcolor black;

fill fullcircle scaled 2.5u shifted (8u,2u) withcolor black;
fill fullcircle scaled 2.5u shifted (8u,8u) withcolor black;

draw (8u,2u)--(2u,5u)--(8u,8u) wp3;

endfig;

beginfig(2);

draw (7u,5u)--(3u,5u) wp3;
draw (7u,3u)--(3u,3u) wp3;

draw (7u,7u)--(3u,7u) wp3;

draw (1u,2u)--(2u,1u)--(3u,2u) wp3;
draw (2u,1u)--(2u,5u) wp3;

draw (9u,8u)--(8u,9u)--(7u,8u) wp3;
draw (8u,9u)--(8u,5u) wp3;


endfig;

beginfig(3)

draw halfcircle scaled (2u*sqrt(2)) rotated 135 shifted (2u,2u) wp3;

draw halfcircle scaled (2u*sqrt(2)) rotated -45 shifted (8u,8u) wp3;

draw fullcircle scaled (2u*sqrt(2)) shifted (5u,5u) wp3;

fill (7u,3u)--(5u,5u)--(7u,7u)--(9u,5u)--cycle withcolor white;

fill (3u,7u)--(5u,5u)--(3u,3u)--(1u,5u)--cycle withcolor white;

draw (1u,3u)--(4u,6u) wp3;

draw (3u,1u)--(4u,2u) wp3;

draw (6u,8u)--(7u,9u) wp3;

draw (6u,4u)--(9u,7u) wp3;




 

endfig; 


end.
