#!/bin/bash

SRC=src
DIST=dist
LIBS=libs
CP="cp -rvu"
MV="mv -v"

function mymkdir()
{
	test -d "$1" || mkdir -p "$1"
}

cd "$(dirname "$0")"

# Syntax check
if [ -n "$(which parallel)" ]
then
  synerr="$(find "$SRC" -name '*.php' -print | parallel --jobs 100% "php -l '{}' 2>&1 1>/dev/null")"
else
  synerr="$(find "$SRC" -name '*.php' -exec php -l '{}' \; 2>&1 1>/dev/null)"
fi
if test -n "$synerr"
then
  echo "$synerr"
  exit 1
fi
php phpmd.phar src ansi phpmd.xml
if [ "$?" -ne "0" ]
then
  exit 1
fi

#main
mymkdir "$DIST"

$CP "$SRC"/{controllers,exceptions,models,trans,utils,views} "$DIST"/
#$CP "$SRC"/*.png "$DIST"/
$CP "$SRC"/*.php  "$SRC"/*.css "$SRC"/.htaccess "$DIST"/
$CP "$SRC/config.php.sample" "$DIST/config.php.sample"

LIBDIR="$LIBS/ace-1.4.12"
mymkdir "$DIST/$LIBDIR"
$CP "$LIBDIR"/* "$DIST/$LIBDIR"

LIBDIR="$LIBS/bootstrap-5.1.0"
mymkdir "$DIST/$LIBDIR/css"
mymkdir "$DIST/$LIBDIR/js"
$CP "$LIBDIR/css"/*.min.css* "$DIST/$LIBDIR/css"
$CP "$LIBDIR/js"/*.min.js* "$DIST/$LIBDIR/js"

LIBDIR="$LIBS/datatables-1.11.0"
mymkdir "$DIST/$LIBDIR"
$CP "$LIBDIR"/*.min.css* "$DIST/$LIBDIR"
$CP "$LIBDIR"/*.min.js* "$DIST/$LIBDIR"

LIBDIR="$LIBS/dropzone-5.7.0"
mymkdir "$DIST/$LIBDIR/"
$CP "$LIBDIR"/*.min.css* "$DIST/$LIBDIR/"
$CP "$LIBDIR"/*.min.js* "$DIST/$LIBDIR/"

LIBDIR="$LIBS/fontawesome-free-5.15.4-web"
mymkdir "$DIST/$LIBDIR/"
$CP "$LIBDIR"/* "$DIST/$LIBDIR/"

LIBDIR="$LIBS/highlight-11.2.0"
mymkdir "$DIST/$LIBDIR/"
mymkdir "$DIST/$LIBDIR/styles"
mymkdir "$DIST/$LIBDIR/languages"
$CP "$LIBDIR"/*.min.js* "$DIST/$LIBDIR/"
$CP "$LIBDIR"/styles/* "$DIST/$LIBDIR/styles/"
$CP "$LIBDIR"/languages/* "$DIST/$LIBDIR/languages/"

LIBDIR="$LIBS/jquery-3.6.0"
mymkdir "$DIST/$LIBDIR/"
$CP "$LIBDIR"/*.min.js* "$DIST/$LIBDIR/"
