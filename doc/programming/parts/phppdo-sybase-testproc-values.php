$stmt=$dbh->prepare("exec testproc");
$stmt->bindValue("@in1",1);
$stmt->bindValue("@in2","1.1");
$stmt->bindValue("@in3","hello");
$out1=0;
$stmt->bindParam("@out1",$out1,PDO::PARAM_INT|PDO::PARAM_INPUT_OUTPUT);
$out2="";
$stmt->bindParam("@out2",$out2,PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT);
$out3="";
$stmt->bindParam("@out3",$out3,PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT);
$result=$stmt->execute();
