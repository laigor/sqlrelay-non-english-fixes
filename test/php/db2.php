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

	# instantiation
	$con=sqlrcon_alloc($host,$port,$socket,$user,$password,0,1);
	$cur=sqlrcur_alloc($con);

	# get database type
	echo("IDENTIFY: \n");
	checkSuccess(sqlrcon_identify($con),"db2");
	echo("\n");

	# ping
	echo("PING: \n");
	checkSuccess(sqlrcon_ping($con),1);
	echo("\n");

	# drop existing table
	sqlrcur_sendQuery($cur,"drop table testtable");

	echo("CREATE TEMPTABLE: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"create table testtable (testsmallint smallint, testint integer, testbigint bigint, testdecimal decimal(10,2), testreal real, testdouble double, testchar char(40), testvarchar varchar(40), testdate date, testtime time, testtimestamp timestamp)"),1);
	echo("\n");

	echo("INSERT: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (1,1,1,1.1,1.1,1.1,'testchar1','testvarchar1','01/01/2001','01:00:00',NULL)"),1);
	echo("\n");

	echo("BIND BY POSITION: \n");
	sqlrcur_prepareQuery($cur,"insert into testtable values (?,?,?,?,?,?,?,?,?,?,NULL)");
	sqlrcur_inputBind($cur,"1",2);
	sqlrcur_inputBind($cur,"2",2);
	sqlrcur_inputBind($cur,"3",2);
	sqlrcur_inputBind($cur,"4",2.2,4,2);
	sqlrcur_inputBind($cur,"5",2.2,4,2);
	sqlrcur_inputBind($cur,"6",2.2,4,2);
	sqlrcur_inputBind($cur,"7","testchar2");
	sqlrcur_inputBind($cur,"8","testvarchar2");
	sqlrcur_inputBind($cur,"9","01/01/2002");
	sqlrcur_inputBind($cur,"10","02:00:00");
	checkSuccess(sqlrcur_executeQuery($cur),1);
	sqlrcur_clearBinds($cur);
	sqlrcur_inputBind($cur,"1",3);
	sqlrcur_inputBind($cur,"2",3);
	sqlrcur_inputBind($cur,"3",3);
	sqlrcur_inputBind($cur,"4",3.3,4,2);
	sqlrcur_inputBind($cur,"5",3.3,4,2);
	sqlrcur_inputBind($cur,"6",3.3,4,2);
	sqlrcur_inputBind($cur,"7","testchar3");
	sqlrcur_inputBind($cur,"8","testvarchar3");
	sqlrcur_inputBind($cur,"9","01/01/2003");
	sqlrcur_inputBind($cur,"10","03:00:00");
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("ARRAY OF BINDS BY POSITION: \n");
	sqlrcur_clearBinds($cur);
	$bindvars=array("1","2","3","4","5","6","7","8","9","10");
	$bindvals=array("4","4","4","4.4","4.4","4.4",
			"testchar4","testvarchar4","01/01/2004","04:00:00");
	sqlrcur_inputBinds($cur,$bindvars,$bindvals);
	checkSuccess(sqlrcur_executeQuery($cur),1);
	echo("\n");

	echo("INSERT: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (5,5,5,5.5,5.5,5.5,'testchar5','testvarchar5','01/01/2005','05:00:00',NULL)"),1);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (6,6,6,6.6,6.6,6.6,'testchar6','testvarchar6','01/01/2006','06:00:00',NULL)"),1);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (7,7,7,7.7,7.7,7.7,'testchar7','testvarchar7','01/01/2007','07:00:00',NULL)"),1);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable values (8,8,8,8.8,8.8,8.8,'testchar8','testvarchar8','01/01/2008','08:00:00',NULL)"),1);
	echo("\n");

	echo("AFFECTED ROWS: \n");
	checkSuccess(sqlrcur_affectedRows($cur),1);
	echo("\n");

	#echo("STORED PROCEDURE: \n");
	#sqlrcur_sendQuery($cur,"drop procedure testproc");
	#checkSuccess(sqlrcur_sendQuery($cur,"create procedure testproc(in invar int, out outvar int) language sql begin set outvar = invar; end"),1);
	#sqlrcur_prepareQuery($cur,"call testproc(?,?)");
	#sqlrcur_inputBind($cur,"1",5);
	#sqlrcur_defineOutputBind($cur,"2",10);
	#checkSuccess(sqlrcur_executeQuery($cur),1);
	#checkSuccess(sqlrcur_getOutputBind($cur,"2"),"5");
	#checkSuccess(sqlrcur_sendQuery($cur,"drop procedure testproc"),1);
	#echo("\n");

	echo("SELECT: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
	echo("\n");

	echo("COLUMN COUNT: \n");
	checkSuccess(sqlrcur_colCount($cur),11);
	echo("\n");

	echo("COLUMN NAMES: \n");
	checkSuccess(sqlrcur_getColumnName($cur,0),"TESTSMALLINT");
	checkSuccess(sqlrcur_getColumnName($cur,1),"TESTINT");
	checkSuccess(sqlrcur_getColumnName($cur,2),"TESTBIGINT");
	checkSuccess(sqlrcur_getColumnName($cur,3),"TESTDECIMAL");
	checkSuccess(sqlrcur_getColumnName($cur,4),"TESTREAL");
	checkSuccess(sqlrcur_getColumnName($cur,5),"TESTDOUBLE");
	checkSuccess(sqlrcur_getColumnName($cur,6),"TESTCHAR");
	checkSuccess(sqlrcur_getColumnName($cur,7),"TESTVARCHAR");
	checkSuccess(sqlrcur_getColumnName($cur,8),"TESTDATE");
	checkSuccess(sqlrcur_getColumnName($cur,9),"TESTTIME");
	checkSuccess(sqlrcur_getColumnName($cur,10),"TESTTIMESTAMP");
	$cols=sqlrcur_getColumnNames($cur);
	checkSuccess($cols[0],"TESTSMALLINT");
	checkSuccess($cols[1],"TESTINT");
	checkSuccess($cols[2],"TESTBIGINT");
	checkSuccess($cols[3],"TESTDECIMAL");
	checkSuccess($cols[4],"TESTREAL");
	checkSuccess($cols[5],"TESTDOUBLE");
	checkSuccess($cols[6],"TESTCHAR");
	checkSuccess($cols[7],"TESTVARCHAR");
	checkSuccess($cols[8],"TESTDATE");
	checkSuccess($cols[9],"TESTTIME");
	checkSuccess($cols[10],"TESTTIMESTAMP");
	echo("\n");

	echo("COLUMN TYPES: \n");
	checkSuccess(sqlrcur_getColumnType($cur,0),"SMALLINT");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTSMALLINT"),"SMALLINT");
	checkSuccess(sqlrcur_getColumnType($cur,1),"INTEGER");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTINT"),"INTEGER");
	checkSuccess(sqlrcur_getColumnType($cur,2),"BIGINT");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTBIGINT"),"BIGINT");
	checkSuccess(sqlrcur_getColumnType($cur,3),"DECIMAL");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTDECIMAL"),"DECIMAL");
	checkSuccess(sqlrcur_getColumnType($cur,4),"REAL");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTREAL"),"REAL");
	checkSuccess(sqlrcur_getColumnType($cur,5),"DOUBLE");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTDOUBLE"),"DOUBLE");
	checkSuccess(sqlrcur_getColumnType($cur,6),"CHAR");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTCHAR"),"CHAR");
	checkSuccess(sqlrcur_getColumnType($cur,7),"VARCHAR");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTVARCHAR"),"VARCHAR");
	checkSuccess(sqlrcur_getColumnType($cur,8),"DATE");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTDATE"),"DATE");
	checkSuccess(sqlrcur_getColumnType($cur,9),"TIME");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTTIME"),"TIME");
	checkSuccess(sqlrcur_getColumnType($cur,10),"TIMESTAMP");
	checkSuccess(sqlrcur_getColumnType($cur,"TESTTIMESTAMP"),"TIMESTAMP");
	echo("\n");

	echo("COLUMN LENGTH: \n");
	checkSuccess(sqlrcur_getColumnLength($cur,0),2);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTSMALLINT"),2);
	checkSuccess(sqlrcur_getColumnLength($cur,1),4);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTINT"),4);
	checkSuccess(sqlrcur_getColumnLength($cur,2),8);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTBIGINT"),8);
	checkSuccess(sqlrcur_getColumnLength($cur,3),12);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTDECIMAL"),12);
	checkSuccess(sqlrcur_getColumnLength($cur,4),4);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTREAL"),4);
	checkSuccess(sqlrcur_getColumnLength($cur,5),8);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTDOUBLE"),8);
	checkSuccess(sqlrcur_getColumnLength($cur,6),40);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTCHAR"),40);
	checkSuccess(sqlrcur_getColumnLength($cur,7),40);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTVARCHAR"),40);
	checkSuccess(sqlrcur_getColumnLength($cur,8),6);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTDATE"),6);
	checkSuccess(sqlrcur_getColumnLength($cur,9),6);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTTIME"),6);
	checkSuccess(sqlrcur_getColumnLength($cur,10),16);
	checkSuccess(sqlrcur_getColumnLength($cur,"TESTTIMESTAMP"),16);
	echo("\n");

	echo("LONGEST COLUMN: \n");
	checkSuccess(sqlrcur_getLongest($cur,0),1);
	checkSuccess(sqlrcur_getLongest($cur,"TESTSMALLINT"),1);
	checkSuccess(sqlrcur_getLongest($cur,1),1);
	checkSuccess(sqlrcur_getLongest($cur,"TESTINT"),1);
	checkSuccess(sqlrcur_getLongest($cur,2),1);
	checkSuccess(sqlrcur_getLongest($cur,"TESTBIGINT"),1);
	checkSuccess(sqlrcur_getLongest($cur,3),4);
	checkSuccess(sqlrcur_getLongest($cur,"TESTDECIMAL"),4);
	#checkSuccess(sqlrcur_getLongest($cur,4),3);
	#checkSuccess(sqlrcur_getLongest($cur,"TESTREAL"),3);
	#checkSuccess(sqlrcur_getLongest($cur,5),3);
	#checkSuccess(sqlrcur_getLongest($cur,"TESTDOUBLE"),3);
	checkSuccess(sqlrcur_getLongest($cur,6),40);
	checkSuccess(sqlrcur_getLongest($cur,"TESTCHAR"),40);
	checkSuccess(sqlrcur_getLongest($cur,7),12);
	checkSuccess(sqlrcur_getLongest($cur,"TESTVARCHAR"),12);
	checkSuccess(sqlrcur_getLongest($cur,8),10);
	checkSuccess(sqlrcur_getLongest($cur,"TESTDATE"),10);
	checkSuccess(sqlrcur_getLongest($cur,9),8);
	checkSuccess(sqlrcur_getLongest($cur,"TESTTIME"),8);
	echo("\n");

	echo("ROW COUNT: \n");
	checkSuccess(sqlrcur_rowCount($cur),8);
	echo("\n");

	echo("TOTAL ROWS: \n");
	checkSuccess(sqlrcur_totalRows($cur),-1);
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
	checkSuccess(sqlrcur_getField($cur,0,3),"1.10");
	#checkSuccess(sqlrcur_getField($cur,0,4),"1.1");
	#checkSuccess(sqlrcur_getField($cur,0,5),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,6),"testchar1                               ");
	checkSuccess(sqlrcur_getField($cur,0,7),"testvarchar1");
	checkSuccess(sqlrcur_getField($cur,0,8),"2001-01-01");
	checkSuccess(sqlrcur_getField($cur,0,9),"01:00:00");
	echo("\n");
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	checkSuccess(sqlrcur_getField($cur,7,1),"8");
	checkSuccess(sqlrcur_getField($cur,7,2),"8");
	checkSuccess(sqlrcur_getField($cur,7,3),"8.80");
	#checkSuccess(sqlrcur_getField($cur,7,4),"8.8");
	#checkSuccess(sqlrcur_getField($cur,7,5),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,6),"testchar8                               ");
	checkSuccess(sqlrcur_getField($cur,7,7),"testvarchar8");
	checkSuccess(sqlrcur_getField($cur,7,8),"2008-01-01");
	checkSuccess(sqlrcur_getField($cur,7,9),"08:00:00");
	echo("\n");

	echo("FIELD LENGTHS BY INDEX: \n");
	checkSuccess(sqlrcur_getFieldLength($cur,0,0),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,1),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,2),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,3),4);
	#checkSuccess(sqlrcur_getFieldLength($cur,0,4),3);
	#checkSuccess(sqlrcur_getFieldLength($cur,0,5),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,6),40);
	checkSuccess(sqlrcur_getFieldLength($cur,0,7),12);
	checkSuccess(sqlrcur_getFieldLength($cur,0,8),10);
	checkSuccess(sqlrcur_getFieldLength($cur,0,9),8);
	echo("\n");
	checkSuccess(sqlrcur_getFieldLength($cur,7,0),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,1),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,2),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,3),4);
	#checkSuccess(sqlrcur_getFieldLength($cur,7,4),3);
	#checkSuccess(sqlrcur_getFieldLength($cur,7,5),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,6),40);
	checkSuccess(sqlrcur_getFieldLength($cur,7,7),12);
	checkSuccess(sqlrcur_getFieldLength($cur,7,8),10);
	checkSuccess(sqlrcur_getFieldLength($cur,7,9),8);
	echo("\n");

	echo("FIELDS BY NAME: \n");
	checkSuccess(sqlrcur_getField($cur,0,"testsmallint"),"1");
	checkSuccess(sqlrcur_getField($cur,0,"testint"),"1");
	checkSuccess(sqlrcur_getField($cur,0,"testbigint"),"1");
	checkSuccess(sqlrcur_getField($cur,0,"testdecimal"),"1.10");
	#checkSuccess(sqlrcur_getField($cur,0,"testreal"),"1.1");
	#checkSuccess(sqlrcur_getField($cur,0,"testdouble"),"1.1");
	checkSuccess(sqlrcur_getField($cur,0,"testchar"),"testchar1                               ");
	checkSuccess(sqlrcur_getField($cur,0,"testvarchar"),"testvarchar1");
	checkSuccess(sqlrcur_getField($cur,0,"testdate"),"2001-01-01");
	checkSuccess(sqlrcur_getField($cur,0,"testtime"),"01:00:00");
	echo("\n");
	checkSuccess(sqlrcur_getField($cur,7,"testsmallint"),"8");
	checkSuccess(sqlrcur_getField($cur,7,"testint"),"8");
	checkSuccess(sqlrcur_getField($cur,7,"testbigint"),"8");
	checkSuccess(sqlrcur_getField($cur,7,"testdecimal"),"8.80");
	#checkSuccess(sqlrcur_getField($cur,7,"testreal"),"8.8");
	#checkSuccess(sqlrcur_getField($cur,7,"testdouble"),"8.8");
	checkSuccess(sqlrcur_getField($cur,7,"testchar"),"testchar8                               ");
	checkSuccess(sqlrcur_getField($cur,7,"testvarchar"),"testvarchar8");
	checkSuccess(sqlrcur_getField($cur,7,"testdate"),"2008-01-01");
	checkSuccess(sqlrcur_getField($cur,7,"testtime"),"08:00:00");
	echo("\n");

	echo("FIELD LENGTHS BY NAME: \n");
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testsmallint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testbigint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testdecimal"),4);
	#checkSuccess(sqlrcur_getFieldLength($cur,0,"testreal"),3);
	#checkSuccess(sqlrcur_getFieldLength($cur,0,"testdouble"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testchar"),40);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testvarchar"),12);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testdate"),10);
	checkSuccess(sqlrcur_getFieldLength($cur,0,"testtime"),8);
	echo("\n");
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testsmallint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testbigint"),1);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testdecimal"),4);
	#checkSuccess(sqlrcur_getFieldLength($cur,7,"testreal"),3);
	#checkSuccess(sqlrcur_getFieldLength($cur,7,"testdouble"),3);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testchar"),40);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testvarchar"),12);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testdate"),10);
	checkSuccess(sqlrcur_getFieldLength($cur,7,"testtime"),8);
	echo("\n");

	echo("FIELDS BY ARRAY: \n");
	$fields=sqlrcur_getRow($cur,0);
	checkSuccess($fields[0],"1");
	checkSuccess($fields[1],"1");
	checkSuccess($fields[2],"1");
	checkSuccess($fields[3],"1.10");
	#checkSuccess($fields[4],"1.1");
	#checkSuccess($fields[5],"1.1");
	checkSuccess($fields[6],"testchar1                               ");
	checkSuccess($fields[7],"testvarchar1");
	checkSuccess($fields[8],"2001-01-01");
	checkSuccess($fields[9],"01:00:00");
	echo("\n");

	echo("FIELD LENGTHS BY ARRAY: \n");
	$fieldlens=sqlrcur_getRowLengths($cur,0);
	checkSuccess($fieldlens[0],1);
	checkSuccess($fieldlens[1],1);
	checkSuccess($fieldlens[2],1);
	checkSuccess($fieldlens[3],4);
	#checkSuccess($fieldlens[4],3);
	#checkSuccess($fieldlens[5],3);
	checkSuccess($fieldlens[6],40);
	checkSuccess($fieldlens[7],12);
	checkSuccess($fieldlens[8],10);
	checkSuccess($fieldlens[9],8);
	echo("\n");

	echo("FIELDS BY ASSOCIATIVE ARRAY: \n");
	$fields=sqlrcur_getRowAssoc($cur,0);
	checkSuccess($fields["TESTSMALLINT"],"1");
	checkSuccess($fields["TESTINT"],"1");
	checkSuccess($fields["TESTBIGINT"],"1");
	checkSuccess($fields["TESTDECIMAL"],"1.10");
	#checkSuccess($fields["TESTREAL"],"1.1");
	#checkSuccess($fields["TESTDOUBLE"],"1.1");
	checkSuccess($fields["TESTCHAR"],"testchar1                               ");
	checkSuccess($fields["TESTVARCHAR"],"testvarchar1");
	checkSuccess($fields["TESTDATE"],"2001-01-01");
	checkSuccess($fields["TESTTIME"],"01:00:00");
	echo("\n");
	$fields=sqlrcur_getRowAssoc($cur,7);
	checkSuccess($fields["TESTSMALLINT"],"8");
	checkSuccess($fields["TESTINT"],"8");
	checkSuccess($fields["TESTBIGINT"],"8");
	checkSuccess($fields["TESTDECIMAL"],"8.80");
	#checkSuccess($fields["TESTREAL"],"8.8");
	#checkSuccess($fields["TESTDOUBLE"],"8.8");
	checkSuccess($fields["TESTCHAR"],"testchar8                               ");
	checkSuccess($fields["TESTVARCHAR"],"testvarchar8");
	checkSuccess($fields["TESTDATE"],"2008-01-01");
	checkSuccess($fields["TESTTIME"],"08:00:00");
	echo("\n");

	echo("FIELD LENGTHS BY ASSOCIATIVE ARRAY: \n");
	$fieldlengths=sqlrcur_getRowLengthsAssoc($cur,0);
	checkSuccess($fieldlengths["TESTSMALLINT"],1);
	checkSuccess($fieldlengths["TESTINT"],1);
	checkSuccess($fieldlengths["TESTBIGINT"],1);
	checkSuccess($fieldlengths["TESTDECIMAL"],4);
	#checkSuccess($fieldlengths["TESTREAL"],3);
	#checkSuccess($fieldlengths["TESTDOUBLE"],3);
	checkSuccess($fieldlengths["TESTCHAR"],40);
	checkSuccess($fieldlengths["TESTVARCHAR"],12);
	checkSuccess($fieldlengths["TESTDATE"],10);
	checkSuccess($fieldlengths["TESTTIME"],8);
	echo("\n");
	$fieldlengths=sqlrcur_getRowLengthsAssoc($cur,7);
	checkSuccess($fieldlengths["TESTSMALLINT"],1);
	checkSuccess($fieldlengths["TESTINT"],1);
	checkSuccess($fieldlengths["TESTBIGINT"],1);
	checkSuccess($fieldlengths["TESTDECIMAL"],4);
	#checkSuccess($fieldlengths["TESTREAL"],3);
	#checkSuccess($fieldlengths["TESTDOUBLE"],3);
	checkSuccess($fieldlengths["TESTCHAR"],40);
	checkSuccess($fieldlengths["TESTVARCHAR"],12);
	checkSuccess($fieldlengths["TESTDATE"],10);
	checkSuccess($fieldlengths["TESTTIME"],8);
	echo("\n");

	echo("INDIVIDUAL SUBSTITUTIONS: \n");
	sqlrcur_prepareQuery($cur,"values ($(var1),'$(var2)','$(var3)')");
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
	sqlrcur_prepareQuery($cur,"values ($(var1),'$(var2)',$(var3))");
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
	sqlrcur_sendQuery($cur,"drop table testtable1");
	sqlrcur_sendQuery($cur,"create table testtable1 (col1 char(1), col2 char(1), col3 char(1))");
	sqlrcur_getNullsAsNulls($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"insert into testtable1 values ('1',NULL,NULL)"),1);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable1"),1);
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,0,1),NULL);
	checkSuccess(sqlrcur_getField($cur,0,2),NULL);
	sqlrcur_getNullsAsEmptyStrings($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable1"),1);
	checkSuccess(sqlrcur_getField($cur,0,0),"1");
	checkSuccess(sqlrcur_getField($cur,0,1),"");
	checkSuccess(sqlrcur_getField($cur,0,2),"");
	checkSuccess(sqlrcur_sendQuery($cur,"drop table testtable1"),1);
	echo("\n");
	
	echo("RESULT SET BUFFER SIZE: \n");
	checkSuccess(sqlrcur_getResultSetBufferSize($cur),0);
	sqlrcur_setResultSetBufferSize($cur,2);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
	checkSuccess(sqlrcur_getColumnName($cur,0),NULL);
	checkSuccess(sqlrcur_getColumnLength($cur,0),0);
	checkSuccess(sqlrcur_getColumnType($cur,0),NULL);
	sqlrcur_getColumnInfo($cur);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
	checkSuccess(sqlrcur_getColumnName($cur,0),"TESTSMALLINT");
	checkSuccess(sqlrcur_getColumnLength($cur,0),2);
	checkSuccess(sqlrcur_getColumnType($cur,0),"SMALLINT");
	echo("\n");

	echo("SUSPENDED SESSION: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
	$filename=sqlrcur_getCacheFileName($cur);
	checkSuccess($filename,"/tmp/cachefile1");
	sqlrcur_cacheOff($cur);
	checkSuccess(sqlrcur_openCachedResultSet($cur,$filename),1);
	checkSuccess(sqlrcur_getField($cur,7,0),"8");
	echo("\n");

	echo("COLUMN COUNT FOR CACHED RESULT SET: \n");
	checkSuccess(sqlrcur_colCount($cur),11);
	echo("\n");

	echo("COLUMN NAMES FOR CACHED RESULT SET: \n");
	checkSuccess(sqlrcur_getColumnName($cur,0),"TESTSMALLINT");
	checkSuccess(sqlrcur_getColumnName($cur,1),"TESTINT");
	checkSuccess(sqlrcur_getColumnName($cur,2),"TESTBIGINT");
	checkSuccess(sqlrcur_getColumnName($cur,3),"TESTDECIMAL");
	checkSuccess(sqlrcur_getColumnName($cur,4),"TESTREAL");
	checkSuccess(sqlrcur_getColumnName($cur,5),"TESTDOUBLE");
	checkSuccess(sqlrcur_getColumnName($cur,6),"TESTCHAR");
	checkSuccess(sqlrcur_getColumnName($cur,7),"TESTVARCHAR");
	checkSuccess(sqlrcur_getColumnName($cur,8),"TESTDATE");
	checkSuccess(sqlrcur_getColumnName($cur,9),"TESTTIME");
	checkSuccess(sqlrcur_getColumnName($cur,10),"TESTTIMESTAMP");
	$cols=sqlrcur_getColumnNames($cur);
	checkSuccess($cols[0],"TESTSMALLINT");
	checkSuccess($cols[1],"TESTINT");
	checkSuccess($cols[2],"TESTBIGINT");
	checkSuccess($cols[3],"TESTDECIMAL");
	checkSuccess($cols[4],"TESTREAL");
	checkSuccess($cols[5],"TESTDOUBLE");
	checkSuccess($cols[6],"TESTCHAR");
	checkSuccess($cols[7],"TESTVARCHAR");
	checkSuccess($cols[8],"TESTDATE");
	checkSuccess($cols[9],"TESTTIME");
	checkSuccess($cols[10],"TESTTIMESTAMP");
	echo("\n");

	echo("CACHED RESULT SET WITH RESULT SET BUFFER SIZE: \n");
	sqlrcur_setResultSetBufferSize($cur,2);
	sqlrcur_cacheToFile($cur,"/tmp/cachefile1");
	sqlrcur_setCacheTtl($cur,200);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),1);
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
	sqlrcon_commit($con);
	sqlrcur_sendQuery($cur,"drop table testtable");
	echo("\n");

	# invalid queries...
	echo("INVALID QUERIES: \n");
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),0);
	checkSuccess(sqlrcur_sendQuery($cur,"select * from testtable order by testsmallint"),0);
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
