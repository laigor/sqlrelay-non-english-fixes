<html><pre><?php
# Copyright (c) 2001  David Muse
# See the file COPYING for more information.

require_once 'DB.php';

	function checkSuccess($db,$value,$success) {

		if ($value==$success) {
			echo("success ");
		} else {
			echo("failure ");
			print("$value!=$success\n");
			$db->disconnect();
			exit(0);
		}
	}

	$host="localhost";
	$port=9000;
	$socket="/tmp/test.socket";
	$user="test";
	$password="test";
	$dsn = "sqlrelay://$user:$password@$host:$port/";


	# instantiation
	$db = DB::connect($dsn);
	if (DB::isError($db)) {
        	die ($db->getMessage());
	}

	# drop existing table
	$db->query("drop table testtable");

	echo("CREATE TEMPTABLE: \n");
	checkSuccess($db,$db->query("create table testtable (testint integer, testchar char(40), testvarchar varchar(40), testdate date)"),DB_OK);
	echo("\n");

	echo("INSERT: \n");
	checkSuccess($db,$db->query("insert into testtable values (1,'testchar1','testvarchar1','01/01/2001')"),DB_OK);
	echo("\n");

	echo("AFFECTED ROWS: \n");
	checkSuccess($db,$db->affectedRows(),1);
	echo("\n");

	echo(":-DELIMITED BINDS: \n");
	$sth=$db->prepare("insert into testtable values (:var1,:var2,:var3,:var4,:var5)");
	$bindvars=array("1" => 2,
			"2" => "testchar2",
			"3" => "testvarchar2",
			"4" => "2002-01-01");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);

	$bindvars=array("var1" => 3,
			"var2" => "testchar3",
			"var3" => "testvarchar3",
			"var4" => "2003-01-01");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);

	$bindvars=array("1" => 4,
			"2" => "testchar4",
			"3" => "testvarchar4",
			"4" => "2004-01-01");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);

	$bindvars=array("var1" => 5,
			"var2" => "testchar5",
			"var3" => "testvarchar5",
			"var4" => "2005-01-01");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);

	$bindvars=array("1" => 6,
			"2" => "testchar6",
			"3" => "testvarchar6",
			"4" => "2006-01-01");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);

	$bindvars=array("var1" => 7,
			"var2" => "testchar7",
			"var3" => "testvarchar7",
			"var4" => "2007-01-01");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);
	echo("\n");

	echo("?-DELIMITED BINDS: \n");
	$sth=$db->prepare("insert into testtable values (?,?,?,?)");
	$bindvars=array(8,"testchar8","testvarchar8","01/01/2008");
	checkSuccess($db,$db->execute($sth,$bindvars),DB_OK);
	echo("\n");

	# auto execute
	echo("AUTO EXECUTE: \n");
	$bindvars=array("testnumber" => 9,
			"testchar" => "testchar9",
			"testvarchar" => "testvarchar9",
			"testdate" => "01/01/2009");
	checkSuccess($db,$db->autoExecute("testtable",$bindvars,
					DB_AUTOQUERY_INSERT),DB_OK);

	$bindvars=array("testnumber" => NULL,
			"testchar" => NULL,
			"testvarchar" => NULL,
			"testdate" => NULL);
	checkSuccess($db,$db->autoExecute("testtable",$bindvars,
					DB_AUTOQUERY_INSERT),DB_OK);
	echo("\n");

	$res=$db->query("select * from testtable order by testnumber");

	echo("COLUMN COUNT: \n");
	checkSuccess($db,$res->numCols(),5);
	echo("\n");

	echo ("TABLE INFO: \n");
	$tableinfo=$res->tableInfo(DB_TABLEINFO_ORDER);
	checkSuccess($db,$tableinfo['num_fields'],5);
	checkSuccess($db,$tableinfo['order']['TESTNUMBER'],0);
	checkSuccess($db,$tableinfo['order']['TESTCHAR'],1);
	checkSuccess($db,$tableinfo['order']['TESTVARCHAR'],2);
	checkSuccess($db,$tableinfo['order']['TESTDATE'],3);
	checkSuccess($db,$tableinfo['order']['TESTLONG'],4);
	echo("\n");

	echo ("TABLE INFO: \n");
	$tableinfo=$res->tableInfo(DB_TABLEINFO_ORDERTABLE);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTNUMBER'],0);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTCHAR'],1);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTVARCHAR'],2);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTDATE'],3);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTLONG'],4);
	echo("\n");

	echo ("TABLE INFO: \n");
	$tableinfo=$res->tableInfo(DB_TABLEINFO_ORDER|DB_TABLEINFO_ORDERTABLE);
	checkSuccess($db,$tableinfo['num_fields'],5);
	checkSuccess($db,$tableinfo['order']['TESTNUMBER'],0);
	checkSuccess($db,$tableinfo['order']['TESTCHAR'],1);
	checkSuccess($db,$tableinfo['order']['TESTVARCHAR'],2);
	checkSuccess($db,$tableinfo['order']['TESTDATE'],3);
	checkSuccess($db,$tableinfo['order']['TESTLONG'],4);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTNUMBER'],0);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTCHAR'],1);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTVARCHAR'],2);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTDATE'],3);
	checkSuccess($db,$tableinfo['ordertable']['']['TESTLONG'],4);
	echo("\n");
	
	echo("COLUMN NAMES: \n");
	$tableinfo=$res->tableInfo();
	checkSuccess($db,$tableinfo[0]['name'],"TESTNUMBER");
	checkSuccess($db,$tableinfo[1]['name'],"TESTCHAR");
	checkSuccess($db,$tableinfo[2]['name'],"TESTVARCHAR");
	checkSuccess($db,$tableinfo[3]['name'],"TESTDATE");
	checkSuccess($db,$tableinfo[4]['name'],"TESTLONG");
	echo("\n");

	echo("COLUMN TYPES: \n");
	checkSuccess($db,$tableinfo[0]['type'],"NUMBER");
	checkSuccess($db,$tableinfo[1]['type'],"CHAR");
	checkSuccess($db,$tableinfo[2]['type'],"VARCHAR2");
	checkSuccess($db,$tableinfo[3]['type'],"DATE");
	checkSuccess($db,$tableinfo[4]['type'],"LONG");
	echo("\n");

	echo("COLUMN LENGTH: \n");
	checkSuccess($db,$tableinfo[0]['len'],22);
	checkSuccess($db,$tableinfo[1]['len'],40);
	checkSuccess($db,$tableinfo[2]['len'],40);
	checkSuccess($db,$tableinfo[3]['len'],7);
	checkSuccess($db,$tableinfo[4]['len'],0);
	echo("\n");

	echo("ROW COUNT: \n");
	checkSuccess($db,$res->numRows(),10);
	echo("\n");

	echo("FIELDS BY ARRAY: \n");
	$row=$res->fetchRow(DB_FETCHMODE_ORDERED);
	checkSuccess($db,$row[0],"1");
	checkSuccess($db,$row[1],"testchar1                               ");
	checkSuccess($db,$row[2],"testvarchar1");
	checkSuccess($db,$row[3],"2001-01-01");
	echo("\n");
	$row=$res->fetchRow(DB_FETCHMODE_ORDERED,7);
	checkSuccess($db,$row[0],"8");
	checkSuccess($db,$row[1],"testchar8                               ");
	checkSuccess($db,$row[2],"testvarchar8");
	checkSuccess($db,$row[3],"2008-01-01");
	echo("\n");
	$res->free();

	echo("FETCH INTO FIELDS BY ARRAY: \n");
	$res=$db->query("select * from testtable order by testnumber");
	$res->fetchInto($row,DB_FETCHMODE_ORDERED);
	checkSuccess($db,$row[0],"1");
	checkSuccess($db,$row[1],"testchar1                               ");
	checkSuccess($db,$row[2],"testvarchar1");
	checkSuccess($db,$row[3],"2001-01-01");
	echo("\n");
	$res->fetchInto($row,DB_FETCHMODE_ORDERED,7);
	checkSuccess($db,$row[0],"8");
	checkSuccess($db,$row[1],"testchar8                               ");
	checkSuccess($db,$row[2],"testvarchar8");
	checkSuccess($db,$row[3],"2008-01-01");
	echo("\n");
	$res->free();

	echo("SET FETCH MODE FIELDS BY ARRAY: \n");
	$db->setFetchMode(DB_FETCHMODE_ORDERED);
	$res=$db->query("select * from testtable order by testnumber");
	$row=$res->fetchRow();
	checkSuccess($db,$row[0],"1");
	checkSuccess($db,$row[1],"testchar1                               ");
	checkSuccess($db,$row[2],"testvarchar1");
	checkSuccess($db,$row[3],"2001-01-01");
	echo("\n");
	$row=$res->fetchRow();
	checkSuccess($db,$row[0],"2");
	checkSuccess($db,$row[1],"testchar2                               ");
	checkSuccess($db,$row[2],"testvarchar2");
	checkSuccess($db,$row[3],"2002-01-01");
	echo("\n");
	$res->free();

	echo("FIELDS BY ASSOCIATIVE ARRAY: \n");
	$res=$db->query("select * from testtable order by testnumber");
	$row=$res->fetchRow(DB_FETCHMODE_ASSOC);
	checkSuccess($db,$row['TESTNUMBER'],"1");
	checkSuccess($db,$row['TESTCHAR'],"testchar1                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar1");
	checkSuccess($db,$row['TESTDATE'],"2001-01-01");
	echo("\n");
	$row=$res->fetchRow(DB_FETCHMODE_ASSOC,7);
	checkSuccess($db,$row['TESTNUMBER'],"8");
	checkSuccess($db,$row['TESTCHAR'],"testchar8                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar8");
	checkSuccess($db,$row['TESTDATE'],"2008-01-01");
	echo("\n");
	$res->free();

	echo("FETCH INTO FIELDS BY ASSOCIATIVE ARRAY: \n");
	$res=$db->query("select * from testtable order by testnumber");
	$res->fetchInto($row,DB_FETCHMODE_ASSOC);
	checkSuccess($db,$row['TESTNUMBER'],"1");
	checkSuccess($db,$row['TESTCHAR'],"testchar1                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar1");
	checkSuccess($db,$row['TESTDATE'],"2001-01-01");
	echo("\n");
	$res->fetchInto($row,DB_FETCHMODE_ASSOC,7);
	checkSuccess($db,$row['TESTNUMBER'],"8");
	checkSuccess($db,$row['TESTCHAR'],"testchar8                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar8");
	checkSuccess($db,$row['TESTDATE'],"2008-01-01");
	echo("\n");
	$res->free();

	echo("SET FETCH MODE FIELDS BY ASSOCIATIVE ARRAY: \n");
	$db->setFetchMode(DB_FETCHMODE_ASSOC);
	$res=$db->query("select * from testtable order by testnumber");
	$row=$res->fetchRow();
	checkSuccess($db,$row['TESTNUMBER'],"1");
	checkSuccess($db,$row['TESTCHAR'],"testchar1                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar1");
	checkSuccess($db,$row['TESTDATE'],"2001-01-01");
	echo("\n");
	$row=$res->fetchRow();
	checkSuccess($db,$row['TESTNUMBER'],"2");
	checkSuccess($db,$row['TESTCHAR'],"testchar2                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar2");
	checkSuccess($db,$row['TESTDATE'],"2002-01-01");
	echo("\n");
	$res->free();

	echo("FIELDS BY OBJECT: \n");
	$res=$db->query("select * from testtable order by testnumber");
	$row=$res->fetchRow(DB_FETCHMODE_OBJECT);
	checkSuccess($db,$row->TESTNUMBER,"1");
	checkSuccess($db,$row->TESTCHAR,"testchar1                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar1");
	checkSuccess($db,$row->TESTDATE,"2001-01-01");
	echo("\n");
	$row=$res->fetchRow(DB_FETCHMODE_OBJECT,7);
	checkSuccess($db,$row->TESTNUMBER,"8");
	checkSuccess($db,$row->TESTCHAR,"testchar8                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar8");
	checkSuccess($db,$row->TESTDATE,"2008-01-01");
	echo("\n");
	$res->free();

	echo("FETCH INTO FIELDS BY OBJECT: \n");
	$res=$db->query("select * from testtable order by testnumber");
	$res->fetchInto($row,DB_FETCHMODE_OBJECT);
	checkSuccess($db,$row->TESTNUMBER,"1");
	checkSuccess($db,$row->TESTCHAR,"testchar1                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar1");
	checkSuccess($db,$row->TESTDATE,"2001-01-01");
	echo("\n");
	$res->fetchInto($row,DB_FETCHMODE_OBJECT,7);
	checkSuccess($db,$row->TESTNUMBER,"8");
	checkSuccess($db,$row->TESTCHAR,"testchar8                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar8");
	checkSuccess($db,$row->TESTDATE,"2008-01-01");
	echo("\n");
	$res->free();

	echo("SET FETCH MODE FIELDS BY OBJECT: \n");
	$db->setFetchMode(DB_FETCHMODE_OBJECT);
	$res=$db->query("select * from testtable order by testnumber");
	$row=$res->fetchRow();
	checkSuccess($db,$row->TESTNUMBER,"1");
	checkSuccess($db,$row->TESTCHAR,"testchar1                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar1");
	checkSuccess($db,$row->TESTDATE,"2001-01-01");
	echo("\n");
	$row=$res->fetchRow();
	checkSuccess($db,$row->TESTNUMBER,"2");
	checkSuccess($db,$row->TESTCHAR,"testchar2                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar2");
	checkSuccess($db,$row->TESTDATE,"2002-01-01");
	echo("\n");
	$res->free();

	echo("GET ROW: \n");
	$db->setFetchMode(DB_FETCHMODE_ORDERED);
	$row=$db->getRow("select * from testtable order by testnumber");
	checkSuccess($db,$row[0],"1");
	checkSuccess($db,$row[1],"testchar1                               ");
	checkSuccess($db,$row[2],"testvarchar1");
	checkSuccess($db,$row[3],"2001-01-01");
	echo("\n");

	$db->setFetchMode(DB_FETCHMODE_ASSOC);
	$row=$db->getRow("select * from testtable order by testnumber");
	checkSuccess($db,$row['TESTNUMBER'],"1");
	checkSuccess($db,$row['TESTCHAR'],"testchar1                               ");
	checkSuccess($db,$row['TESTVARCHAR'],"testvarchar1");
	checkSuccess($db,$row['TESTDATE'],"2001-01-01");
	echo("\n");

	$db->setFetchMode(DB_FETCHMODE_OBJECT);
	$row=$db->getRow("select * from testtable order by testnumber");
	checkSuccess($db,$row->TESTNUMBER,"1");
	checkSuccess($db,$row->TESTCHAR,"testchar1                               ");
	checkSuccess($db,$row->TESTVARCHAR,"testvarchar1");
	checkSuccess($db,$row->TESTDATE,"2001-01-01");
	echo("\n");
	
	echo("GET COL: \n");
	$col=$db->getCol("select * from testtable order by testnumber",0);
	checkSuccess($db,$col[0],"1");
	checkSuccess($db,$col[1],"2");
	checkSuccess($db,$col[2],"3");
	checkSuccess($db,$col[3],"4");
	checkSuccess($db,$col[4],"5");
	checkSuccess($db,$col[5],"6");
	checkSuccess($db,$col[6],"7");
	checkSuccess($db,$col[7],"8");
	echo("\n");

	echo("GET ALL: \n");
	$db->setFetchMode(DB_FETCHMODE_ORDERED);
	$rows=$db->getAll("select * from testtable order by testnumber",0);
	checkSuccess($db,$rows[0][0],"1");
	checkSuccess($db,$rows[0][1],"testchar1                               ");
	checkSuccess($db,$rows[0][2],"testvarchar1");
	checkSuccess($db,$rows[0][3],"2001-01-01");
	checkSuccess($db,$rows[7][0],"8");
	checkSuccess($db,$rows[7][1],"testchar8                               ");
	checkSuccess($db,$rows[7][2],"testvarchar8");
	checkSuccess($db,$rows[7][3],"2008-01-01");
	echo("\n");

	$db->setFetchMode(DB_FETCHMODE_ASSOC);
	$rows=$db->getAll("select * from testtable order by testnumber",0);
	checkSuccess($db,$rows[0]['TESTNUMBER'],"1");
	checkSuccess($db,$rows[0]['TESTCHAR'],"testchar1                               ");
	checkSuccess($db,$rows[0]['TESTVARCHAR'],"testvarchar1");
	checkSuccess($db,$rows[0]['TESTDATE'],"2001-01-01");
	checkSuccess($db,$rows[7]['TESTNUMBER'],"8");
	checkSuccess($db,$rows[7]['TESTCHAR'],"testchar8                               ");
	checkSuccess($db,$rows[7]['TESTVARCHAR'],"testvarchar8");
	checkSuccess($db,$rows[7]['TESTDATE'],"2008-01-01");
	echo("\n");

	$db->setFetchMode(DB_FETCHMODE_OBJECT);
	$rows=$db->getAll("select * from testtable order by testnumber",0);
	checkSuccess($db,$rows[0]->TESTNUMBER,"1");
	checkSuccess($db,$rows[0]->TESTCHAR,"testchar1                               ");
	checkSuccess($db,$rows[0]->TESTVARCHAR,"testvarchar1");
	checkSuccess($db,$rows[0]->TESTDATE,"2001-01-01");
	checkSuccess($db,$rows[7]->TESTNUMBER,"8");
	checkSuccess($db,$rows[7]->TESTCHAR,"testchar8                               ");
	checkSuccess($db,$rows[7]->TESTVARCHAR,"testvarchar8");
	checkSuccess($db,$rows[7]->TESTDATE,"2008-01-01");
	echo("\n");

	echo("COMMIT AND ROLLBACK: \n");
	$db2 = DB::connect($dsn);
	if (DB::isError($db2)) {
        	die ($db2->getMessage());
	}

	checkSuccess($db2,$db2->getOne("select count(*) from testtable"),0);
	checkSuccess($db,$db->commit(),DB_OK);
	checkSuccess($db2,$db2->getOne("select count(*) from testtable"),10);
	checkSuccess($db,$db->autoCommit(true),DB_OK);
	checkSuccess($db,$db->query("insert into testtable values (10,'testchar10','testvarchar10','01/01/2010')"),DB_OK);
	checkSuccess($db2,$db2->getOne("select count(*) from testtable"),11);
	$db->autoCommit(false);

	$db2->disconnect();
	echo("\n");

	echo("SEQUENCES: \n");
	$db->dropSequence("mysequence");
	checkSuccess($db,$db->nextId("mysequence"),1);
	checkSuccess($db,$db->nextId("mysequence"),2);
	checkSuccess($db,$db->dropSequence("mysequence"),DB_OK);
	checkSuccess($db,$db->createSequence("mysequence"),DB_OK);
	checkSuccess($db,$db->nextId("mysequence"),1);
	checkSuccess($db,$db->nextId("mysequence"),2);
	checkSuccess($db,$db->dropSequence("mysequence"),DB_OK);
	echo("\n");

	echo("?'s INSIDE QUOTES: \n");
	$sth=$db->prepare("select '?',':help',? from dual");
	$res=$db->execute($sth,array(1));
	$row=$res->fetchRow(DB_FETCHMODE_ORDERED);
	checkSuccess($db,$row[0],"?");
	checkSuccess($db,$row[1],":help");
	checkSuccess($db,$row[2],"1");
	echo("\n");

	# drop existing table
	$db->query("drop table testtable");

	$db->disconnect();

?></pre></html>
