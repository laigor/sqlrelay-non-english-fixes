// Copyright (c) 2014  David Muse
// See the file COPYING for more information
#include <rudiments/commandline.h>
#include <rudiments/process.h>
#include <rudiments/stdio.h>

#include "bench.h"

//#include "oraclebench.h"
#include "sqlrelaybench.h"

#define ORACLE_SID "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = db64.firstworks.com)(PORT = 1521)) (CONNECT_DATA = (SERVER = DEDICATED) (SERVICE_NAME = ora1)))"

int main(int argc, const char **argv) {

	// process the command line
	commandline	cmdl(argc,argv);

	// usage info
	if (cmdl.found("help") || cmdl.found("h")) {
		stdoutput.printf(
			"usage: bench \\\n"
			"	-db [db] \\\n"
			"	-connections [connection-count] \\\n"
			"	-queries [query-per-connection-count] \\\n"
			"	-rows [rows-per-query] \\\n"
			"	-cols [columns-per-query] \\\n"
			"	-colsize [characters-per-column] \\\n"
			"	-debug\n");
		process::exit(1);
	}

	// default parameters
	const char	*db="oracle";
	uint64_t	conns=10;
	uint64_t	queries=10;
	uint64_t	rows=10;
	uint32_t	cols=10;
	uint32_t	colsize=10;
	bool		debug=false;

	// override defaults with command line parameters
	if (cmdl.found("db")) {
		db=cmdl.getValue("db");
	}
	if (cmdl.found("connections")) {
		conns=charstring::toInteger(cmdl.getValue("connections"));
	}
	if (cmdl.found("queries")) {
		queries=charstring::toInteger(cmdl.getValue("queries"));
	}
	if (cmdl.found("rows")) {
		rows=charstring::toInteger(cmdl.getValue("rows"));
	}
	if (cmdl.found("cols")) {
		cols=charstring::toInteger(cmdl.getValue("cols"));
	}
	if (cmdl.found("colsize")) {
		colsize=charstring::toInteger(cmdl.getValue("colsize"));
	}
	if (cmdl.found("debug")) {
		debug=true;
	}

	// for each database...
	bool	error=false;

	// first time for the real db, second time for sqlrelay...
	for (uint16_t which=0; which<2 && !error; which++) {

		if (!which) {
			stdoutput.printf("benchmarking %s\n",db);
		} else {
			stdoutput.printf("benchmarking sqlrelay-%s\n",db);
		}

		// init benchmarks
		benchmarks	*bm=NULL;
		if (which) {
			bm=new sqlrelaybenchmarks(
					"host=localhost;port=9000;"
					"socket=/tmp/test.socket;"
					"user=test;password=test;"
					"debug=no",
					db,conns,queries,
					rows,cols,colsize,debug);
		} else if (!charstring::compare(db,"db2")) {
		} else if (!charstring::compare(db,"firebird")) {
		} else if (!charstring::compare(db,"freetds")) {
		} else if (!charstring::compare(db,"mdbtools")) {
		} else if (!charstring::compare(db,"mysql")) {
		} else if (!charstring::compare(db,"odbc")) {
		} else if (!charstring::compare(db,"oracle")) {
		} else if (!charstring::compare(db,"postgresql")) {
		} else if (!charstring::compare(db,"sqlite")) {
		} else if (!charstring::compare(db,"sybase")) {
		}
		if (!bm) {
			stdoutput.printf("error creating benchmarks\n");
			continue;
		}

		// run the benchmarks
		bm->run();

		// clean up
		delete bm;
	}

	// exit
	process::exit(0);
}
