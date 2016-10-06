#include <sqlrelay/sqlrclient.h>

main() {

        sqlrconnection      *con=new sqlrconnection("sqlrserver",9000,"/tmp/test.socket","user","password",0,1);
        sqlrcursor          *cursor1=new sqlrcursor(con);
        sqlrcursor          *cursor2=new sqlrcursor(con);

        cursor1->setResultSetBufferSize(10);
        cursor1->sendQuery("select * from my_huge_table");

        int     index=0;
        while (!cursor1->endOfResultSet()) {
                cursor2->prepareQuery("insert into my_other_table values (:1,:2,:3)");
                cursor2->inputBind("1",cursor1->getField(index,1));
                cursor2->inputBind("2",cursor1->getField(index,2));
                cursor2->inputBind("3",cursor1->getField(index,3));
                cursor2->executeQuery();
        }

        delete cursor2;
        delete cursor1;
        delete con;
}