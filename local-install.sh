#!/usr/bin/env bash

set -e

# set this to -v to add some verbosity (or use -v switch)
VERBOSE=""

# wwwroot owner/group (www or www-data on debian)
HTTPD_USER="apache"

# database file name
SITE_DB="db.sqlite3"

function message() {
    echo -e "[\033[33mINSTALL\x1b[0m] $@"
}

function message_ok() {
    echo -e "[\033[32mINSTALL\x1b[0m] $@"
}

function message_error() {
    echo -e "[\033[31mINSTALL\x1b[0m] $@"
}


function print_usage() {
    echo "Install DMVN to local www root"
    echo "Usage: `basename $0` <options>"
    echo "          [-i|--installer]       Force installer installation"
    echo "          [-v|--verbose]         Verbose mode"
    echo "          [-p|--path] <name>     Change destination to <name>"
    echo "          [-h|--help]            This cool help"
    exit 1
}

# read options
while [ -n "$1" ]; do
    ARG="$1"
    if [ "$ARG" = "-h" ] || [ "$ARG" = "--help" ]; then
        print_usage
    elif [ "$ARG" = "-v" ] || [ "$ARG" = "--verbose" ]; then
        VERBOSE="-v"
    elif [ "$ARG" = "-i" ] || [ "$ARG" = "--installer" ]; then
        PREPARE_INSTALLER="yes"
    elif [ "$ARG" = "-p" ] || [ "$ARG" = "--path" ]; then
        shift || true
        DEST_NAME="$1"
    fi
    shift || true
done

user="$SUDO_USER"
if [ -z "$user" ] ; then
    user="$USER"
fi
message "User detected: $user"

if grep -q Ubuntu /etc/*release ; then
    message "Switching to Ubuntu"
    HTTPD_USER=www-data
fi

# configure rests of unset options
if [ -z "$DEST_NAME" ] ; then
    # default site location
    DEST_NAME="dmvn.mexmat.net"
fi

# folder with content repo
REPO_NAME="content-$DEST_NAME"

# content path
CONT_DIR="../$REPO_NAME/content"

# installed content folder name
DEST_CONT="$DEST_NAME-content"

function xcms_version_css()
{
    css_root_dir="$1"
    if ! [ -d "$css_root_dir" ] ; then
        return 0
    fi
    version="$( cat $DEST/VERSION | sed -e 's/[^0-9.]//g' )"
    message "    Processing '$css_root_dir'..."

    (
        cd "$css_root_dir"
        css_dirs="$( find . -type d -name 'css' )"
        for d in $css_dirs ; do (
            sudo rm -rf "$d/$version"
            cd "$d"
            sudo ln -sf . "$version"
        ) done
    )
}

# site root
DEST="/var/www/vhosts/$DEST_NAME"

# check SQLite3 presence
if ! which sqlite3 > /dev/null; then
    message_error "Please install SQLite v.3 on your machine"
    exit 1
fi

if [ -z "$DEST" ] ; then
    message_error "Destination path cannot be empty"
    exit 1
fi

if false && ! [ -d "$CONT_DIR" ]; then
    message_error "Content directory not found, exiting. "
    message_error "It should be 2 dir-levels above and named '$CONT_DIR'."
    exit 1
fi

if ! ping -q -c1 dmn.local ; then
    message_error "Please add dmn.local aliast to /etc/hosts"
    exit 1
fi

message "Preparing destination directory. "

sudo mkdir -p  $VERBOSE "$DEST"

# double-check that we are not doing something awful:
# ensure that DEST with removed slashes is not empty
# to avoid 'sudo rm -rf /'
DEST_CHECK="` echo -n $DEST | sed -e 's:/::g' `"

if ! [ -z "$DEST_CHECK" ]; then
    message "Cleaning destination directory $DEST [currently nothing]"
    # do not quote whole path with curly braces, it will not expand
else
    message_error "Bug in your script!"
    exit 1
fi

message "Copying all stuff to destination. "
sudo cp -a $VERBOSE ./* "$DEST/"

# once action
#sudo cp -a $VERBOSE .production/dmvn.mexmat.net/* "$DEST/"

#VERSION="`tools/publish/version.sh`-local"
VERSION="0.31415926"
message "Set version: $VERSION"
VFILE=`mktemp`
echo "version : $VERSION" > $VFILE
sudo cp $VERBOSE $VFILE $DEST/INFO
rm -f $VFILE

message "Cleaning temporary stuff and caches"

sudo touch "$DEST/"{.htaccess,engine.log}

if false && ! [ -r $DEST/settings.php ]; then
    message "Seems that installation is performed for a first time. "
    message "So you should go through XCMS install process. "
    do_prepare_installer $DEST
fi

message "Installing logrotate script"
sudo cp -f $VERBOSE ./xcms.logrotate /etc/logrotate.d/xcms

message "Creating directory for logs"
sudo mkdir -p /var/log/xcms/
sudo chown -R $HTTPD_USER:$HTTPD_USER /var/log/xcms/

sudo chown -R $HTTPD_USER:$HTTPD_USER "$DEST"

message_ok "DMVN installed to http://localhost/$DEST_NAME"
