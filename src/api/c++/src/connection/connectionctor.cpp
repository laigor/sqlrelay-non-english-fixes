// Copyright (c) 1999-2001  David Muse
// See the file COPYING for more information

#include <sqlrelay/sqlrclient.h>
#include <rudiments/environment.h>

sqlrconnection::sqlrconnection(const char *server, uint16_t port,
					const char *socket,
					const char *user, const char *password, 
					int32_t retrytime, int32_t tries,
					bool copyreferences) {
	init(server,port,socket,user,password,retrytime,tries,copyreferences);
}

sqlrconnection::sqlrconnection(const char *server, uint16_t port,
					const char *socket,
					const char *user, const char *password, 
					int32_t retrytime, int32_t tries) {
	init(server,port,socket,user,password,retrytime,tries,false);
}

void sqlrconnection::init(const char *server, uint16_t port,
					const char *socket,
					const char *user, const char *password, 
					int32_t retrytime, int32_t tries,
					bool copyreferences) {

	copyrefs=copyreferences;

	// retry reads if they get interrupted by signals
	ucs.translateByteOrder();
	ucs.retryInterruptedReads();
	ics.retryInterruptedReads();
	cs=&ucs;

	// connection
	this->server=(copyrefs)?
			charstring::duplicate(server):
			(char *)server;
	listenerinetport=port;
	listenerunixport=(copyrefs)?
				charstring::duplicate(socket):
				(char *)socket;
	this->retrytime=retrytime;
	this->tries=tries;

	// initialize timeouts
	setTimeoutFromEnv("SQLR_CLIENT_CONNECT_TIMEOUT",
				&connecttimeoutsec,&connecttimeoutusec);
	setTimeoutFromEnv("SQLR_CLIENT_AUTHENTICATION_TIMEOUT",
				&authtimeoutsec,&authtimeoutusec);
	setTimeoutFromEnv("SQLR_CLIENT_RESPONSE_TIMEOUT",
				&responsetimeoutsec,&responsetimeoutusec);

	// authentication
	this->user=(copyrefs)?
			charstring::duplicate(user):
			(char *)user;
	this->password=(copyrefs)?
			charstring::duplicate(password):
			(char *)password;
	userlen=charstring::length(user);
	passwordlen=charstring::length(password);

	// database id
	id=NULL;

	// db version
	dbversion=NULL;

	// db host name
	dbhostname=NULL;

	// db ip address
	dbipaddress=NULL;

	// server version
	serverversion=NULL;

	// current database name
	currentdbname=NULL;

	// bind format
	bindformat=NULL;

	// client info
	clientinfo=NULL;
	clientinfolen=0;

	// session state
	connected=false;
	clearSessionFlags();

	// debug print function
	printfunction=NULL;

	// enable/disable debug
	const char	*sqlrdebug=environment::getValue("SQLRDEBUG");
	if (!charstring::length(sqlrdebug)) {
		sqlrdebug=environment::getValue("SQLR_CLIENT_DEBUG");
	}
	debug=(charstring::length(sqlrdebug) &&
			charstring::compareIgnoringCase(sqlrdebug,"OFF"));
	webdebug=-1;

	// copy references, delete cursors flags
	copyrefs=false;

	// error
	errorno=0;
	error=NULL;

	// cursor list
	firstcursor=NULL;
	lastcursor=NULL;
}

void sqlrconnection::setConnectTimeout(int32_t timeoutsec,
					int32_t timeoutusec) {
	connecttimeoutsec=timeoutsec;
	connecttimeoutusec=timeoutusec;
}

void sqlrconnection::setAuthenticationTimeout(int32_t timeoutsec,
						int32_t timeoutusec) {
	authtimeoutsec=timeoutsec;
	authtimeoutusec=timeoutusec;
}

void sqlrconnection::setResponseTimeout(int32_t timeoutsec,
						int32_t timeoutusec) {
	responsetimeoutsec=timeoutsec;
	responsetimeoutusec=timeoutusec;
}

void sqlrconnection::setTimeoutFromEnv(const char *var,
					int32_t *timeoutsec,
					int32_t *timeoutusec) {
	const char	*timeout=environment::getValue(var);
	if (charstring::isNumber(timeout)) {
		*timeoutsec=charstring::toInteger(timeout);
		long double	dbl=charstring::toFloat(timeout);
		dbl=dbl-(long double)(*timeoutsec);
		*timeoutusec=(int32_t)(dbl*1000000.0);
	} else {
		*timeoutsec=-1;
		*timeoutusec=-1;
	}
}
