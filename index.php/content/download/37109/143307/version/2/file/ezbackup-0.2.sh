#!/bin/sh
#
# backing up ezpublish sites
#
# Author: Marco Stipek <m.stipek@link-m.de>
# Verison: 0.2 (release date 2006-07-19)
#
#   Warning: 
#      This script is only tested with ezbublish 3.4.1
#      Maybe it's working with different Versions, but
#      please test. Due to ini changes in ezpublish
#      this script could fail. 
#      It works only with Mysql Databases!
#
#   Description:
#	This script creates every time it is called a new
#	timestamped backup tar.gz file for each site you like 
#	to backup. This file contains a database dump, the 
#	specific site settings (settings/siteaccess/<site>)
#	and the storage (var/<storagedir>)
#
#	To recover, you have to extract the .tar.gz file 
#	within the ezroot directory. The paths are relative!
#
#   Usage:
#	1. Copy this file anywhere you like to run it from
#	2. Edit the following config section 
#	3. chmod 750 ezbackup.sh 
#	4. If you like to run from cron, create e.g. a link in /etc/cron.daily
#
#   Licence: 
#
#   This program is free software; you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation; either version 2 of the License, or
#   (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

############################################################## 
# Configuration
#
# Where ezpublish is living
EZROOT="/www/sites/ezpublish"
# which sites to backup (space delemited list)
SITES="<mysite>"
# where the backup should be written to
BACKUPDIR="/opt/backup"
# Mysql arguments
MYSQLARG="--add-drop-table --lock-tables --complete-insert "
# Debugging on/off (0/1)
DEBUG=0

### END OF CONFIG ###########################################

function DEBUG
{
	[ $DEBUG -ne 0 ] && echo -e "$1"
}

function ERROR
{
	echo -e "\a$1"
	exit -1
}


# Check existence of backup directory
if [ ! -d ${BACKUPDIR} ];
then
	DEBUG "Backup dir does not exist ... creating ${BACKUPDIR}"
	mkdir -p ${BACKUPDIR}
fi

for SITE in $SITES;
do
	DEBUG "Creating Backup for site ${SITE}"
	SITEBACKUP=${BACKUPDIR}/temp/${SITE}
	SITEACCESSDIR=${EZROOT}/settings/siteaccess/${SITE}

	if [ ! -d ${SITEACCESSDIR} ];
	then
		ERROR "!!! configuration for site ${SITE} not found"
	fi

	# Creating Directory for Sitebackup
	if [ ! -d ${SITEBACKUP} ];
	then
		mkdir -p ${SITEBACKUP}
	else
		ERROR "Temporory Directory ${SITEBACKUP} already exists!\nPlease remove first and restart backup! No backup created !!!"
	fi

	DB=$(sed -n -e  "s/^Database=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)
	USER=$(sed -n -e  "s/^User=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)
	PASS=$(sed -n -e  "s/^Password=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)
	HOST=$(sed -n -e  "s/^Server=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)
	DESIGN=$(sed -n -e  "s/^SiteDesign=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)
	VARDIR=$(sed -n -e  "s/^VarDir=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)
	STORAGEDIR=$(sed -n -e  "s/^StorageDir=//p" ${EZROOT}/settings/siteaccess/${SITE}/site.ini.append.php)

	[ ! -z "${USER:-}" ] && MYSQLARG="${MYSQLARG} -u${USER}"
	[ ! -z "${PASS:-}" ] && MYSQLARG="${MYSQLARG} -p${PASS}"
	[ ! -z "${HOST:-}" ] && MYSQLARG="${MYSQLARG} -h${HOST}"

	DEBUG "Dumping Mysql table ${DB} to ${SITEBACKUP}/mysqldata_${SITE}.sql"
	mysqldump ${MYSQLARG} ${DB} >${SITEBACKUP}/mysqldata_for_site_${SITE}.sql

	DEBUG "Copy site settings, design templates and storage to backup"

	# backing up siteaccess folder
	if [ ! -d ${EZROOT}/settings/siteaccess/${SITE} ];
	then
		ERROR  "siteaccess (${EZROOT}/settings/siteaccess/${SITE}) does not exist!"
	else
		mkdir -p ${SITEBACKUP}/settings/siteaccess
		cp -rp  ${EZROOT}/settings/siteaccess/${SITE} ${SITEBACKUP}/settings/siteaccess

	fi

	# backing up design folder
	if [ ! -d ${EZROOT}/design/${DESIGN} ];
	then
		ERROR  "siteaccess (${EZROOT}/settings/siteaccess/${DESIGN}) does not exist!"
	else
		mkdir -p ${SITEBACKUP}/design && cp -rp ${EZROOT}/design/${DESIGN} ${SITEBACKUP}/design
	fi

	# backing up storage folder
	if [ ! -d ${EZROOT}/${VARDIR}/${STORAGEDIR} ];
	then
		ERROR  "storage folder ${EZROOT}/${VARDIR}/${STORAGEDIR} does not exist!"
	else
		mkdir -p ${SITEBACKUP}/${VARDIR}/${STORAGEDIR} && cp -rp ${EZROOT}/${VARDIR}/${STORAGEDIR}/* ${SITEBACKUP}/${VARDIR}/${STORAGEDIR}
	fi

	# Creating tar.gz
	BACKUPFILE="${BACKUPDIR}/${SITE}_$(date +"%Y-%m-%d-%H_%M").tar.gz"

	[ -f $BACKUPFILE ] && ERROR "Backup file already exists: ${BACKUPFILE}\nis there another backup process running? No Backup created!"

	cd ${SITEBACKUP}
	tar --remove-files -czf ${BACKUPDIR}/${SITE}_$(date +"%Y-%m-%d-%H_%M").tar.gz *

	# Cleaning up temporary sitebackup directory
	rm -rf ${SITEBACKUP}
done

# Cleaning up
rm -rf "${BACKUPDIR}/temp"
