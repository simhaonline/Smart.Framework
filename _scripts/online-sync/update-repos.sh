#!/bin/sh

#####
# Update Project Repos Before Deployment: site -> svn ; sf -> git ; sfm -> git
# version: 2018-12-14
# This script is used by AppCodePack
# (c) 2018 unix-world.org
#####

echo "=== Updating Repositories: site ; sf ; sfm ... ==="

if [ ! -d repos/ ]; then
	echo "=== FAIL: repos/ Directory does not exists ==="
	exit 1
fi
cd repos/

if [ ! -d site/ ]; then
	echo "=== FAIL: repos/site/ Directory does not exists ==="
	exit 2
fi
cd site/
echo "##### SVN ### Update Backup4all @@@ Tag: STABLE #####"
svn st
#svn --no-auth-cache --username readonly --password readonly up
svn up
svn st
cd ..
echo ""

if [ ! -d sf/ ]; then
	echo "=== FAIL: repos/sf/ Directory does not exists ==="
	exit 3
fi
cd sf/
echo "##### GIT ### Update Smart.Framework @@@ HEAD #####"
git status
git pull
git status
cd ..
echo ""

if [ ! -d sfm/ ]; then
	echo "=== FAIL: repos/sfm/ Directory does not exists ==="
	exit 3
fi
cd sfm/
echo "##### GIT ### Update Smart.Framework.Modules @@@ HEAD #####"
git status
git pull
git status
cd ..
echo ""

echo "=== Done. ==="
exit 0

# END
