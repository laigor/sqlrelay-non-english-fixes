cur.prepareQuery("call exampleproc(?,?,?,?)");
cur.inputBind("1", 1);
cur.inputBind("2", 1.1, 2, 1);
cur.inputBind("3", "hello");
cur.defineOutputBindInteger("4");
cur.executeQuery();
Int64 result=cur.getOutputBindInteger("4");