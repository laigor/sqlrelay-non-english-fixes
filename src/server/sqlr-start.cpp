// Copyright (c) 1999-2001  David Muse
// See the file COPYING for more information

#include <config.h>
#include <defaults.h>
#include <sqlrelay/sqlrutil.h>
#include <rudiments/process.h>
#include <rudiments/stdio.h>

#ifdef _WIN32
	#include <stdio.h>
	#include <io.h>
#endif

// for ceil()
#include <math.h>

bool	iswindows;

bool startListener(const char *id, const char *config,
			const char *localstatedir, bool disablecrashhandler) {

	// start the listener
	stdoutput.printf("\nStarting listener:\n");

	// build command to spawn
	const char	*cmd=NULL;
	if (iswindows) {
		cmd="sqlr-listener.exe";
	} else {
		cmd="sqlr-listener";
 	}
	uint16_t	i=0;
	const char	*args[9];
	args[i++]="sqlr-listener";
	args[i++]="-id";
	args[i++]=id;
	args[i++]="-config";
	args[i++]=config;
	if (charstring::length(localstatedir)) {
		args[i++]="-localstatedir";
		args[i++]=localstatedir;
	}
	if (disablecrashhandler) {
		args[i++]="-disable-crash-handler";
	}
	args[i]=NULL;
	
	// display command
	stdoutput.printf("  ");
	for (uint16_t index=0; index<i; index++) {
		stdoutput.printf("%s ",args[index]);
	}
	stdoutput.printf("\n");

	// spawn the command
	if (process::spawn(cmd,args,(iswindows)?true:false)==-1) {
		stdoutput.printf("\nsqlr-listener failed to start.\n");
		return false;
	}
	return true;
}


bool startConnection(const char *id, const char *connectionid,
				const char *config, const char *localstatedir,
				bool strace, bool disablecrashhandler) {

	// build command to spawn
	const char	*cmd=NULL;
	uint16_t	i=0;
	const char	*args[15];
	if (strace) {
		cmd="strace";
		args[i++]="strace";
		args[i++]="-ff";
		args[i++]="-o";
	} else {
		if (iswindows) {
			cmd="sqlr-connection.exe";
		} else {
			cmd="sqlr-connection";
 		}
	}
	args[i++]="sqlr-connection";
	args[i++]="-id";
	args[i++]=id;
	if (connectionid) {
		args[i++]="-connectionid";
		args[i++]=connectionid;
	}
	args[i++]="-config";
	args[i++]=config;
	if (charstring::length(localstatedir)) {
		args[i++]="-localstatedir";
		args[i++]=localstatedir;
	}
	if (disablecrashhandler) {
		args[i++]="-disable-crash-handler";
	}
	if (strace) {
		args[i++]="&";
	}
	args[i]=NULL;

	// display command
	stdoutput.printf("  ");
	for (uint16_t index=0; index<i; index++) {
		stdoutput.printf("%s ",args[index]);
	}
	stdoutput.printf("\n");

	// spawn the command
	if (process::spawn(cmd,args,(iswindows)?true:false)==-1) {
		stdoutput.printf("\nsqlr-connection failed to start.\n");
		return false;
	}
	return true;
}

bool startConnections(sqlrconfig *cfgfile,
				const char *id, const char *config,
				const char *localstatedir, bool strace,
				bool disablecrashhandler) {

	// get the connection count and total metric
	linkedlist< connectstringcontainer *>	*connectionlist=
						cfgfile->getConnectStringList();

	// if the metrictotal was 0, start no connections
	if (!cfgfile->getMetricTotal()) {
		return true;
	}

	// if no connections were defined in the config file,
	// start 1 default one
	if (!cfgfile->getConnectionCount()) {
		return !startConnection(id,config,localstatedir,NULL,
						strace,disablecrashhandler);
	}

	// get number of connections
	int32_t	connections=cfgfile->getConnections();

	// start the connections
	connectstringnode	*csn=connectionlist->getFirst();
	connectstringcontainer	*csc;
	int32_t	metric=0;
	int32_t	startup=0;
	int32_t	totalstarted=0;
	bool	done=false;
	while (!done) {

		// get the appropriate connection
		csc=csn->getValue();
	
		// scale the number of each connection to start by 
		// each connection's metric vs the total number of 
		// connections to start up
		metric=csc->getMetric();
		if (metric>0) {
			startup=(int32_t)ceil(
				((double)(metric*connections))/
				((double)cfgfile->getMetricTotal()));
		} else {
			startup=0;
		}

		// keep from starting too many connections
		if (totalstarted+startup>connections) {
			startup=connections-totalstarted;
			done=true;
		}

		stdoutput.printf("\nStarting %d connections to ",startup);
		stdoutput.printf("%s :\n",csc->getConnectionId());

		// fire them up
		for (int32_t i=0; i<startup; i++) {
			if (!startConnection(id,csc->getConnectionId(),
						config,localstatedir,strace,
						disablecrashhandler)) {
				// it's ok if at least 1 connection started up
				return (totalstarted>0 || i>0);
			}
		}

		// have we started enough connections?
		totalstarted=totalstarted+startup;
		if (totalstarted==connections) {
			done=true;
		}

		// next...
		csn=csn->getNext();
	}
	return true;
}

bool startScaler(sqlrconfig *cfgfile, const char *id,
			const char *config, const char *localstatedir,
			bool disablecrashhandler) {

	// don't start the scalar if unless dynamic scaling is enabled
	if (!cfgfile->getDynamicScaling()) {
		return true;
	}

	stdoutput.printf("\nStarting scaler:\n");

	// build command to spawn
	const char	*cmd=NULL;
	if (iswindows) {
		cmd="sqlr-scaler.exe";
	} else {
		cmd="sqlr-scaler";
 	}
	uint16_t	i=0;
	const char	*args[9];
	args[i++]="sqlr-scaler";
	args[i++]="-id";
	args[i++]=id;
	args[i++]="-config";
	args[i++]=config;
	if (charstring::length(localstatedir)) {
		args[i++]="-localstatedir";
		args[i++]=localstatedir;
	}
	if (disablecrashhandler) {
		args[i++]="-disable-crash-handler";
	}
	args[i]=NULL;

	// display command
	stdoutput.printf("  ");
	for (uint16_t index=0; index<i; index++) {
		stdoutput.printf("%s ",args[index]);
	}
	stdoutput.printf("\n");

	// spawn the command
	if (process::spawn(cmd,args,(iswindows)?true:false)==-1) {
		stdoutput.printf("\nsqlr-scaler failed to start.\n");
		return false;
	}
	return true;
}

int main(int argc, const char **argv) {

	#include <version.h>

	sqlrcmdline	cmdl(argc,argv);
	sqlrpaths	sqlrpth(&cmdl);
	sqlrconfig	cfgfile(&sqlrpth);

	// get the command line args
	const char	*localstatedir=sqlrpth.getLocalStateDir();
	bool		strace=cmdl.found("-strace");
	const char	*id=cmdl.getValue("-id");
	const char	*config=sqlrpth.getConfigFile();
	bool		disablecrashhandler=
				cmdl.found("-disable-crash-handler");

	// on Windows, open a new console window and redirect everything to it
	#ifdef _WIN32
	fclose(stdin);
	fclose(stdout);
	fclose(stderr);
	FreeConsole();
	AllocConsole();
	stringbuffer	title;
	title.append("SQL Relay");
	if (id && id[0]) {
		title.append(" - ");
		title.append(id);
	}
	SetConsoleTitle(title.getString());
	*stdin=*(_fdopen(_open_osfhandle(
				(long)GetStdHandle(STD_INPUT_HANDLE),
				_O_TEXT),"r"));
	setvbuf(stdin,NULL,_IONBF,0);
	*stdout=*(_fdopen(_open_osfhandle(
				(long)GetStdHandle(STD_OUTPUT_HANDLE),
				_O_TEXT),"w"));
	setvbuf(stdout,NULL,_IONBF,0);
	*stderr=*(_fdopen(_open_osfhandle(
				(long)GetStdHandle(STD_ERROR_HANDLE),
				_O_TEXT),"w"));
	setvbuf(stderr,NULL,_IONBF,0);
	iswindows=true;
	#else
	iswindows=false;
	#endif

	// get the id
	linkedlist< char * >	ids;
	if (id && id[0]) {
		ids.append(charstring::duplicate(id));
	} else {
		cfgfile.getEnabledIds(config,&ids);
	}

	// start each enabled instance
	int32_t	exitstatus=0;
	for (linkedlistnode< char * > *node=ids.getFirst();
					node; node=node->getNext()) {

		// get the id
		char	*thisid=node->getValue();

		// parse the config file(s) and
		// start listener, connections and scaler
		if (!cfgfile.parse(config,thisid) ||
			!startListener(thisid,
					config,localstatedir,
					disablecrashhandler) ||
			!startConnections(&cfgfile,thisid,
					config,localstatedir,
					strace,disablecrashhandler) ||
			!startScaler(&cfgfile,thisid,
					config,localstatedir,
					disablecrashhandler)) {
			exitstatus=1;
		}

		// clean up
		delete[] thisid;
	}

	// successful exit
	process::exit(exitstatus);
}
