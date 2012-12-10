// Copyright (c) 2000-2005  David Muse
// See the file COPYING for more information

#include <debugfile.h>
#include <rudiments/charstring.h>
#include <rudiments/stringbuffer.h>
#include <rudiments/process.h>
#include <config.h>

#ifdef RUDIMENTS_NAMESPACE
using namespace rudiments;
#endif

debugfile::debugfile() {
	dbgfile=NULL;
	debuglogger=NULL;
	dbgfilename=NULL;
	enabled=false;
}

debugfile::~debugfile() {
	closeDebugFile();
	delete[] dbgfilename;
}

void debugfile::init(const char *name, const char *localstatedir) {

	closeDebugFile();
	delete[] dbgfilename;

	// set the debug file name
	size_t	dbgfilenamelen;

	if (localstatedir[0]) {
		dbgfilenamelen=charstring::length(localstatedir)+
					16+5+charstring::length(name)+20+1;
		dbgfilename=new char[dbgfilenamelen];
		snprintf(dbgfilename,dbgfilenamelen,
					"%s/sqlrelay/debug/sqlr-%s.%ld",
						localstatedir,name,
						(long)process::getProcessId());
	} else {
		dbgfilenamelen=charstring::length(DEBUG_DIR)+5+
					charstring::length(name)+20+1;
		dbgfilename=new char[dbgfilenamelen];
		snprintf(dbgfilename,dbgfilenamelen,
					"%s/sqlr-%s.%ld",DEBUG_DIR,name,
						(long)process::getProcessId());
	}
}

bool debugfile::openDebugFile() {

	// create the debug file
	mode_t	oldumask=process::setFileCreationMask(066);
	dbgfile=new filedestination();
	process::setFileCreationMask(oldumask);

	// open the file
	bool	retval=false;
	if (dbgfile->open(dbgfilename)) {
		printf("Debugging to: %s\n",dbgfilename);
		debuglogger=new logger();
		debuglogger->addLogDestination(dbgfile);
		retval=true;
	} else {
		fprintf(stderr,"Couldn't open debug file: %s\n",dbgfilename);
		if (dbgfile) {
			dbgfile->close();
			delete dbgfile;
			dbgfile=NULL;
		}
	}

	delete[] dbgfilename;
	dbgfilename=NULL;
	return retval;
}

void debugfile::closeDebugFile() {
	if (dbgfile) {
		dbgfile->close();
		delete dbgfile;
		dbgfile=NULL;
		delete debuglogger;
		debuglogger=NULL;
	}
}

void debugfile::enable() {
	enabled=true;
}

void debugfile::disable() {
	enabled=false;
}

bool debugfile::debugEnabled() {
	return enabled;
}

void debugfile::debugPrint(const char *name, int32_t tabs, const char *string) {
	if (!enabled || (!debuglogger && !openDebugFile())) {
		return;
	}
	char	*header=debuglogger->logHeader(name);
	debuglogger->write(header,tabs,string);
	delete[] header;
}

void debugfile::debugPrint(const char *name, int32_t tabs, int32_t number) {
	if (!enabled || (!debuglogger && !openDebugFile())) {
		return;
	}
	char	*header=debuglogger->logHeader(name);
	debuglogger->write(header,tabs,number);
	delete[] header;
}

void debugfile::debugPrint(const char *name, int32_t tabs, uint32_t number) {
	debugPrint(name,tabs,(int32_t)number);
}

void debugfile::debugPrint(const char *name, int32_t tabs, double number) {
	if (!enabled || (!debuglogger && !openDebugFile())) {
		return;
	}
	char	*header=debuglogger->logHeader(name);
	debuglogger->write(header,tabs,number);
	delete[] header;
}

void debugfile::debugPrintBlob(const char *blob, uint32_t length) {

	if (!enabled || (!debuglogger && !openDebugFile())) {
		return;
	}

	// write printable characters from the blob, for all other characters,
	// print a period instead, also print a carriage return every 80 columns
	stringbuffer	*debugstr=new stringbuffer();
	debugstr->append('\n');
	int	column=0;
	for (uint32_t i=0; i<length; i++) {
		if (blob[i]>=' ' && blob[i]<='~') {
			debugstr->append(blob[i]);
		} else {
			debugstr->append('.');
		}
		column++;
		if (column==80) {
			debugstr->append('\n');
			column=0;
		}
	}
	debugstr->append('\n');
	char	*header=logger::logHeader("connection");
	debuglogger->write(header,0,debugstr->getString());
	delete[] header;
	delete debugstr;
}

void debugfile::debugPrintClob(const char *clob, uint32_t length) {

	if (!enabled || (!debuglogger && !openDebugFile())) {
		return;
	}

	// write printable characters from the clob, for NULl characters,
	// print a \0 instead
	stringbuffer	*debugstr=new stringbuffer();
	debugstr->append('\n');
	for (uint32_t i=0; i<length; i++) {
		if (clob[i]=='\0') {
			debugstr->append("\\0");
		} else {
			debugstr->append(clob[i]);
		}
	}
	debugstr->append('\n');
	char	*header=logger::logHeader("connection");
	debuglogger->write(header,0,debugstr->getString());
	delete[] header;
	delete debugstr;
}
