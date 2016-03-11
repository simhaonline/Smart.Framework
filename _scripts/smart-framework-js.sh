#!/bin/sh

# Combine all Smart.Framework JS scripts from lib/js/framework/*.js into one JS file: smart-framework.js

THE_DTIME=`date +%Y%m%d.%H%M%S`

echo "// JS Combined: smart-framework.js from lib/js/framework/ @ r.${THE_DTIME}" > ./smart-framework.js
cat ../lib/js/framework/browser_check.js >> ./smart-framework.js
cat ../lib/js/framework/core_utils.js >> ./smart-framework.js
cat ../lib/js/framework/arch_utils.js >> ./smart-framework.js
cat ../lib/js/framework/crypt_utils.js >> ./smart-framework.js
cat ../lib/js/framework/ifmodalbox.js >> ./smart-framework.js
cat ../lib/js/framework/browser_utils.js >> ./smart-framework.js
cat ../lib/js/framework/ifmodalbox_scanner.js >> ./smart-framework.js
cat ../lib/js/framework/validate_input.js >> ./smart-framework.js
echo "" >> ./smart-framework.js
echo "// #END# JS Combined: smart-framework.js from lib/js/framework/" >> ./smart-framework.js

# END
