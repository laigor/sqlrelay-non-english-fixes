my $sth=$dbh->prepare("call testproc(?,?,?)");
$sth->bind_param("1",1);
$sth->bind_param("2",1.1,2,1);
$sth->bind_param("3","hello");
$sth->execute();