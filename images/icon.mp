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


color iniText,iniBack,iniAlt;

iniText:=black;
iniBack:=white;
iniAlt:=(0.067,0.459,0.855);

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




beginfig(4);

fill fullcircle scaled 2.5u shifted (2u,5u) withcolor (0.067,0.459,0.855);

fill fullcircle scaled 2.5u shifted (8u,2u) withcolor (0.067,0.459,0.855);
fill fullcircle scaled 2.5u shifted (8u,8u) withcolor (0.067,0.459,0.855);

draw (8u,2u)--(2u,5u)--(8u,8u) wp3 withcolor (0.067,0.459,0.855);

endfig;

beginfig(5);

draw (7u,5u)--(3u,5u) wp3 withcolor (0.067,0.459,0.855);
draw (7u,3u)--(3u,3u) wp3 withcolor (0.067,0.459,0.855);

draw (7u,7u)--(3u,7u) wp3 withcolor (0.067,0.459,0.855);

draw (1u,2u)--(2u,1u)--(3u,2u) wp3 withcolor (0.067,0.459,0.855);
draw (2u,1u)--(2u,5u) wp3 withcolor (0.067,0.459,0.855);

draw (9u,8u)--(8u,9u)--(7u,8u) wp3 withcolor (0.067,0.459,0.855);
draw (8u,9u)--(8u,5u) wp3 withcolor (0.067,0.459,0.855);


endfig;

beginfig(6)

draw halfcircle scaled (2u*sqrt(2)) rotated 135 shifted (2u,2u) wp3 withcolor (0.067,0.459,0.855);

draw halfcircle scaled (2u*sqrt(2)) rotated -45 shifted (8u,8u) wp3 withcolor (0.067,0.459,0.855);

draw fullcircle scaled (2u*sqrt(2)) shifted (5u,5u) wp3 withcolor (0.067,0.459,0.855);

fill (7u,3u)--(5u,5u)--(7u,7u)--(9u,5u)--cycle withcolor white;

fill (3u,7u)--(5u,5u)--(3u,3u)--(1u,5u)--cycle withcolor white;

draw (1u,3u)--(4u,6u) wp3 withcolor (0.067,0.459,0.855);

draw (3u,1u)--(4u,2u) wp3 withcolor (0.067,0.459,0.855);

draw (6u,8u)--(7u,9u) wp3 withcolor (0.067,0.459,0.855);

draw (6u,4u)--(9u,7u) wp3 withcolor (0.067,0.459,0.855);



endfig; 

beginfig(7)



fill fullcircle scaled 2u shifted (2u,2u) withcolor white;

draw (2u,5u){right} .. (5u,2u) wp 7.5 withcolor white;;

draw (2u,8u){right} .. (8u,2u) wp 7.5 withcolor white;;

endfig;




vardef kol(expr c)=
fill (
for i= 0  step 1 until 7:
(4u*cos(pi*i/4),4u*sin(i*pi/4))..(2.75u*cos((i+.5)*pi/4),2.75u*sin((i+.5)*pi/4))..
endfor cycle).shifted(5u,5u) withcolor c;

unfill fullcircle scaled 3u shifted (5u,5u)  ;
enddef;



beginfig(10)
kol(iniText);
endfig;

beginfig(11)
kol(iniAlt);
endfig;

beginfig(12)
kol(iniBack);
endfig;

vardef time(expr c)=
draw fullcircle scaled 8u shifted (5u,5u) wp5 withcolor c;
draw (5u,5u)-- (5u,8u) wp5 withcolor c;
draw (5u,5u)-- (7u,4u) wp5 withcolor c;
enddef;

beginfig(13)
time(iniText);
endfig;

beginfig(14)
time(iniAlt);
endfig;

beginfig(15)
time(iniBack);
endfig;

beginfig(16)
fill (1u,1u)..(2u,4u)..(6u,6u)--(6u,8u)--(9u,5u)--(6u,2u)--(6u,4u)..(3u,3u)..(1u,1u)--cycle withcolor white;

endfig;





end.
