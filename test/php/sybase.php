<html><pre><?php
# Copyright (c) 2001  David Muse
# See the file COPYING for more information.

dl("sql_relay.so");

function checkSuccess($value,$success) {

	if ($value==$success) {
		echo("success ");
	} else {
		echo("failure ");
		sqlrcur_free($cur);
		sqlrcon_free($con);
		exit(0);
	}
}

	$host="localhost";
	$port=9000;
	$socket="/tmp/test.socket";
	$user="test";
	$password="test";

	$bindvars=array("1","2","3","4","5","6","7","8","9",
				"10","11","12","13");
	$bindvals=array("4","4","4","4.4","4.4","4.4","4.4",
				"4.00","4.00",
				"01-Jan-2004 04:00:00",
				"01-Jan-2004 04:00:00",
				"testchar4","testvarchar4");
	$arraybindvars=array("var1","var2","var3","var4","var5","var6",
				"var7","var8","var9","var10","var11","var12",
				"var13");
	$arraybindvals=array("7","7","7","7.7","7.7","7.7","7.7",
				"7.00","7.00",
				"01-Jan-2007 07:00:00",
				"01-Jan-2007 07:00:00",
				"testchar7","testvarchar7");


	# instantiation
	$con=sqlrcon_alloc($host,$port,$socket,$user,$password,0,1);
	$cur=sqlrcur_alloc($con);

	# get database type
	echo("IDENTIFY: \n");
	checkSuccess(sqlrcon_identify($con),"sybase");
	echo("\n");

	# ping
	echo("PING: \n");
	checkSuccess(sqlrcon_ping($con),1);
	echo("\n");

	# drop existing table
	sqlrcur_sendQuery($cur,"drop table testtable");

	echo("CREATE TEMPTABLE: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"create table testtable (testint int, testsmallint smallint, testtinyint tinyint, testreal real, testfloat float, testdecimal decimal(4,1), testnumeric numeric(4,1), testmoney money, testsmallmoney smallmoney, testdatetime datetime, testsmalldatetime smalldatetime, testchar char(40), testvarchar varchar(40), testbit bit)"),1);
	echo("\n");

	echo("BEGIN TRANSACTION: \n");
	#checkSuccess(sqlrcur_sendQuery($cur,"begin tran"),1);
	echo("\n");

	echo("INSERT: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (1,1,1,1.1,1.1,1.1,1.1,1.00,1.00,'01-Jan-2001 01:00:00','01-Jan-2001 01:00:00','testchar1','testvarchar1',1)"),1);
	echo("\n");

	echo("AFFECTED ROWS: \n");
	checkSuccess(sqlrcur_affectedRows($cur),1);
	echo("\n");

	echo("BIND BY POSITION: \n");
	sqlrcur_prepareQuery($cur,"insert into testtable values (@var1,@var2,@var3,@var4,@var5,@var6,@var7,@var8,@var9,@var10,@var11,@var12,@var13,@var14)");
	checkSuccess(sqlrcur_countBindVariables($cur),14);
	sqlrcur_inputBind($cur,"1",2);
	sqlrcur_inputBind($cur,"2",2);
	sqlrcur_inputBind($cur,"3",2);
	sqlrcur_inputBind($cur,"4",2.2,2,1);
	sqlrcur_inputBind($cur,"5",2.2,2,1);
	sqlrcur_inputBind($cur,"6",2.2,2,1);
	sqlrcur_inputBind($cur,"7",2.2,2,1);
	sqlrcur_inputBind($cur,"8",2.00,3,2);
	sqlrcur_inputBind($cur,"9",2.00,3,2);
	sqlrcur_inputBind($cur,"10","01-Jan-2002 02:00:00");
	sqlrcur_inputBind($cur,"11","01-Jan-2002 02:00:00");
	sqlrcur_inputBind($cur,"12","testchar2");
	sqlrcur_inputBind($cur,"13","testvarchar2");
	sqlrcur_inputBind($cur,"14",1);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	sqlrcur_clearBinds($cur);
	sqlrcur_inputBind($cur,"1",3);
	sqlrcur_inputBind($cur,"2",3);
	sqlrcur_inputBind($cur,"3",3);
	sqlrcur_inputBind($cur,"4",3.3,2,1);
	sqlrcur_inputBind($cur,"5",3.3,2,1);
	sqlrcur_inputBind($cur,"6",3.3,2,1);
	sqlrcur_inputBind($cur,"7",3.3,2,1);
	sqlrcur_inputBind($cur,"8",3.00,3,2);
	sqlrcur_inputBind($cur,"9",3.00,3,2);
	sqlrcur_inputBind($cur,"10","01-Jan-2003 03:00:00");
	sqlrcur_inputBind($cur,"11","01-Jan-2003 03:00:00");
	sqlrcur_inputBind($cur,"12","testchar3");
	sqlrcur_inputBind($cur,"13","testvarchar3");
	sqlrcur_inputBind($cur,"14",1);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("ARRAY OF BINDS BY POSITION: \n");
	sqlrcur_clearBinds($cur);
	$bindvars=array("1","2","3","4","5","6","7","8","9","10","11","12","13","14");
	$bindvals=array(4,4,4,4.4,4.4,4.4,4.4,4.00,4.00,"01-Jan-2004 04:00:00","01-Jan-2004 04:00:00","testchar4","testvarchar4",1);
	$precs=array(0,0,0,2,2,2,2,3,3,0,0,0,0,0);
	$scales=array(0,0,0,1,1,1,1,2,2,0,0,0,0,0);
	sqlrcur_inputBinds($cur,$bindvars,$bindvals,$precs,$scales);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("BIND BY NAME: \n");
	sqlrcur_clearBinds($cur);
	sqlrcur_inputBind($cur,"var1",5);
	sqlrcur_inputBind($cur,"var2",5);
	sqlrcur_inputBind($cur,"var3",5);
	sqlrcur_inputBind($cur,"var4",5.5,2,1);
	sqlrcur_inputBind($cur,"var5",5.5,2,1);
	sqlrcur_inputBind($cur,"var6",5.5,2,1);
	sqlrcur_inputBind($cur,"var7",5.5,2,1);
	sqlrcur_inputBind($cur,"var8",5.00,3,2);
	sqlrcur_inputBind($cur,"var9",5.00,3,2);
	sqlrcur_inputBind($cur,"var10","01-Jan-2005 05:00:00");
	sqlrcur_inputBind($cur,"var11","01-Jan-2005 05:00:00");
	sqlrcur_inputBind($cur,"var12","testchar5");
	sqlrcur_inputBind($cur,"var13","testvarchar5");
	sqlrcur_inputBind($cur,"var14",1);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	sqlrcur_clearBinds($cur);
	sqlrcur_inputBind($cur,"var1",6);
	sqlrcur_inputBind($cur,"var2",6);
	sqlrcur_inputBind($cur,"var3",6);
	sqlrcur_inputBind($cur,"var4",6.6,2,1);
	sqlrcur_inputBind($cur,"var5",6.6,2,1);
	sqlrcur_inputBind($cur,"var6",6.6,2,1);
	sqlrcur_inputBind($cur,"var7",6.6,2,1);
	sqlrcur_inputBind($cur,"var8",6.00,3,2);
	sqlrcur_inputBind($cur,"var9",6.00,3,2);
	sqlrcur_inputBind($cur,"var10","01-Jan-2006 06:00:00");
	sqlrcur_inputBind($cur,"var11","01-Jan-2006 06:00:00");
	sqlrcur_inputBind($cur,"var12","testchar6");
	sqlrcur_inputBind($cur,"var13","testvarchar6");
	sqlrcur_inputBind($cur,"var14",1);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("ARRAY OF BINDS BY NAME: \n");
	sqlrcur_clearBinds($cur);
	$bindvars=array("var1","var2","var3","var4","var5","var6","var7","var8","var9","var10","var11","var12","var13","var14");
	$bindvals=array(7,7,7,7.7,7.7,7.7,7.7,7.00,7.00,"01-Jan-2007 07:00:00","01-Jan-2007 07:00:00","testchar7","testvarchar7",1);
	$precs=array(0,0,0,2,2,2,2,3,3,0,0,0,0);
	$scales=array(0,0,0,1,1,1,1,2,2,0,0,0,0);
	sqlrcur_inputBinds($cur,$bindvars,$bindvals,$precs,$scales);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("BIND BY NAME WITH VALIDATION: \n");
	sqlrcur_clearBinds($cur);
	sqlrcur_inputBind($cur,"var1",8);
	sqlrcur_inputBind($cur,"var2",8);
	sqlrcur_inputBind($cur,"var3",8);
	sqlrcur_inputBind($cur,"var4",8.8,2,1);
	sqlrcur_inputBind($cur,"var5",8.8,2,1);
	sqlrcur_inputBind($cur,"var6",8.8,2,1);
	sqlrcur_inputBind($cur,"var7",8.8,2,1);
	sqlrcur_inputBind($cur,"var8",8.00,3,2);
	sqlrcur_inputBind($cur,"var9",8.00,3,2);
	sqlrcur_inputBind($cur,"var10","01-Jan-2008 08:00:00");
	sqlrcur_inputBind($cur,"var11","01-Jan-2008 08:00:00");
	sqlrcur_inputBind($cur,"var12","testchar8");
	sqlrcur_inputBind($cur,"var13","testvarchar8");
	sqlrcur_inputBind($cur,"var14",1);
	sqlrcur_inputBind($cur,"var15","junkvalue");
	sqlrcur_validateBinds($cur);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("SELECT: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	echo("\n");

	echo("COLUMN COUNT: \n");
	checkSuccess(sqlrcur_colCount($cur),14);
	echo("\n");

	echo("COLUMN NAMES: \n");
	checkSuccess(sqlrcur_getColumnName($cur,0),"testint");
	checkSuccess(sqlrcur_getColumnName($cur,1),"testsmallint");
	checkSuccess(sqlrcur_getColumnName($cur,2),"testtinyint");
	checkSuccess(sqlrcur_getColumnName($cur,3),"testreal");
	checkSuccess(sqlrcur_getColumnName($cur,4),"testfloat");
	checkSuccess(sqlrcur_getColumnName($cur,5),"testdecimal");
	checkSuccess(sqlrcur_getColumnName($cur,6),"testnumeric");
	checkSuccess(sqlrcur_getColumnName($cur,7),"testmoney");
	checkSuccess(sqlrcur_getColumnName($cur,8),"testsmallmoney");
	checkSuccess(sqlrcur_getColumnName($cur,9),"testdatetime");
	checkSuccess(sqlrcur_getColumnName($cur,10),"testsmalldatetime");
	checkSuccess(sqlrcur_getColumnName($cur,11),"testchar");
	checkSuccess(sqlrcur_getColumnName($cur,12),"testvarchar");
	checkSuccess(sqlrcur_getColumnName($cur,13),"testbit");
	$cols=sqlrcur_getColumnNames($cur);
	checkSuccess($cols[0],"testint");
	checkSuccess($cols[1],"testsmallint");
	checkSuccess($cols[2],"testtinyint");
	checkSuccess($cols[3],"testreal");
	checkSuccess($cols[4],"testfloat");
	checkSuccess($cols[5],"testdecimal");
	checkSuccess($cols[6],"testnumeric");
	checkSuccess($cols[7],"testmoney");
	checkSuccess($cols[8],"testsmallmoney");
	checkSuccess($cols[9],"testdatetime");
	checkSuccess($cols[10],"testsmalldatetime");
	checkSuccess($cols[11],"testchar");
	checkSuccess($cols[12],"testvarchar");
	checkSuccess($cols[13],"testbit");
	echo("\n");

	echo("COLUMN TYPES: \n");
	checkSuccess(sqlrcur_getColumnType($cur,0),"INT");
	checkSuccess(sqlrcur_getColumnType($cur,"testint"),"INT");
	checkSuccess(sqlrcur_getColumnType($cur,1),"SMALLINT");
	checkSuccess(sqlrcur_getColumnType($cur,"testsmallint"),"SMALLINT");
	checkSuccess(sqlrcur_getColumnType($cur,2),"TINYINT");
	checkSuccess(sqlrcur_getColumnType($cur,"testtinyint"),"TINYINT");
	checkSuccess(sqlrcur_getColumnType($cur,3),"REAL");
	checkSuccess(sqlrcur_getColumnType($cur,"testreal"),"REAL");
	checkSuccess(sqlrcur_getColumnType($cur,4),"FLOAT");
	checkSuccess(sqlrcur_getColumnType($cur,"testfloat"),"FLOAT");
	checkSuccess(sqlrcur_getColumnType($cur,5),"DECIMAL");
	checkSuccess(sqlrcur_getColumnType($cur,"testdecimal"),"DECIMAL");
	checkSuccess(sqlrcur_getColumnType($cur,6),"NUMERIC");
	checkSuccess(sqlrcur_getColumnType($cur,"testnumeric"),"NUMERIC");
	checkSuccess(sqlrcur_getColumnType($cur,7),"MONEY");
	checkSuccess(sqlrcur_getColumnType($cur,"testmoney"),"MONEY");
	checkSuccess(sqlrcur_getColumnType($cur,8),"SMALLMONEY");
	checkSuccess(sqlrcur_getColumnType($cur,"testsmallmoney"),"SMALLMONEY");
	checkSuccess(sqlrcur_getColumnType($cur,9),"DATETIME");
	checkSuccess(sqlrcur_getColumnType($cur,"testdatetime"),"DATETIME");
	checkSuccess(sqlrcur_getColumnType($cur,10),"SMALLDATETIME");
	checkSuccess(sqlrcur_getColumnType($cur,"testsmalldatetime"),"SMALLDATETIME");
	checkSuccess(sqlrcur_getColumnType($cur,11),"LONGCHAR");
	checkSuccess(sqlrcur_getColumnType($cur,"testchar"),"LONGCHAR");
	checkSuccess(sqlrcur_getColumnType($cur,12),"LONGCHAR");
	checkSuccess(sqlrcur_getColumnType($cur,"testvarchar"),"LONGCHAR");
	checkSuccess(sqlrcur_getColumnType($cur,13),"BIT");
	checkSuccess(sqlrcur_getColumnType($cur,"testbit"),"BIT");
	echo("\n");

	echo("COLUMN LENGTH: \n");
	checkSuccess(sqlrcur_getColumnLength($cur,0),4);
	checkSuccess(sqlrcur_getColumnLength($cur,"testint"),4);
	checkSuccess(sqlrcur_getColumnLength($cur,1),2);
	checkSuccess(sqlrcur_getColumnLength($cur,"testsmallint"),2);
	checkSuccess(sqlrcur_getColumnLength($cur,2),1);
	checkSuccess(sqlrcur_getColumnLength($cur,"testtinyint"),1);
	checkSuccess(sqlrcur_getColumnLength($cur,3),4);
	checkSuccess(sqlrcur_getColumnLength($cur,"testreal"),4);
	checkSuccess(sqlrcur_getColumnLength($cur,4),8);
	checkSuccess(sqlrcur_getColumnLength($cur,"testfloat"),8);
	checkSuccess(sqlrcur_getColumnLength($cur,5),35);
	checkSuccess(sqlrcur_getColumnLength($cur,"testdecimal"),35);
	checkSuccess(sqlrcur_getColumnLength($cur,6),35);
	checkSuccess(sqlrcur_getColumnLength($cur,"testnumeric"),35);
	checkSuccess(sqlrcur_getColumnLength($cur,7),8);
	checkSuccess(sqlrcur_getColumnLength($cur,"testmoney"),8);
	checkSuccess(sqlrcur_getColumnLength($cur,8),4);
	checkSuccess(sqlrcur_getColumnLength($cur,"testsmallmoney"),4);
	checkSuccess(sqlrcur_getColumnLength($cur,9),8);
	checkSuccess(sqlrcur_getColumnLength($cur,"testdatetime"),8);
	checkSuccess(sqlrcur_getColumnLength($cur,10),4);
	checkSuccess(sqlrcur_getColumnLength($cur,"testsmalldatetime"),4);
	checkSuccess(sqlrcur_getColumnLength($cur,11),80);
	checkSuccess(sqlrcur_getColumnLength($cur,"testchar"),80);
	checkSuccess(sqlrcur_getColumnLength($cur,12),80);
	checkSuccess(sqlrcur_getColumnLength($cur,"testvarchar"),80);
	checkSuccess(sqlrcur_getColumnLength($cur,13),1);
	checkSuccess(sqlrcur_getColumnLength($cur,"testbit"),1);
	echo("\n");

	echo("LONGEST COLUMN: \n");
	checkSuccess(sqlrcur_getLongest($cur,0),1);
	checkSuccess(sqlrcur_getLongest($cur,"testint"),1);
	checkSuccess(sqlrcur_getLongest($cur,1),1);
	checkSuccess(sqlrcur_getLongest($cur,"testsmallint"),1);
	checkSuccess(sqlrcur_getLongest($cur,2),1);
	checkSuccess(sqlrcur_getLongest($cur,"testtinyint"),1);
	checkSuccess(sqlrcur_getLongest($cur,3),18);
	checkSuccess(sqlrcur_getLongest($cur,"testreal"),18);
	checkSuccess(sqlrcur_getLongest($cur,4),18);
	checkSuccess(sqlrcur_getLongest($cur,"testfloat"),18);
	checkSuccess(sqlrcur_getLongest($cur,5),3);
	checkSuccess(sqlrcur_getLongest($cur,"testdecimal"),3);
	checkSuccess(sqlrcur_getLongest($cur,6),3);
	checkSuccess(sqlrcur_getLongest($cur,"testnumeric"),3);
	checkSuccess(sqlrcur_getLongest($cur,7),4);
	checkSuccess(sqlrcur_getLongest($cur,"testmoney"),4);
	checkSuccess(sqlrcur_getLongest($cur,8),4);
	checkSuccess(sqlrcur_getLongest($cur,"testsmallmoney"),4);
	checkSuccess(sqlrcur_getLongest($cur,9),19);
	checkSuccess(sqlrcur_getLongest($cur,"testdatetime"),19);
	checkSuccess(sqlrcur_getLongest($cur,10),19);
	checkSuccess(sqlrcur_getLongest($cur,"testsmalldatetime"),19);
	checkSuccess(sqlrcur_getLongest($cur,11),40);
	checkSuccess(sqlrcur_getLongest($cur,"testchar"),40);
	checkSuccess(sqlrcur_getLongest($cur,12),12);
	checkSuccess(sqlrcur_getLongest($cur,"testvarchar"),12);
	checkSuccess(sqlrcur_getLongest($cur,13),1);
	checkSuccess(sqlrcur_getLongest($cur,"testbit"),1);
	echo("\n");

	echo("ROW COUNT: \n");
	checkSuccess(sqlrcur_rowCount($cur),8);
	echo("\n");

	echo("TOTAL ROWS: \n");
	checkSuccess(sqlrcur_totalRows($cur),0);
	echo("\n");

	echo("FIRST ROW INDEX: \n");
	checkSuccess(sqlrcur_firstRowIndex($cur),0);
	echo("\n");

	echo("END OF RESULT SET: \n");
	checkSuccess(sqlrcur_endOfResultSet($cur),1);
	echo("\n");

	echo("FIELDS BY INDEX: \n");
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,0,1),"1");
	checkSuccess(sqlrcur_getField($cur,0,2),"1");
	#checkSuccess(sqlrcur_getField($cur,0,3),"1.1");
	#checkSuccess(sqlrcur_getField($cur,0,4),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,5),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,6),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,7),"1.00");
	checkSuccess(sqlrcur_getField($cur,0,8),"1.00");
	checkSuccess(sqlrcur_getField($cur,0,9),"Jan  1 2001  1:00AM");
	checkSuccess(sqlrcur_getField($cur,0,10),"Jan  1 2001  1:00AM");
	checkSuccess(sqlrcur_getField($cur,0,11),"testchar1                               ");
	checkSuccess(sqlrcur_getField($cur,0,12),"testvarchar1");
	checkSuccess(sqlrcur_getField($cur,0,13),"1");
	echo("\n");
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	checkSuccess(sqlrcur_getField($cur,7,1),"8");
	checkSuccess(sqlrcur_getField($cur,7,2),"8");
	#checkSuccess(sqlrcur_getField($cur,7,3),"8.8");
	#checkSuccess(sqlrcur_getField($cur,7,4),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,5),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,6),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,7),"8.00");
	checkSuccess(sqlrcur_getField($cur,7,8),"8.00");
	checkSuccess(sqlrcur_getField($cur,7,9),"Jan  1 2008  8:00AM");
	checkSuccess(sqlrcur_getField($cur,7,10),"Jan  1 2008  8:00AM");
	checkSuccess(sqlrcur_getField($cur,7,11),"testchar8                               ");
	checkSuccess(sqlrcur_getField($cur,7,12),"testvarchar8");
	checkSuccess(sqlrcur_getField($cur,7,13),"1");
	echo("\n");

	echo("FIELD LENGTHS BY INDEX: \n");
	checkSuccess(sqlrcur_getFieldLength($cur,0,0),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,1),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,2),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,3),18);
	checkSuccess(sqlrcur_getFieldLength($cur,0,4),18);
	checkSuccess(sqlrcur_getFieldLength($cur,0,5),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,6),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,7),4);
	checkSuccess(sqlrcur_getFieldLength($cur,0,8),4);
	checkSuccess(sqlrcur_getFieldLength($cur,0,9),19);
	checkSuccess(sqlrcur_getFieldLength($cur,0,10),19);
	checkSuccess(sqlrcur_getFieldLength($cur,0,11),40);
	checkSuccess(sqlrcur_getFieldLength($cur,0,12),12);
	checkSuccess(sqlrcur_getFieldLength($cur,0,13),1);
	echo("\n");
	checkSuccess(sqlrcur_getFieldLength($cur,7,0),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,1),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,2),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,3),18);
	checkSuccess(sqlrcur_getFieldLength($cur,7,4),18);
	checkSuccess(sqlrcur_getFieldLength($cur,7,5),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,6),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,7),4);
	checkSuccess(sqlrcur_getFieldLength($cur,7,8),4);
	checkSuccess(sqlrcur_getFieldLength($cur,7,9),19);
	checkSuccess(sqlrcur_getFieldLength($cur,7,10),19);
	checkSuccess(sqlrcur_getFieldLength($cur,7,11),40);
	checkSuccess(sqlrcur_getFieldLength($cur,7,12),12);
	checkSuccess(sqlrcur_getFieldLength($cur,7,13),1);
	echo("\n");

	echo("FIELDS BY NAME: \n");
	checkSuccess(sqlrcur_getField($cur,0,"testint"),"1");
	checkSuccess(sqlrcur_getField($cur,0,"testsmallint"),"1");
	checkSuccess(sqlrcur_getField($cur,0,"testtinyint"),"1");
	#checkSuccess(sqlrcur_getField($cur,0,"testreal"),"1.1");
	#checkSuccess(sqlrcur_getField($cur,0,"testfloat"),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,"testdecimal"),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,"testnumeric"),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,"testmoney"),"1.00");
	checkSuccess(sqlrcur_getField($cur,0,"testsmallmoney"),"1.00");
	checkSuccess(sqlrcur_getField($cur,0,"testdatetime"),"Jan  1 2001  1:00AM");
	checkSuccess(sqlrcur_getField($cur,0,"testsmalldatetime"),"Jan  1 2001  1:00AM");
	checkSuccess(sqlrcur_getField($cur,0,"testchar"),"testchar1                               ");
	checkSuccess(sqlrcur_getField($cur,0,"testvarchar"),"testvarchar1");
	checkSuccess(sqlrcur_getField($cur,0,"testbit"),"1");
	echo("\n");
	checkSuccess(sqlrcur_getField($cur,7,"testint"),"8");
	checkSuccess(sqlrcur_getField($cur,7,"testsmallint"),"8");
	checkSuccess(sqlrcur_getField($cur,7,"testtinyint"),"8");
	#checkSuccess(sqlrcur_getField($cur,7,"testreal"),"8.8");
	#checkSuccess(sqlrcur_getField($cur,7,"testfloat"),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,"testdecimal"),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,"testnumeric"),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,"testmoney"),"8.00");
	checkSuccess(sqlrcur_getField($cur,7,"testsmallmoney"),"8.00");
	checkSuccess(sqlrcur_getField($cur,7,"testdatetime"),"Jan  1 2008  8:00AM");
	checkSuccess(sqlrcur_getField($cur,7,"testsmalldatetime"),"Jan  1 2008  8:00AM");
	checkSuccess(sqlrcur_getField($cur,7,"testchar"),"testchar8                               ");
	checkSuccess(sqlrcur_getField($cur,7,"testvarchar"),"testvarchar8");
	checkSuccess(sqlrcur_getField($cur,7,"testbit"),"1");
	echo("\n");

	echo("FIELD LENGTHS BY NAME: \n");
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testsmallint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testtinyint"),1);
	#checkSuccess(sqlrcur_getFieldLength($cur,0,"testreal"),3);
	#checkSuccess(sqlrcur_getFieldLength($cur,0,"testfloat"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testdecimal"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testnumeric"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testmoney"),4);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testsmallmoney"),4);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testdatetime"),19);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testsmalldatetime"),19);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testchar"),40);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testvarchar"),12);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testbit"),1);
	echo("\n");
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testsmallint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testtinyint"),1);
	#checkSuccess(sqlrcur_getFieldLength($cur,7,"testreal"),3);
	#checkSuccess(sqlrcur_getFieldLength($cur,7,"testfloat"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testdecimal"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testnumeric"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testmoney"),4);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testsmallmoney"),4);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testdatetime"),19);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testsmalldatetime"),19);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testchar"),40);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testvarchar"),12);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testbit"),1);
	echo("\n");

	echo("FIELDS BY ARRAY: \n");
	$fields=sqlrcur_getRow($cur,0);
	checkSuccess($fields[0],"1");
	checkSuccess($fields[1],"1");
	checkSuccess($fields[2],"1");
	#checkSuccess($fields[3],"1.1");
	#checkSuccess($fields[4],"1.1");
	checkSuccess($fields[5],"1.1");
	checkSuccess($fields[6],"1.1");
	checkSuccess($fields[7],"1.00");
	checkSuccess($fields[8],"1.00");
	checkSuccess($fields[9],"Jan  1 2001  1:00AM");
	checkSuccess($fields[10],"Jan  1 2001  1:00AM");
	checkSuccess($fields[11],"testchar1                               ");
	checkSuccess($fields[12],"testvarchar1");
	checkSuccess($fields[13],"1");
	echo("\n");

	echo("FIELD LENGTHS BY ARRAY: \n");
	$fieldlens=sqlrcur_getRowLengths($cur,0);
	checkSuccess($fieldlens[0],1);
	checkSuccess($fieldlens[1],1);
	checkSuccess($fieldlens[2],1);
	#checkSuccess($fieldlens[3],3);
	#checkSuccess($fieldlens[4],3);
	checkSuccess($fieldlens[5],3);
	checkSuccess($fieldlens[6],3);
	checkSuccess($fieldlens[7],4);
	checkSuccess($fieldlens[8],4);
	checkSuccess($fieldlens[9],19);
	checkSuccess($fieldlens[10],19);
	checkSuccess($fieldlens[11],40);
	checkSuccess($fieldlens[12],12);
	checkSuccess($fieldlens[13],1);
	echo("\n");

	echo("FIELDS BY ASSOCIATIVE ARRAY: \n");
	$fields=sqlrcur_getRowAssoc($cur,0);
	checkSuccess($fields["testint"],"1");
	checkSuccess($fields["testsmallint"],"1");
	checkSuccess($fields["testtinyint"],"1");
	#checkSuccess($fields["testreal"],"1.1");
	#checkSuccess($fields["testfloat"],"1.1");
	checkSuccess($fields["testdecimal"],"1.1");
	checkSuccess($fields["testnumeric"],"1.1");
	checkSuccess($fields["testmoney"],"1.00");
	checkSuccess($fields["testsmallmoney"],"1.00");
	checkSuccess($fields["testdatetime"],"Jan  1 2001  1:00AM");
	checkSuccess($fields["testsmalldatetime"],"Jan  1 2001  1:00AM");
	checkSuccess($fields["testchar"],"testchar1                               ");
	checkSuccess($fields["testvarchar"],"testvarchar1");
	checkSuccess($fields["testbit"],"1");
	echo("\n");
	$fields=sqlrcur_getRowAssoc($cur,7);
	checkSuccess($fields["testint"],"8");
	checkSuccess($fields["testsmallint"],"8");
	checkSuccess($fields["testtinyint"],"8");
	#checkSuccess($fields["testreal"],"8.8");
	#checkSuccess($fields["testfloat"],"8.8");
	checkSuccess($fields["testdecimal"],"8.8");
	checkSuccess($fields["testnumeric"],"8.8");
	checkSuccess($fields["testmoney"],"8.00");
	checkSuccess($fields["testsmallmoney"],"8.00");
	checkSuccess($fields["testdatetime"],"Jan  1 2008  8:00AM");
	checkSuccess($fields["testsmalldatetime"],"Jan  1 2008  8:00AM");
	checkSuccess($fields["testchar"],"testchar8                               ");
	checkSuccess($fields["testvarchar"],"testvarchar8");
	checkSuccess($fields["testbit"],"1");
	echo("\n");

	echo("FIELD LENGTHS BY ASSOCIATIVE ARRAY: \n");
	$fieldlengths=sqlrcur_getRowLengthsAssoc($cur,0);
	checkSuccess($fieldlengths["testint"],1);
	checkSuccess($fieldlengths["testsmallint"],1);
	checkSuccess($fieldlengths["testtinyint"],1);
	#checkSuccess($fieldlengths["testreal"],3);
	#checkSuccess($fieldlengths["testfloat"],3);
	checkSuccess($fieldlengths["testdecimal"],3);
	checkSuccess($fieldlengths["testnumeric"],3);
	checkSuccess($fieldlengths["testmoney"],4);
	checkSuccess($fieldlengths["testsmallmoney"],4);
	checkSuccess($fieldlengths["testdatetime"],19);
	checkSuccess($fieldlengths["testsmalldatetime"],19);
	checkSuccess($fieldlengths["testchar"],40);
	checkSuccess($fieldlengths["testvarchar"],12);
	checkSuccess($fieldlengths["testbit"],1);
	echo("\n");
	$fieldlengths=sqlrcur_getRowLengthsAssoc($cur,7);
	checkSuccess($fieldlengths["testint"],1);
	checkSuccess($fieldlengths["testsmallint"],1);
	checkSuccess($fieldlengths["testtinyint"],1);
	#checkSuccess($fieldlengths["testreal"],3);
	#checkSuccess($fieldlengths["testfloat"],3);
	checkSuccess($fieldlengths["testdecimal"],3);
	checkSuccess($fieldlengths["testnumeric"],3);
	checkSuccess($fieldlengths["testmoney"],4);
	checkSuccess($fieldlengths["testsmallmoney"],4);
	checkSuccess($fieldlengths["testdatetime"],19);
	checkSuccess($fieldlengths["testsmalldatetime"],19);
	checkSuccess($fieldlengths["testchar"],40);
	checkSuccess($fieldlengths["testvarchar"],12);
	checkSuccess($fieldlengths["testbit"],1);
	echo("\n");

	echo("INDIVIDUAL SUBSTITUTIONS: \n");
	sqlrcur_prepareQuery($cur,"select $(var1),'$(var2)',$(var3)");
	sqlrcur_substitution($cur,"var1",1);
	sqlrcur_substitution($cur,"var2","hello");
	sqlrcur_substitution($cur,"var3",10.5556,6,4);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("FIELDS: \n");
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,0,1),"hello");
	checkSuccess(sqlrcur_getField($cur,0,2),"10.5556");
	echo("\n");


	echo("ARRAY SUBSTITUTIONS: \n");
	sqlrcur_prepareQuery($cur,"select $(var1),'$(var2)',$(var3)");
	$vars=array("var1","var2","var3");
	$vals=array(1,"hello",10.5556);
	$precs=array(0,0,6);
	$scales=array(0,0,4);
	sqlrcur_substitutions($cur,$vars,$vals,$precs,$scales);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("FIELDS: \n");
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,0,1),"hello");
	checkSuccess(sqlrcur_getField($cur,0,2),"10.5556");
	echo("\n");

	echo("NULLS as Nulls: \n");
	sqlrcur_getNullsAsNulls($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"select NULL,1,NULL"),1);
	checkSuccess(sqlrcur_getField($cur,0,0),NULL);
	checkSuccess(sqlrcur_getField($cur,0,1),"1");
	checkSuccess(sqlrcur_getField($cur,0,2),NULL);
	sqlrcur_getNullsAsEmptyStrings($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"select NULL,1,NULL"),1);
	checkSuccess(sqlrcur_getField($cur,0,0),"");
	checkSuccess(sqlrcur_getField($cur,0,1),"1");
	checkSuccess(sqlrcur_getField($cur,0,2),"");
	sqlrcur_getNullsAsNulls($cur);
	echo("\n");

	echo("RESULT SET BUFFER SIZE: \n");
	checkSuccess(sqlrcur_getResultSetBufferSize($cur),0);
	sqlrcur_setResultSetBufferSize($cur,2);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	checkSuccess(sqlrcur_getResultSetBufferSize($cur),2);
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),0);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),2);
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,1,0),"2");
	checkSuccess(sqlrcur_getField($cur,2,0),"3");
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),2);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),4);
	checkSuccess(sqlrcur_getField($cur,6,0),"7");
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),6);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),8);
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),8);
	checkSuccess(sqlrcur_endOfResultSet($cur),1);
	checkSuccess(sqlrcur_rowCount($cur),8);
	echo("\n");

	echo("DONT GET COLUMN INFO: \n");
	sqlrcur_dontGetColumnInfo($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	checkSuccess(sqlrcur_getColumnName($cur,0),NULL);
	checkSuccess(sqlrcur_getColumnLength($cur,0),0);
	checkSuccess(sqlrcur_getColumnType($cur,0),NULL);
	sqlrcur_getColumnInfo($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	checkSuccess(sqlrcur_getColumnName($cur,0),"testint");
	checkSuccess(sqlrcur_getColumnLength($cur,0),4);
	checkSuccess(sqlrcur_getColumnType($cur,0),"INT");
	echo("\n");

	echo("SUSPENDED SESSION: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	sqlrcur_suspendResultSet($cur);
	checkSuccess(sqlrcon_suspendSession($con),1);
	$conport=sqlrcon_getConnectionPort($con);
	$consocket=sqlrcon_getConnectionSocket($con);
	checkSuccess(sqlrcon_resumeSession($con,$conport,$consocket),1);
	echo("\n");
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,1,0),"2");
	checkSuccess(sqlrcur_getField($cur,2,0),"3");
	checkSuccess(sqlrcur_getField($cur,3,0),"4");
	checkSuccess(sqlrcur_getField($cur,4,0),"5");
	checkSuccess(sqlrcur_getField($cur,5,0),"6");
	checkSuccess(sqlrcur_getField($cur,6,0),"7");
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	sqlrcur_suspendResultSet($cur);
	checkSuccess(sqlrcon_suspendSession($con),1);
	$conport=sqlrcon_getConnectionPort($con);
	$consocket=sqlrcon_getConnectionSocket($con);
	checkSuccess(sqlrcon_resumeSession($con,$conport,$consocket),1);
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,1,0),"2");
	checkSuccess(sqlrcur_getField($cur,2,0),"3");
	checkSuccess(sqlrcur_getField($cur,3,0),"4");
	checkSuccess(sqlrcur_getField($cur,4,0),"5");
	checkSuccess(sqlrcur_getField($cur,5,0),"6");
	checkSuccess(sqlrcur_getField($cur,6,0),"7");
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	sqlrcur_suspendResultSet($cur);
	checkSuccess(sqlrcon_suspendSession($con),1);
	$conport=sqlrcon_getConnectionPort($con);
	$consocket=sqlrcon_getConnectionSocket($con);
	checkSuccess(sqlrcon_resumeSession($con,$conport,$consocket),1);
	echo("\n");
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,1,0),"2");
	checkSuccess(sqlrcur_getField($cur,2,0),"3");
	checkSuccess(sqlrcur_getField($cur,3,0),"4");
	checkSuccess(sqlrcur_getField($cur,4,0),"5");
	checkSuccess(sqlrcur_getField($cur,5,0),"6");
	checkSuccess(sqlrcur_getField($cur,6,0),"7");
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");

	echo("SUSPENDED RESULT SET: \n");
	sqlrcur_setResultSetBufferSize($cur,2);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	checkSuccess(sqlrcur_getField($cur,2,0),"3");
	$id=sqlrcur_getResultSetId($cur);
	sqlrcur_suspendResultSet($cur);
	checkSuccess(sqlrcon_suspendSession($con),1);
	$conport=sqlrcon_getConnectionPort($con);
	$consocket=sqlrcon_getConnectionSocket($con);
	checkSuccess(sqlrcon_resumeSession($con,$conport,$consocket),1);
	checkSuccess(sqlrcur_resumeResultSet($cur,$id),1);
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),4);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),6);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),6);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),8);
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),8);
	checkSuccess(sqlrcur_endOfResultSet($cur),1);
	checkSuccess(sqlrcur_rowCount($cur),8);
	sqlrcur_setResultSetBufferSize($cur,0);
	echo("\n");

	echo("CACHED RESULT SET: \n");
	sqlrcur_cacheToFile($cur,"/tmp/cachefile1");
	sqlrcur_setCacheTtl($cur,200);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	$filename=sqlrcur_getCacheFileName($cur);
	checkSuccess($filename,"/tmp/cachefile1");
	sqlrcur_cacheOff($cur);
	checkSuccess(sqlrcur_openCachedResultSet($cur,$filename),1);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");

	echo("COLUMN COUNT FOR CACHED RESULT SET: \n");
	checkSuccess(sqlrcur_colCount($cur),14);
	echo("\n");

	echo("COLUMN NAMES FOR CACHED RESULT SET: \n");
	checkSuccess(sqlrcur_getColumnName($cur,0),"testint");
	checkSuccess(sqlrcur_getColumnName($cur,1),"testsmallint");
	checkSuccess(sqlrcur_getColumnName($cur,2),"testtinyint");
	checkSuccess(sqlrcur_getColumnName($cur,3),"testreal");
	checkSuccess(sqlrcur_getColumnName($cur,4),"testfloat");
	checkSuccess(sqlrcur_getColumnName($cur,5),"testdecimal");
	checkSuccess(sqlrcur_getColumnName($cur,6),"testnumeric");
	checkSuccess(sqlrcur_getColumnName($cur,7),"testmoney");
	checkSuccess(sqlrcur_getColumnName($cur,8),"testsmallmoney");
	checkSuccess(sqlrcur_getColumnName($cur,9),"testdatetime");
	checkSuccess(sqlrcur_getColumnName($cur,10),"testsmalldatetime");
	checkSuccess(sqlrcur_getColumnName($cur,11),"testchar");
	checkSuccess(sqlrcur_getColumnName($cur,12),"testvarchar");
	checkSuccess(sqlrcur_getColumnName($cur,13),"testbit");
	$cols=sqlrcur_getColumnNames($cur);
	checkSuccess($cols[0],"testint");
	checkSuccess($cols[1],"testsmallint");
	checkSuccess($cols[2],"testtinyint");
	checkSuccess($cols[3],"testreal");
	checkSuccess($cols[4],"testfloat");
	checkSuccess($cols[5],"testdecimal");
	checkSuccess($cols[6],"testnumeric");
	checkSuccess($cols[7],"testmoney");
	checkSuccess($cols[8],"testsmallmoney");
	checkSuccess($cols[9],"testdatetime");
	checkSuccess($cols[10],"testsmalldatetime");
	checkSuccess($cols[11],"testchar");
	checkSuccess($cols[12],"testvarchar");
	checkSuccess($cols[13],"testbit");
	echo("\n");

	echo("CACHED RESULT SET WITH RESULT SET BUFFER SIZE: \n");
	sqlrcur_setResultSetBufferSize($cur,2);
	sqlrcur_cacheToFile($cur,"/tmp/cachefile1");
	sqlrcur_setCacheTtl($cur,200);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	$filename=sqlrcur_getCacheFileName($cur);
	checkSuccess($filename,"/tmp/cachefile1");
	sqlrcur_cacheOff($cur);
	checkSuccess(sqlrcur_openCachedResultSet($cur,$filename),1);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	sqlrcur_setResultSetBufferSize($cur,0);
	echo("\n");

	echo("FROM ONE CACHE FILE TO ANOTHER: \n");
	sqlrcur_cacheToFile($cur,"/tmp/cachefile2");
	checkSuccess(sqlrcur_openCachedResultSet($cur,"/tmp/cachefile1"),1);
	sqlrcur_cacheOff($cur);
	checkSuccess(sqlrcur_openCachedResultSet($cur,"/tmp/cachefile2"),1);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	echo("\n");

	echo("FROM ONE CACHE FILE TO ANOTHER WITH RESULT SET BUFFER SIZE: \n");
	sqlrcur_setResultSetBufferSize($cur,2);
	sqlrcur_cacheToFile($cur,"/tmp/cachefile2");
	checkSuccess(sqlrcur_openCachedResultSet($cur,"/tmp/cachefile1"),1);
	sqlrcur_cacheOff($cur);
	checkSuccess(sqlrcur_openCachedResultSet($cur,"/tmp/cachefile2"),1);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	sqlrcur_setResultSetBufferSize($cur,0);
	echo("\n");

	echo("CACHED RESULT SET WITH SUSPEND AND RESULT SET BUFFER SIZE: \n");
	sqlrcur_setResultSetBufferSize($cur,2);
	sqlrcur_cacheToFile($cur,"/tmp/cachefile1");
	sqlrcur_setCacheTtl($cur,200);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),1);
	checkSuccess(sqlrcur_getField($cur,2,0),"3");
	$filename=sqlrcur_getCacheFileName($cur);
	checkSuccess($filename,"/tmp/cachefile1");
	$id=sqlrcur_getResultSetId($cur);
	sqlrcur_suspendResultSet($cur);
	checkSuccess(sqlrcon_suspendSession($con),1);
	$conport=sqlrcon_getConnectionPort($con);
	$consocket=sqlrcon_getConnectionSocket($con);
	echo("\n");
	checkSuccess(sqlrcon_resumeSession($con,$conport,$consocket),1);
	checkSuccess(sqlrcur_resumeCachedResultSet($cur,$id,$filename),1);
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),4);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),6);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),6);
	checkSuccess(sqlrcur_endOfResultSet($cur),0);
	checkSuccess(sqlrcur_rowCount($cur),8);
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	echo("\n");
	checkSuccess(sqlrcur_firstRowIndex($cur),8);
	checkSuccess(sqlrcur_endOfResultSet($cur),1);
	checkSuccess(sqlrcur_rowCount($cur),8);
	sqlrcur_cacheOff($cur);
	echo("\n");
	checkSuccess(sqlrcur_openCachedResultSet($cur,$filename),1);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	checkSuccess(sqlrcur_getField($cur,8,0),NULL);
	sqlrcur_setResultSetBufferSize($cur,0);
	echo("\n");

	# drop existing table
	sqlrcur_sendQuery($cur,"drop table testtable");

	# invalid queries...
	echo("INVALID QUERIES: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testint"),0);
	echo("\n");
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (1,2,3,4)"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (1,2,3,4)"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (1,2,3,4)"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (1,2,3,4)"),0);
	echo("\n");
	checkSuccess(sqlrcur_sendQuery($cur,"create table testtable"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"create table testtable"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"create table testtable"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"create table testtable"),0);
	echo("\n");

?></pre></html>
