#!/bin/bash

# This script creates symlinks from the local GIT repo into your EE install. It also copies some of the extension icons.

dirname=`dirname "$0"`

echo ""
echo "Creating symlinks for LG Addon Updater"
echo "--------------------------------------"
echo ""
echo "Enter the full path to your ExpressionEngine install without a trailing slash [ENTER]:"
read ee_path
echo "Enter your ee system folder name [ENTER]:"
read ee_system_folder

ln -s "$dirname"/system/extensions/ext.lg_addon_updater_ext.php "$ee_path"/"$ee_system_folder"/extensions/ext.lg_addon_updater_ext.php
ln -s "$dirname"/system/language/english/lang.lg_addon_updater_ext.php "$ee_path"/"$ee_system_folder"/language/english/lang.lg_addon_updater_ext.php