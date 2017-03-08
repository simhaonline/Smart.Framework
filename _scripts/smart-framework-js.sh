#!/bin/sh

# Combine all required Smart.Framework JS scripts from lib/js/framework/src/*.js into one JS file: lib/js/framework/smart-framework.js

THE_SVN_REVISION=`svn info --show-item revision`
THE_FILE=../lib/js/framework/smart-framework.js

echo "// JS Combined: smart-framework.js from lib/js/framework/src/*.js @ rev: svn.${THE_SVN_REVISION}" > ${THE_FILE}
cat ../lib/js/framework/src/browser_check.js >> ${THE_FILE}
cat ../lib/js/framework/src/core_utils.js >> ${THE_FILE}
cat ../lib/js/framework/src/arch_utils.js >> ${THE_FILE}
cat ../lib/js/framework/src/crypt_utils.js >> ${THE_FILE}
cat ../lib/js/framework/src/ifmodalbox.js >> ${THE_FILE}
cat ../lib/js/framework/src/browser_utils.js >> ${THE_FILE}
cat ../lib/js/framework/src/ifmodalbox_scanner.js >> ${THE_FILE}
cat ../lib/js/framework/src/validate_input.js >> ${THE_FILE}
echo "" >> ${THE_FILE}
echo "// #END# JS Combined: smart-framework.js from lib/js/framework/" >> ${THE_FILE}

# END
