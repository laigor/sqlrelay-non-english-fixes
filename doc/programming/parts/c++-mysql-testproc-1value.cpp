cur->prepareQuery("select testfunc(?,?,?)");
cur->inputBind("1",1);
cur->inputBind("2",1.1,4,2);
cur->inputBind("3","hello");
cur->executeQuery();
char    *result=cur->getField(0,0);
