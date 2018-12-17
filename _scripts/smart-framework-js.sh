#!/bin/sh

# Combine all required Smart.Framework JS source scripts from lib/js/framework/src/*.js into one package JS file: lib/js/framework/smart-framework.js # r.181217

THE_FILE=../lib/js/framework/smart-framework.js

echo "Regenerating Smart Framework JS Package: ${THE_FILE}"

echo "" > ${THE_FILE}
echo "// # JS Package: smart-framework.js :: #START# :: @ generated from lib/js/framework/src/*.js" >> ${THE_FILE}
echo "// Included Files: browser_check.js ; core_utils.js ; arch_utils.js ; crypt_utils.js ; ifmodalbox.js ; browser_utils.js ; ifmodalbox_scanner.js #" >> ${THE_FILE}
echo "" >> ${THE_FILE}
echo "// ### DO NOT EDIT THIS FILE AS IT WILL BE OVERWRITTEN EACH TIME THE INCLUDED SOURCES WILL CHANGE !!! ###" >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### browser_check.js" >> ${THE_FILE}
cat ../lib/js/framework/src/browser_check.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### core_utils.js" >> ${THE_FILE}
cat ../lib/js/framework/src/core_utils.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### arch_utils.js" >> ${THE_FILE}
cat ../lib/js/framework/src/arch_utils.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### crypt_utils.js" >> ${THE_FILE}
cat ../lib/js/framework/src/crypt_utils.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### ifmodalbox.js" >> ${THE_FILE}
cat ../lib/js/framework/src/ifmodalbox.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### browser_utils.js" >> ${THE_FILE}
cat ../lib/js/framework/src/browser_utils.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### ifmodalbox_scanner.js" >> ${THE_FILE}
cat ../lib/js/framework/src/ifmodalbox_scanner.js >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "// ############### [#]" >> ${THE_FILE}
echo "" >> ${THE_FILE}
echo "// # JS Package: smart-framework.js :: #END#" >> ${THE_FILE}
echo "" >> ${THE_FILE}

echo "[DONE !]"

# END
