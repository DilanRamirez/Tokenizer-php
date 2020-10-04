# Tokenizer-php

You are given a java program that reads a file from a URL that contains
a program in a simple made-up programming language with no loops..
A grammar for the language is as follows:
* <input> ::= <section>*
* <section> ::= ’[’ <statement>* ’]’
* <statement> ::= <assignment> | <switch> | <output>
* <assignment> ::= ID ’=’ (INT | ID)
* <output> ::= ’output’ (INT | ID | STRING)
* <switch> ::= ’switch’ ID ’{’ <case>* [ ’default’ ’:’ <statement>* ] ’}’
* <case> ::= ’case’ INT ’:’ <statement>* ’break’
* ID ::= [a-zA-Z_]+
* INT ::= [0-9]+
* STRING starts and ends with double quotes ("), " can by escaped: (\")
