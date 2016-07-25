#!/usr/bin/env bash

set -e

# set this to -v to add some verbosity (or use -v switch)
VERBOSE=""

# database update mode
DB_MODE=nothing

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
    echo "          [-d|--db-mode] <mode>  DB install mode:"
    echo "                   original      Save original database (default)"
    echo "                   empty         Init fresh database using dbinit"
    echo "                   merged        Install merged database"
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
    elif [ "$ARG" = "-d" ] || [ "$ARG" = "--db-mode" ]; then
        shift || true
        DB_MODE="$1"
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

function install_db_component() {
    message "  Initializing db component '$2'"
    db_init="./site/engine/dbpatches/dbinit-$2.sql"
    if ! sqlite3 "$1" < "$db_init" ; then
        message_error "Error applying '$db_init' to '$1' database"
        return 1
    fi
}

function install_fresh_db() {
    message "Creating fresh database"
    TEMP_DB="/tmp/temp_$$_$XSM_DB"
    rm -f $TEMP_DB || true
    install_db_component $TEMP_DB "notify"
    install_db_component $TEMP_DB "xsm"
    install_db_component $TEMP_DB "contest"
}

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

if ! ping -c1 dmn.local ; then
    message_error "Please add dmn.local aliast to /etc/hosts"
    exit 1
fi

message "Preparing database. "

sudo mkdir -p  $VERBOSE "$DEST"

DB_SUBDIR="$DEST_CONT/ank"
TEMP_DB="/tmp/xcms_local_installer_$$_$XSM_DB"
DEST_DB="$DEST/$DB_SUBDIR/$XSM_DB"

if [ -r "$DEST_DB" ]; then
    message "Database already exists at [ $DEST_DB ], backing it up."
    sudo cp $VERBOSE "$DEST_DB" "$TEMP_DB"
fi

# double-check that we are not doing something awful:
# ensure that DEST with removed slashes is not empty
# to avoid 'sudo rm -rf /'
DEST_CHECK="` echo -n $DEST | sed -e 's:/::g' `"

if ! [ -z "$DEST_CHECK" ]; then
    message "Cleaning destination directory $DEST [currently nothing]"
    # do not quote whole path with curly braces, it will not expand
    #sudo rm -rf "$DEST/"{doc,"$DEST_CONT",*-design,engine,engine_public}
else
    message_error "Bug in your script!"
    exit 1
fi

message "Copying all stuff to destination. "
sudo cp -a $VERBOSE ./* "$DEST/"

# need to remove destination, otherwise it will be nested
#sudo rm -rf "$DEST/$DEST_CONT"

#sudo cp -a $VERBOSE $CONT_DIR "$DEST/$DEST_CONT"

#VERSION="`tools/publish/version.sh`-local"
VERSION="0.31415926"
message "Set version: $VERSION"
VFILE=`mktemp`
echo "version : $VERSION" > $VFILE
sudo cp $VERBOSE $VFILE $DEST/INFO
rm -f $VFILE

message "Creating database target folder"
sudo mkdir -p $VERBOSE "$(dirname $DEST_DB)"

# process database
if [ "$DB_MODE" == "merged" ] ; then
    message "Installing merged database"
    sudo cp $VERBOSE tools/xsm-merge/new*sqlite3 $DEST_DB
elif [ "$DB_MODE" == "empty" ] ; then
    install_fresh_db
elif [ "$DB_MODE" == "original" ] ; then
    # if saved database exists, restore it,
    # otherwise database stored in original content
    # will be used
    if [ -r "$TEMP_DB" ] ; then
        message "Database backup found, installing..."
        sudo mv $TEMP_DB "$DEST_DB"
    fi
elif [ "$DB_MODE" == "nothing" ] ; then
    message "Told nothing to do with database"
else
    print_usage
fi

message "Cleaning temporary stuff and caches"

sudo touch "$DEST/"{.htaccess,engine.log}

if false && ! [ -r $DEST/settings.php ]; then
    message "Seems that installation is performed for a first time. "
    message "So you should go through XCMS install process. "
    do_prepare_installer $DEST
fi

message "Versioning CSS"

#xcms_version_css "$DEST/engine_public"
#xcms_version_css "$DEST/fizlesh.ru-design"
#xcms_version_css "$DEST/lesh.org.ru-design"

#xsm_clear_notifications

#message "Changing root password to 'root'..."
# change root password to 'root'
#sudo cp -f $VERBOSE ./tools/xcms_console_tools/root_root_user $DEST/$DEST_CONT/auth/usr/root.user

#message "Installing logrotate script"
#sudo cp -f $VERBOSE ./site/xcms.logrotate /etc/logrotate.d/xcms

#message "Creating directory for logs"
#sudo mkdir -p /var/log/xcms/
#sudo chown -R $HTTPD_USER:$HTTPD_USER /var/log/xcms/

sudo chown -R $HTTPD_USER:$HTTPD_USER "$DEST"

message_ok "DMVN installed to http://localhost/$DEST_NAME"