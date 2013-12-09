// Copyright (c) 1999-2001  David Muse
// See the file COPYING for more information

#include <config.h>
#include <defaults.h>

#include <sqlrelay/sqlrclient.h>
#include <rudiments/commandline.h>
#include <rudiments/stringbuffer.h>
#include <rudiments/process.h>
#include <rudiments/stdio.h>
#include <sqlrconfigfile.h>

int main(int argc, const char **argv) {

	#include <version.h>

	commandline	cmdline(argc,argv);
	sqlrconfigfile	cfgfile;

	const char	*config=cmdline.getValue("-config");
	if (!(config && config[0])) {
		config=DEFAULT_CONFIG_FILE;
	}
	const char	*id=cmdline.getValue("-id");
	const char	*host=cmdline.getValue("-host");
	uint16_t	port=charstring::toInteger(
					cmdline.getValue("-port"));
	const char	*socket=cmdline.getValue("-socket");
	const char	*user=cmdline.getValue("-user");
	const char	*password=cmdline.getValue("-password");
	const char	*table=cmdline.getValue("-table");
	bool		debug=cmdline.found("-debug");
	const char	*debugfile=NULL;
	if (debug) {
		debugfile=cmdline.getValue("-debug");
	}

	if (!(charstring::length(id) ||
		((charstring::length(host) ||
			charstring::length(socket)) &&
				charstring::length(user) &&
				charstring::length(password))) ||
		!(charstring::length(table))) {

		stdoutput.printf("usage: sqlr-fields -host host -port port -socket socket -table table [-debug [filename]]\n"
			"  or   sqlr-fields  [-config configfile] -id id -table table [-debug [filename]]\n");
		process::exit(1);
	}

	if (charstring::length(id) && cfgfile.parse(config,id)) {

		// get the host/port/socket/username/password
		host="localhost";
		port=cfgfile.getPort();
		socket=cfgfile.getUnixPort();
		usercontainer	*currentnode=
			cfgfile.getUserList()->getFirstNode()->getValue();
		user=currentnode->getUser();
		password=currentnode->getPassword();
	}

	sqlrconnection	sqlrcon(host,port,socket,user,password,0,1);
	sqlrcursor	sqlrcur(&sqlrcon);

	if (debug) {
		if (debugfile) {
			sqlrcon.setDebugFile(debugfile);
		}
		sqlrcon.debugOn();
	}

	if (sqlrcur.getColumnList(table,NULL)) {
		for (uint64_t j=0; j<sqlrcur.rowCount(); j++) {
			if (j>0) {
				stdoutput.printf(",");
			}
			stdoutput.printf("%s",sqlrcur.getField(j,(uint32_t)0));
		}
		stdoutput.printf("\n");
	}

	process::exit(0);
}
