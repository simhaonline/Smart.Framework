[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.DataBase: START :: r.20191031

(function() {

	var localTests = {};

	localTests.SQLite 		= [####TEST-DB-SQLITE|bool####];
	localTests.PCache 		= [####TEST-DB-PCACHE|bool####];
	localTests.MongoDB 		= [####TEST-DB-MONGO|bool####];
	localTests.PostgreSQL 	= [####TEST-DB-PGSQL|bool####];
	localTests.MySQL 		= [####TEST-DB-MYSQL|bool####];

	var testsDisabled = 0;
	for(var val in localTests) {
		if(localTests[val] !== true) {
			testsDisabled++;
		} //end if
	} //end for
	smartQUnitDisabledTests += testsDisabled;

	localTests.module = false;
	for(var val in localTests) {
		if(localTests[val] === true) {
			localTests.module = true;
			break;
		} //end if
	} //end for

	if(localTests.module) {

		QUnit.module('DataBase', function(){

			if(localTests.SQLite) {
				QUnit.test('SQLite Test: PHP :: ajax', function(assert) {
					SmartQUnit.runAjaxTest(
						baseScript + '?page=testunit&op=testunit.json-sqlite3-smartgrid',
						'GET',
						'json', // data type
						assert,
						'Test OK: PHP SQLite.',
						function(QAsyncTestDone, testOK, msg) {
							var value;
							if((typeof msg == 'object') && (msg.status) && (typeof msg.status == 'string') && (msg.status = 'OK') && (msg.itemsPerPage) && (msg.itemsPerPage === 25) && (msg.totalRows) && (msg.totalRows >= 25) && (msg.rowsList) && (typeof msg.rowsList == 'object') && (msg.rowsList.length = 25)) {
								value = 'Test OK: PHP SQLite.';
							} else {
								value = 'INVALID TEST CONTENT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
							} //end if else
							assert.equal(
								value, testOK,
								testOK
							);
							QAsyncTestDone();
						} //end function
					);
				});
			} else {
				// skip SQLite Test
			} //end if else

			if(localTests.PCache) {
				QUnit.test('Persistent Cache ([####NAME-DB-PCACHE|js####]) Test: PHP :: ajax', function(assert) {
					SmartQUnit.runAjaxTest(
						baseScript + '?page=samples.testunit&op=testunit.pcache-test',
						'GET',
						'html', // data type
						assert,
						'Test OK: PHP PersistentCache.',
						function(QAsyncTestDone, testOK, msg, testHtmlDiv) {
							if(typeof msg == 'string') {
								testHtmlDiv(assert, QAsyncTestDone, testOK, value, msg, 250);
							} else {
								var value = 'INVALID TEST CONTENT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
								assert.equal(
									value, testOK,
									testOK
								);
								QAsyncTestDone();
							} //end if else
						} //end function
					);
				});
			} else {
				// skip PersistentCache Test
			} //end if else

			if(localTests.MongoDB) {
				QUnit.test('MongoDB Test: PHP :: ajax', function(assert) {
					SmartQUnit.runAjaxTest(
						baseScript + '?page=samples.testunit&op=testunit.mongodb-server-test',
						'GET',
						'html', // data type
						assert,
						'Test OK: PHP MongoDB.',
						function(QAsyncTestDone, testOK, msg, testHtmlDiv) {
							if(typeof msg == 'string') {
								testHtmlDiv(assert, QAsyncTestDone, testOK, value, msg, 250);
							} else {
								var value = 'INVALID TEST CONTENT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
								assert.equal(
									value, testOK,
									testOK
								);
								QAsyncTestDone();
							} //end if else
						} //end function
					);
				});
			} else {
				// skip MongoDB Test
			} //end if else

			if(localTests.PostgreSQL) {
				QUnit.test('PostgreSQL Test: PHP :: ajax', function(assert) {
					SmartQUnit.runAjaxTest(
						baseScript + '?page=samples.testunit&op=testunit.pgsql-server-test',
						'GET',
						'html', // data type
						assert,
						'Test OK: PHP PostgreSQL.',
						function(QAsyncTestDone, testOK, msg, testHtmlDiv) {
							if(typeof msg == 'string') {
								testHtmlDiv(assert, QAsyncTestDone, testOK, value, msg, 250);
							} else {
								var value = 'INVALID TEST CONTENT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
								assert.equal(
									value, testOK,
									testOK
								);
								QAsyncTestDone();
							} //end if else
						} //end function
					);
				});
			} else {
				// skip PostgreSQL Test
			} //end if else

			if(localTests.MySQL) {
				QUnit.test('MySQLi Test: PHP :: ajax', function(assert) {
					SmartQUnit.runAjaxTest(
						baseScript + '?page=samples.testunit&op=testunit.mysql-server-test',
						'GET',
						'html', // data type
						assert,
						'Test OK: PHP MySQLi.',
						function(QAsyncTestDone, testOK, msg, testHtmlDiv) {
							if(typeof msg == 'string') {
								testHtmlDiv(assert, QAsyncTestDone, testOK, value, msg, 250);
							} else {
								var value = 'INVALID TEST CONTENT: ' + String(JSON.stringify(msg).substr(0,100) + '...');
								assert.equal(
									value, testOK,
									testOK
								);
								QAsyncTestDone();
							} //end if else
						} //end function
					);
				});
			} else {
				// skip MySQLi Test
			} //end if else

		});

	} //end if

})();

//===== QUnit Tests.DataBase: #END


// Smart.Framework.Modules Tests (optional)
[@@@@SUB-TEMPLATE:?!modules/mod-dbal-zend/tests/module-test.mtpl.js.inc!@@@@]
[@@@@SUB-TEMPLATE:?!modules/mod-db-orm-redbean/tests/module-test.mtpl.js.inc!@@@@]

// #END
