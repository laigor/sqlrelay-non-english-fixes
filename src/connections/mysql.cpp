// Copyright (c) 1999-2015  David Muse
// See the file COPYING for more information

#include <sqlrelay/sqlrserver.h>
#include <rudiments/charstring.h>
#include <rudiments/bytestring.h>
#include <rudiments/regularexpression.h>

#include <defines.h>
#include <datatypes.h>
#include <config.h>

#include <mysql.h>
#if defined(MYSQL_VERSION_ID) && MYSQL_VERSION_ID>=32200
	#include <errmsg.h>
#endif

#ifndef TRUE
#define TRUE (1)
#endif

#ifndef FALSE
#define FALSE (0)
#endif

// mysql_change_user() once worked, but it hasn't for a long time.  It's not
// clear why, but the problem appears to be on the server-side.  I'm disabling
// it until I can figure out a workaround.
#undef HAVE_MYSQL_CHANGE_USER

#define MAX_SELECT_LIST_SIZE	256
#ifdef HAVE_MYSQL_STMT_PREPARE
	#define MAX_ITEM_BUFFER_SIZE	32768
#endif

class mysqlconnection;

class SQLRSERVER_DLLSPEC mysqlcursor : public sqlrservercursor {
	friend class mysqlconnection;
	private:
				mysqlcursor(sqlrserverconnection *conn,
							uint16_t id);
				~mysqlcursor();
#ifdef HAVE_MYSQL_STMT_PREPARE
		void		allocateResultSetBuffers(
						int32_t selectlistsize,
						int32_t itembuffersize);
		void		deallocateResultSetBuffers();
		bool		open();
		bool		close();
#endif
		bool		prepareQuery(const char *query,
						uint32_t length);
		bool		supportsNativeBinds(const char *query,
							uint32_t length);
#ifdef HAVE_MYSQL_STMT_PREPARE
		bool		inputBind(const char *variable, 
						uint16_t variablesize,
						const char *value, 
						uint32_t valuesize,
						int16_t *isnull);
		bool		inputBind(const char *variable, 
						uint16_t variablesize,
						int64_t *value);
		bool		inputBind(const char *variable, 
						uint16_t variablesize,
						double *value,
						uint32_t precision,
						uint32_t scale);
		bool		inputBind(const char *variable,
						uint16_t variablesize,
						int64_t year,
						int16_t month,
						int16_t day,
						int16_t hour,
						int16_t minute,
						int16_t second,
						int32_t microsecond,
						const char *tz,
						bool isnegative,
						char *buffer,
						uint16_t buffersize,
						int16_t *isnull);
		bool		inputBindBlob(const char *variable, 
						uint16_t variablesize,
						const char *value, 
						uint32_t valuesize,
						int16_t *isnull);
		bool		inputBindClob(const char *variable, 
						uint16_t variablesize,
						const char *value, 
						uint32_t valuesize,
						int16_t *isnull);
#endif
		bool		executeQuery(const char *query,
						uint32_t length);
#ifdef HAVE_MYSQL_COMMIT
		bool		queryIsNotSelect();
#endif
		void		errorMessage(char *errorbuffer,
						uint32_t errorbufferlength,
						uint32_t *errorlength,
						int64_t	*errorcode,
						bool *liveconnection);
		bool		knowsRowCount();
		uint64_t	rowCount();
		uint64_t	affectedRows();
		uint32_t	colCount();
		const char	*getColumnName(uint32_t col);
		uint16_t	getColumnType(uint32_t col);
		uint32_t	getColumnLength(uint32_t col);
		uint32_t	getColumnPrecision(uint32_t col);
		uint32_t	getColumnScale(uint32_t col);
		uint16_t	getColumnIsNullable(uint32_t col);
		uint16_t	getColumnIsPrimaryKey(uint32_t col);
		uint16_t	getColumnIsUnique(uint32_t col);
		uint16_t	getColumnIsPartOfKey(uint32_t col);
		uint16_t	getColumnIsUnsigned(uint32_t col);
		uint16_t	getColumnIsZeroFilled(uint32_t col);
		uint16_t	getColumnIsBinary(uint32_t col);
		uint16_t	getColumnIsAutoIncrement(uint32_t col);
		bool		noRowsToReturn();
		bool		fetchRow();
		void		getField(uint32_t col,
					const char **field,
					uint64_t *fieldlength,
					bool *blob,
					bool *null);

#ifdef HAVE_MYSQL_STMT_PREPARE
		bool		getLobFieldLength(uint32_t col,
						uint64_t *length);
		bool		getLobFieldSegment(uint32_t col,
						char *buffer,
						uint64_t buffersize,
						uint64_t offset,
						uint64_t charstoread,
						uint64_t *charsread);
		void		closeLobField(uint32_t col);
#endif

		void		closeResultSet();

		MYSQL_RES	*mysqlresult;
		MYSQL_FIELD	**mysqlfields;
		uint32_t	mysqlfieldindex;
		unsigned int	ncols;
		my_ulonglong	nrows;
		my_ulonglong	affectedrows;
		int		queryresult;

#ifdef HAVE_MYSQL_STMT_PREPARE
		MYSQL_STMT	*stmt;
		bool		stmtfreeresult;

		MYSQL_BIND	*fieldbind;
		char		*field;
		my_bool		*isnull;
		unsigned long	*fieldlength;

		uint16_t	bindcount;
		bool		boundvariables;
		MYSQL_BIND	*bind;
		unsigned long	*bindvaluesize;

		MYSQL_BIND	lobfield;

		bool		usestmtprepare;
		bool		bindformaterror;

		regularexpression	unsupportedbystmt;
#endif
		MYSQL_ROW	mysqlrow;
		unsigned long	*mysqlrowlengths;

		mysqlconnection	*mysqlconn;
};

class SQLRSERVER_DLLSPEC mysqlconnection : public sqlrserverconnection {
	friend class mysqlcursor;
	public:
				mysqlconnection(sqlrservercontroller *cont);
				~mysqlconnection();
	private:
		void		handleConnectString();
		bool		logIn(const char **error, const char **warning);
		sqlrservercursor	*newCursor(uint16_t id);
		void		deleteCursor(sqlrservercursor *curs);
		void		logOut();
		bool		isTransactional();
#ifdef HAVE_MYSQL_PING
		bool		ping();
#endif
		const char	*identify();
		const char	*dbVersion();
		const char	*dbHostName();
		const char	*bindFormat();
		const char	*getDatabaseListQuery(bool wild);
		const char	*getTableListQuery(bool wild);
		const char	*getColumnListQuery(
						const char *table, bool wild);
		const char	*selectDatabaseQuery();
		const char	*getCurrentDatabaseQuery();
		const char	*setIsolationLevelQuery();
		bool		getLastInsertId(uint64_t *id);
		bool		autoCommitOn();
		bool		autoCommitOff();
		bool		commit();
		bool		rollback();
		void		errorMessage(char *errorbuffer,
						uint32_t errorbufferlength,
						uint32_t *errorlength,
						int64_t	*errorcode,
						bool *liveconnection);
#ifdef HAVE_MYSQL_STMT_PREPARE
		short		nonNullBindValue();
		short		nullBindValue();
#endif
		void		endSession();

#if MYSQL_VERSION_ID<32200
		MYSQL	mysql;
#endif
		MYSQL	*mysqlptr;

		bool	connected;

		const char	*db;
		const char	*host;
		const char	*port;
		const char	*socket;
		const char	*charset;
		const char	*sslkey;
		const char	*sslcert;
		const char	*sslca;
		const char	*sslcapath;
		const char	*sslcipher;
		bool		foundrows;
		bool		ignorespace;
		int32_t		maxselectlistsize;
#ifdef HAVE_MYSQL_STMT_PREPARE
		int32_t		maxitembuffersize;
#endif

		const char	*identity;

		char	*dbversion;
		char	*dbhostname;

		static const my_bool	mytrue;
		static const my_bool	myfalse;

		bool		firstquery;

		stringbuffer	loginerror;
};

extern "C" {
	SQLRSERVER_DLLSPEC sqlrserverconnection *new_mysqlconnection(
						sqlrservercontroller *cont) {
		return new mysqlconnection(cont);
	}
}

const my_bool	mysqlconnection::mytrue=TRUE;
const my_bool	mysqlconnection::myfalse=FALSE;

mysqlconnection::mysqlconnection(sqlrservercontroller *cont) :
					sqlrserverconnection(cont) {
	connected=false;
	dbversion=NULL;
	dbhostname=NULL;

	// start this at false because we don't need to do a commit before
	// the first query when we very first start up
	firstquery=false;

	maxselectlistsize=MAX_SELECT_LIST_SIZE;
#ifdef HAVE_MYSQL_STMT_PREPARE
	maxitembuffersize=MAX_ITEM_BUFFER_SIZE;
#endif

	identity=NULL;

	mysqlptr=NULL;
}

mysqlconnection::~mysqlconnection() {
	delete[] dbversion;
	delete[] dbhostname;
}

void mysqlconnection::handleConnectString() {
	cont->setUser(cont->getConnectStringValue("user"));
	cont->setPassword(cont->getConnectStringValue("password"));
	db=cont->getConnectStringValue("db");
	host=cont->getConnectStringValue("host");
	port=cont->getConnectStringValue("port");
	socket=cont->getConnectStringValue("socket");
	charset=cont->getConnectStringValue("charset");
	sslkey=cont->getConnectStringValue("sslkey");
	sslcert=cont->getConnectStringValue("sslcert");
	sslca=cont->getConnectStringValue("sslca");
	sslcapath=cont->getConnectStringValue("sslcapath");
	sslcipher=cont->getConnectStringValue("sslcipher");
	if (!charstring::compare(
			cont->getConnectStringValue("fakebinds"),"yes")) {
		cont->fakeInputBinds();
	}
	foundrows=!charstring::compare(
			cont->getConnectStringValue("foundrows"),"yes");
	ignorespace=!charstring::compare(
			cont->getConnectStringValue("ignorespace"),"yes");

	maxselectlistsize=charstring::toInteger(
			cont->getConnectStringValue("maxselectlistsize"));
	if (!maxselectlistsize) {
		maxselectlistsize=MAX_SELECT_LIST_SIZE;
	}

#ifdef HAVE_MYSQL_STMT_PREPARE
	maxitembuffersize=charstring::toInteger(
			cont->getConnectStringValue("maxitembuffersize"));
	if (!maxitembuffersize) {
		maxitembuffersize=MAX_ITEM_BUFFER_SIZE;
	}
#endif

	identity=cont->getConnectStringValue("identity");
}

bool mysqlconnection::logIn(const char **error, const char **warning) {

	// Handle host.
	// For really old versions of mysql, a NULL host indicates that the
	// unix socket should be used.  There's no way to specify what unix
	// socket or inet port to connect to, those values are hardcoded
	// into the client library.
	// For some newer versions, a NULL host causes problems, but an empty
	// string is safe.
#ifdef HAVE_MYSQL_REAL_CONNECT_FOR_SURE
	const char	*hostval=(!charstring::isNullOrEmpty(host))?host:"";
#else
	const char	*hostval=(!charstring::isNullOrEmpty(host))?host:NULL;
#endif

	// Handle db.
	const char	*dbval=(!charstring::isNullOrEmpty(db))?db:"";
	
	// log in
	const char	*user=cont->getUser();
	const char	*password=cont->getPassword();
#ifdef HAVE_MYSQL_REAL_CONNECT_FOR_SURE
	// Handle port and socket.
	int		portval=
			(!charstring::isNullOrEmpty(port))?
					charstring::toInteger(port):0;
	const char	*socketval=
			(!charstring::isNullOrEmpty(socket))?socket:NULL;
	unsigned long	clientflag=0;
	#ifdef CLIENT_MULTI_STATEMENTS
	clientflag|=CLIENT_MULTI_STATEMENTS;
	#endif
	#ifdef CLIENT_FOUND_ROWS
	if (foundrows) {
		clientflag|=CLIENT_FOUND_ROWS;
	}
	#endif
	#ifdef CLIENT_IGNORE_SPACE
	if (ignorespace) {
		clientflag|=CLIENT_IGNORE_SPACE;
	}
	#endif
	#if MYSQL_VERSION_ID>=32200
	// initialize database connection structure
	mysqlptr=mysql_init(NULL);
	if (!mysqlptr) {
		*error="mysql_init failed";
		return false;
	}
	#ifdef HAVE_MYSQL_SSL_SET
	mysql_ssl_set(mysqlptr,sslkey,sslcert,sslca,sslcapath,sslcipher);
	#endif
	if (!mysql_real_connect(mysqlptr,hostval,user,password,dbval,
					portval,socketval,clientflag)) {
	#else
	mysqlptr=&mysql;
	if (!mysql_real_connect(mysqlptr,hostval,user,password,
					portval,socketval,clientflag)) {
	#endif
		loginerror.clear();
		loginerror.append("mysql_real_connect failed: ");
		loginerror.append(mysql_error(mysqlptr));
		*error=loginerror.getString();
#else
	if (!mysql_connect(mysqlptr,hostval,user,password)) {
		loginerror.clear();
		loginerror.append("mysql_connect failed: ");
		loginerror.append(mysql_error(mysqlptr));
		*error=loginerror.getString();
#endif
		logOut();
		return false;
	}

#ifdef HAVE_MYSQL_OPT_RECONNECT
	// Enable autoreconnect in the C api
	// (ordinarily mysql_options should be called before mysql_connect,
	// but not for this option)
	mysql_options(mysqlptr,MYSQL_OPT_RECONNECT,&mytrue);
#endif

#ifdef HAVE_MYSQL_REPORT_DATA_TRUNCATION
	// The way this code works, if mysql_stmt_fetch returns any error,
	// then the fetch fails and no more rows are returned.  At some point,
	// MySQL started reporting data truncation as an error.  Disable this
	// though, we'd rather get the truncated data and keep fetching rows
	// rather than stopping all fetching at the point that the truncation
	// occurs.
	mysql_options(mysqlptr,MYSQL_REPORT_DATA_TRUNCATION,&myfalse);
#endif

#ifdef MYSQL_SELECT_DB
	if (mysql_select_db(mysqlptr,dbval)) {
		loginerror.clear();
		loginerror.append("mysql_select_db failed: ");
		loginerror.append(mysql_error(mysqlptr));
		*error=loginerror.getString();
		logOut();
		return false;
	}
#endif
	connected=true;

#ifdef HAVE_MYSQL_STMT_PREPARE
	// fake binds when connected to older servers
#ifdef HAVE_MYSQL_GET_SERVER_VERSION
	if (mysql_get_server_version(mysqlptr)<40102) {
		cont->fakeInputBinds();
	}
#else
	char		**list;
	uint64_t	listlen;
	charstring::split(mysql_get_server_info(mysqlptr),
				".",true,&list,&listlen);

	if (listlen==3) {
		uint64_t	major=charstring::toUnsignedInteger(list[0]);
		uint64_t	minor=charstring::toUnsignedInteger(list[1]);
		uint64_t	patch=charstring::toUnsignedInteger(list[2]);
		if (major>4 || (major==4 && minor>1) ||
				(major==4 && minor==1 && patch>=2)) {
			cont->fakeInputBinds();
		} 
		for (uint64_t index=0; index<listlen; index++) {
			delete[] list[index];
		}
		delete[] list;
	}
#endif

	// get the db host name
	const char	*hostinfo=mysql_get_host_info(mysqlptr);
	const char	*space=charstring::findFirst(hostinfo,' ');
	if (space) {
		dbhostname=charstring::duplicate(hostinfo,space-hostinfo);
	} else {
		dbhostname=charstring::duplicate(hostinfo);
	}

#endif

#ifdef HAVE_MYSQL_SET_CHARACTER_SET
	// set the character set
	if (charstring::length(charset)) {
		mysql_set_character_set(mysqlptr,charset);
	}
#endif

	return true;
}

sqlrservercursor *mysqlconnection::newCursor(uint16_t id) {
	return (sqlrservercursor *)new mysqlcursor((sqlrserverconnection *)this,id);
}

void mysqlconnection::deleteCursor(sqlrservercursor *curs) {
	delete (mysqlcursor *)curs;
}

void mysqlconnection::logOut() {
	connected=false;
	mysql_close(mysqlptr);
}

#ifdef HAVE_MYSQL_PING
bool mysqlconnection::ping() {
	return (!mysql_ping(mysqlptr))?true:false;
}
#endif

const char *mysqlconnection::identify() {
	return (identity)?identity:"mysql";
}

const char *mysqlconnection::dbVersion() {
	delete[] dbversion;
	dbversion=charstring::duplicate(mysql_get_server_info(mysqlptr));
	return dbversion;
}

const char *mysqlconnection::dbHostName() {
	return dbhostname;
}

const char *mysqlconnection::bindFormat() {
#ifdef HAVE_MYSQL_STMT_PREPARE
	return "?";
#else
	return sqlrserverconnection::bindFormat();
#endif
}

const char *mysqlconnection::getDatabaseListQuery(bool wild) {
	return (wild)?"select "
			"	schema_name, "
			"	NULL "
			"from "
			"	information_schema.schemata "
			"where "
			"	schema_name like '%s'"
			:
			"select "
			"	schema_name, "
			"	NULL "
			"from "
			"	information_schema.schemata";
}

const char *mysqlconnection::getTableListQuery(bool wild) {
	return (wild)?"select "
			"	table_name, "
			"	'TABLE', "
			"	NULL "
			"from "
			"	information_schema.tables "
			"where "
			"	table_type='BASE TABLE' "
			"	and "
			"	table_name like '%s'"
			:
			"select "
			"	table_name, "
			"	'TABLE', "
			"	NULL "
			"from "
			"	information_schema.tables "
			"where "
			"	table_type='BASE TABLE'";
}

const char *mysqlconnection::getColumnListQuery(
					const char *table, bool wild) {
	return (wild)?"select "
			"	column_name, "
			"	data_type, "
			"	character_maximum_length, "
			"	numeric_precision, "
			"	numeric_scale, "
			"	is_nullable, "
			"	column_key, "
			"	column_default, "
			"	extra, "
			"	NULL "
			"from "
			"	information_schema.columns "
			"where "
			"	table_name='%s' "
			"	and "
			"	column_name like '%s'"
			:
			"select "
			"	column_name, "
			"	data_type, "
			"	character_maximum_length, "
			"	numeric_precision, "
			"	numeric_scale, "
			"	is_nullable, "
			"	column_key, "
			"	column_default, "
			"	extra, "
			"	NULL "
			"from "
			"	information_schema.columns "
			"where "
			"	table_name='%s' ";
}

const char *mysqlconnection::selectDatabaseQuery() {
	return "use %s";
}

const char *mysqlconnection::getCurrentDatabaseQuery() {
	return "select database()";
}

const char *mysqlconnection::setIsolationLevelQuery() {
	return "set session transaction isolation level %s";
}

bool mysqlconnection::getLastInsertId(uint64_t *id) {
	*id=mysql_insert_id(mysqlptr);
	return true;
}

bool mysqlconnection::isTransactional() {
	return true;
}

bool mysqlconnection::autoCommitOn() {
#ifdef HAVE_MYSQL_AUTOCOMMIT
	return !mysql_autocommit(mysqlptr,true);
#else
	// do nothing
	return true;
#endif
}

bool mysqlconnection::autoCommitOff() {
#ifdef HAVE_MYSQL_AUTOCOMMIT
	return !mysql_autocommit(mysqlptr,false);
#else
	// do nothing
	return true;
#endif
}

bool mysqlconnection::commit() {
#ifdef HAVE_MYSQL_COMMIT
	return !mysql_commit(mysqlptr);
#else
	// do nothing
	return true;
#endif
}

bool mysqlconnection::rollback() {
#ifdef HAVE_MYSQL_ROLLBACK
	return !mysql_rollback(mysqlptr);
#else
	// do nothing
	return true;
#endif
}

void mysqlconnection::errorMessage(char *errorbuffer,
					uint32_t errorbufferlength,
					uint32_t *errorlength,
					int64_t *errorcode,
					bool *liveconnection) {
	const char	*errorstring=mysql_error(mysqlptr);
	*errorlength=charstring::length(errorstring);
	charstring::safeCopy(errorbuffer,errorbufferlength,
					errorstring,*errorlength);
	*errorcode=mysql_errno(mysqlptr);
	*liveconnection=(!charstring::compare(errorstring,"") ||
		!charstring::compareIgnoringCase(errorstring,
				"mysql server has gone away") ||
		!charstring::compareIgnoringCase(errorstring,
				"Can't connect to local MySQL",28) ||
		!charstring::compareIgnoringCase(errorstring,
				"Can't connect to MySQL",22) /*||
		!charstring::compareIgnoringCase(errorstring,
			"Lost connection to MySQL server during query")*/);
}

#ifdef HAVE_MYSQL_STMT_PREPARE
short mysqlconnection::nonNullBindValue() {
	return 0;
}

short mysqlconnection::nullBindValue() {
	return 1;
}
#endif

void mysqlconnection::endSession() {
	firstquery=true;
}

mysqlcursor::mysqlcursor(sqlrserverconnection *conn, uint16_t id) :
						sqlrservercursor(conn,id) {
	mysqlconn=(mysqlconnection *)conn;
	mysqlresult=NULL;

	mysqlfields=new MYSQL_FIELD *[mysqlconn->maxselectlistsize];

#ifdef HAVE_MYSQL_STMT_PREPARE
	stmt=NULL;
	stmtfreeresult=false;

	bindcount=0;
	boundvariables=false;

	uint16_t	maxbindcount=conn->cont->getConfig()->getMaxBindCount();
	bind=new MYSQL_BIND[maxbindcount];
	bindvaluesize=new unsigned long[maxbindcount];
	bytestring::zero(bind,maxbindcount*sizeof(MYSQL_BIND));

	usestmtprepare=true;
	bindformaterror=false;
	unsupportedbystmt.compile(
			"^[ 	\r\n]*(("
				"create|CREATE|"
				"drop|DROP|"
				"procedure|PROCEDURE|"
				"function|FUNCTION|"
				"use|USE|"
				"call|CALL|"
				"start|START|"
				"check|CHECK|"
				"repair|REPAIR|"
				"savepoint|SAVEPOINT|"
				"release|RELEASE|"
				"connect|CONNECT|"
				"lock|LOCK|"
				"unlock|UNLOCK|"
				"show|SHOW"
			")[ 	\r\n]+)|"
			"(("
				"begin|BEGIN|"
				"rollback|ROLLBACK"
			")[ 	\r\n]*)");
	unsupportedbystmt.study();

	allocateResultSetBuffers(mysqlconn->maxselectlistsize,
					mysqlconn->maxitembuffersize);
#endif
}

mysqlcursor::~mysqlcursor() {
#ifdef HAVE_MYSQL_STMT_PREPARE
	if (stmtfreeresult) {
		mysql_stmt_free_result(stmt);
	}
	if (mysqlresult) {
		mysql_free_result(mysqlresult);
	}
	delete[] bind;
	delete[] bindvaluesize;

	deallocateResultSetBuffers();
#endif

	delete[] mysqlfields;
}

#ifdef HAVE_MYSQL_STMT_PREPARE
void mysqlcursor::allocateResultSetBuffers(int32_t selectlistsize,
						int32_t itembuffersize) {
	if (selectlistsize<1) {
		fieldbind=NULL;
		field=NULL;
		isnull=NULL;
		fieldlength=NULL;
	} else {
		fieldbind=new MYSQL_BIND[selectlistsize];
		field=new char[selectlistsize*itembuffersize];
		isnull=new my_bool[selectlistsize];
		fieldlength=new unsigned long[selectlistsize];
		bytestring::zero(fieldbind,selectlistsize*sizeof(MYSQL_BIND));
		for (unsigned short index=0; index<selectlistsize; index++) {
			fieldbind[index].buffer_type=MYSQL_TYPE_STRING;
			fieldbind[index].buffer=&field[index*itembuffersize];
			fieldbind[index].buffer_length=itembuffersize;
			fieldbind[index].is_null=&isnull[index];
			fieldbind[index].length=&fieldlength[index];
		}
	}

	bytestring::zero(&lobfield,sizeof(MYSQL_BIND));
	lobfield.buffer_type=MYSQL_TYPE_STRING;
}

void mysqlcursor::deallocateResultSetBuffers() {
	delete[] fieldbind;
	delete[] field;
	delete[] isnull;
	delete[] fieldlength;
}

bool mysqlcursor::open() {
	stmt=mysql_stmt_init(mysqlconn->mysqlptr);
	return true;
}
#endif

#ifdef HAVE_MYSQL_STMT_PREPARE
bool mysqlcursor::close() {
	mysql_stmt_close(stmt);
	return true;
}
#endif

bool mysqlcursor::prepareQuery(const char *query, uint32_t length) {

	// initialize column count
	ncols=0;

	// if this if the first query of the session, do a commit first,
	// doing this will refresh this connection with any data committed
	// by other connections, which is what would happen if a new client
	// connected directly to mysql
	if (mysqlconn->firstquery) {
		mysqlconn->commit();
		mysqlconn->firstquery=false;
	}

#ifdef HAVE_MYSQL_STMT_PREPARE

	// reset the bind format error flag
	bindformaterror=false;

	// can't use stmt API to run a couple of types of queries as of 5.0
	// (This call is a little redundant though...  the sqlrservercontroller
	// calls supportsNativeBinds for each query to see if it needs to
	// fake binds.  Unfortunately it doesn't call it for things like
	// pings or "use xxx" or other internal queries.  It might be good
	// to sort all of that out at some point.)
	if (!supportsNativeBinds(query,length)) {
		return true;
	}

	// store inbindcount here, otherwise if rebinding/reexecution occurs and
	// the client tries to bind more variables than were defined when the
	// query was prepared, it would cause the inputBind methods to attempt
	// to address beyond the end of the various arrays
	bindcount=getInputBindCount();

	// reset bound variables flag
	boundvariables=false;

	// prepare the statement
	if (mysql_stmt_prepare(stmt,query,length)) {
		return false;
	}

	stmtfreeresult=true;

	// get the column count
	ncols=mysql_stmt_field_count(stmt);
	if (ncols>mysqlconn->maxselectlistsize &&
		mysqlconn->maxselectlistsize!=-1) {
		// mysql_stmt_bind_result expects:
		// "the array (fieldbind) to contain one element for
		// each colun of the result set."
		// If there isn't, then mysql_stmt_bind_result will
		// run off the end of the array, wreaking havoc.
		// So, bail with an error if we don't have enough
		// columns.
		stringbuffer	err;
		err.append(SQLR_ERROR_MAXSELECTLISTSIZETOOSMALL_STRING);
		err.append(" (")->append(mysqlconn->maxselectlistsize);
		err.append('<')->append(ncols)->append(')');
		setError(err.getString(),
			SQLR_ERROR_MAXSELECTLISTSIZETOOSMALL,true);
		return false;
	}

	// get the metadata
	mysqlresult=NULL;
	if (ncols) {
		mysqlresult=mysql_stmt_result_metadata(stmt);
	}

	// bind the fields
	if (ncols && mysql_stmt_bind_result(stmt,fieldbind)) {
		return false;
	}
#endif

	return true;
}

bool mysqlcursor::supportsNativeBinds(const char *query, uint32_t length) {
#ifdef HAVE_MYSQL_STMT_PREPARE
	usestmtprepare=!unsupportedbystmt.match(query);
	return usestmtprepare;
#else
	return false;
#endif
}

#ifdef HAVE_MYSQL_STMT_PREPARE
bool mysqlcursor::inputBind(const char *variable, 
				uint16_t variablesize,
				const char *value, 
				uint32_t valuesize,
				int16_t *isnull) {

	if (!usestmtprepare) {
		return true;
	}

	// "variable" should be something like :1,:2,:3, etc.
	// If it's something like :var1,:var2,:var3, etc. then it'll be
	// converted to 0.  1 will be subtracted and after the cast it will
	// be converted to 65535 and will cause the if below to fail.
	uint16_t	pos=charstring::toInteger(variable+1)-1;

	// don't attempt to bind beyond the number of
	// variables defined when the query was prepared
	if (pos>bindcount) {
		bindformaterror=true;
		return false;
	}

	bindvaluesize[pos]=valuesize;

	if (*isnull) {
		bind[pos].buffer_type=MYSQL_TYPE_NULL;
		bind[pos].buffer=(void *)NULL;
		bind[pos].buffer_length=0;
		bind[pos].length=0;
	} else {
		bind[pos].buffer_type=MYSQL_TYPE_STRING;
		bind[pos].buffer=(void *)value;
		bind[pos].buffer_length=valuesize;
		bind[pos].length=&bindvaluesize[pos];
	}
	bind[pos].is_null=(my_bool *)isnull;
	boundvariables=true;

	return true;
}

bool mysqlcursor::inputBind(const char *variable, 
				uint16_t variablesize,
				int64_t *value) {

	if (!usestmtprepare) {
		return true;
	}

	// "variable" should be something like :1,:2,:3, etc.
	// If it's something like :var1,:var2,:var3, etc. then it'll be
	// converted to 0.  1 will be subtracted and after the cast it will
	// be converted to 65535 and will cause the if below to fail.
	uint16_t	pos=charstring::toInteger(variable+1)-1;

	// don't attempt to bind beyond the number of
	// variables defined when the query was prepared
	if (pos>bindcount) {
		bindformaterror=true;
		return false;
	}

	bindvaluesize[pos]=sizeof(int64_t);

	bind[pos].buffer_type=MYSQL_TYPE_LONGLONG;
	bind[pos].buffer=(void *)value;
	bind[pos].buffer_length=sizeof(int64_t);
	bind[pos].length=&bindvaluesize[pos];
	bind[pos].is_null=(my_bool *)&(mysqlconn->myfalse);
	boundvariables=true;

	return true;
}

bool mysqlcursor::inputBind(const char *variable, 
				uint16_t variablesize,
				double *value,
				uint32_t precision,
				uint32_t scale) {

	if (!usestmtprepare) {
		return true;
	}

	// "variable" should be something like :1,:2,:3, etc.
	// If it's something like :var1,:var2,:var3, etc. then it'll be
	// converted to 0.  1 will be subtracted and after the cast it will
	// be converted to 65535 and will cause the if below to fail.
	uint16_t	pos=charstring::toInteger(variable+1)-1;

	// don't attempt to bind beyond the number of
	// variables defined when the query was prepared
	if (pos>bindcount) {
		bindformaterror=true;
		return false;
	}

	bindvaluesize[pos]=sizeof(double);

	bind[pos].buffer_type=MYSQL_TYPE_DOUBLE;
	bind[pos].buffer=(void *)value;
	bind[pos].buffer_length=sizeof(double);
	bind[pos].length=&bindvaluesize[pos];
	bind[pos].is_null=(my_bool *)&(mysqlconn->myfalse);
	boundvariables=true;

	return true;
}

bool mysqlcursor::inputBind(const char *variable,
				uint16_t variablesize,
				int64_t year,
				int16_t month,
				int16_t day,
				int16_t hour,
				int16_t minute,
				int16_t second,
				int32_t microsecond,
				const char *tz,
				bool isnegative,
				char *buffer,
				uint16_t buffersize,
				int16_t *isnull) {

	if (!usestmtprepare) {
		return true;
	}

	// "variable" should be something like :1,:2,:3, etc.
	// If it's something like :var1,:var2,:var3, etc. then it'll be
	// converted to 0.  1 will be subtracted and after the cast it will
	// be converted to 65535 and will cause the if below to fail.
	uint16_t	pos=charstring::toInteger(variable+1)-1;

	// don't attempt to bind beyond the number of
	// variables defined when the query was prepared
	if (pos>bindcount) {
		bindformaterror=true;
		return false;
	}

	bindvaluesize[pos]=sizeof(MYSQL_TIME);

	bool	validdate=(year>=0 && month>=0 && day>=0);
	bool	validtime=(hour>=0 && minute>=0 && second>=0 && microsecond>=0);

	if (*isnull || (!validdate && !validtime)) {

		bind[pos].buffer_type=MYSQL_TYPE_NULL;
		bind[pos].buffer=(void *)NULL;
		bind[pos].buffer_length=0;
		bind[pos].length=0;

	} else {

		MYSQL_TIME	*t=(MYSQL_TIME *)buffer;

		// MySQL supports date, time and datetime types.
		// Decide which to use.
		if (validdate && validtime) {
			t->time_type=MYSQL_TIMESTAMP_DATETIME;
			bind[pos].buffer_type=MYSQL_TYPE_DATETIME;
		} else if (validdate) {
			t->time_type=MYSQL_TIMESTAMP_DATE;
			bind[pos].buffer_type=MYSQL_TYPE_DATE;
		} else if (validtime) {
			t->time_type=MYSQL_TIMESTAMP_TIME;
			bind[pos].buffer_type=MYSQL_TYPE_TIME;
		}

		t->year=(year>=0)?year:0;
		t->month=(month>=0)?month:0;
		t->day=(day>=0)?day:0;
		t->hour=(hour>=0)?hour:0;
		t->minute=(minute>=0)?minute:0;
		t->second=(second>=0)?second:0;
		t->second_part=(microsecond>=0)?microsecond:0;
		t->neg=(!validdate && isnegative)?TRUE:FALSE;

		bind[pos].buffer=(void *)buffer;
		bind[pos].buffer_length=sizeof(MYSQL_TIME);
		bind[pos].length=&bindvaluesize[pos];
	}
	bind[pos].is_null=(my_bool *)isnull;
	boundvariables=true;

	return true;
}

bool mysqlcursor::inputBindBlob(const char *variable, 
				uint16_t variablesize,
				const char *value, 
				uint32_t valuesize,
				int16_t *isnull) {

	if (!usestmtprepare) {
		return true;
	}

	// "variable" should be something like :1,:2,:3, etc.
	// If it's something like :var1,:var2,:var3, etc. then it'll be
	// converted to 0.  1 will be subtracted and after the cast it will
	// be converted to 65535 and will cause the if below to fail.
	uint16_t	pos=charstring::toInteger(variable+1)-1;

	// don't attempt to bind beyond the number of
	// variables defined when the query was prepared
	if (pos>bindcount) {
		bindformaterror=true;
		return false;
	}

	bindvaluesize[pos]=valuesize;

	if (*isnull) {
		bind[pos].buffer_type=MYSQL_TYPE_NULL;
		bind[pos].buffer=(void *)NULL;
		bind[pos].buffer_length=0;
		bind[pos].length=0;
	} else {
		bind[pos].buffer_type=MYSQL_TYPE_LONG_BLOB;
		bind[pos].buffer=(void *)value;
		bind[pos].buffer_length=valuesize;
		bind[pos].length=&bindvaluesize[pos];
	}
	bind[pos].is_null=(my_bool *)isnull;
	boundvariables=true;

	return true;
}

bool mysqlcursor::inputBindClob(const char *variable, 
				uint16_t variablesize,
				const char *value, 
				uint32_t valuesize,
				int16_t *isnull) {
	return inputBindBlob(variable,variablesize,value,valuesize,isnull);
}
#endif

bool mysqlcursor::executeQuery(const char *query, uint32_t length) {

	// initialize row count
	nrows=0;

#ifdef HAVE_MYSQL_STMT_PREPARE
	if (usestmtprepare) {

		// handle binds
		if (boundvariables && mysql_stmt_bind_param(stmt,bind)) {
			return false;
		}

		// execute the query
		if ((queryresult=mysql_stmt_execute(stmt))) {
			return false;
		}

		checkForTempTable(query,length);

		// allocate buffers, if necessary
		if (mysqlconn->maxselectlistsize==-1) {
			allocateResultSetBuffers(ncols,
					mysqlconn->maxitembuffersize);
		}

		// get the affected row count
		// (call after mysql_stmt_store_result or this will return
		// -1 when the query is a select, which we don't want if
		// foundrows is enabled)
		affectedrows=mysql_stmt_affected_rows(stmt);

	} else {
#endif

		// initialize result set
		mysqlresult=NULL;

		// execute the query
		if ((queryresult=mysql_real_query(mysqlconn->mysqlptr,
							query,length))) {
			return false;
		}

		checkForTempTable(query,length);

		// store the result set
		if ((mysqlresult=mysql_store_result(mysqlconn->mysqlptr))==
							(MYSQL_RES *)NULL) {

			// if there was an error then return failure, otherwise
			// the query must have been some DML or DDL
			char	*err=(char *)mysql_error(mysqlconn->mysqlptr);
			if (!charstring::isNullOrEmpty(err)) {
				return false;
			} else {

				// get affected rows, if it was DML then 
				// this should be set
				affectedrows=mysql_affected_rows(
						mysqlconn->mysqlptr);
				return true;
			}
		}

		// get the column count
		ncols=mysql_num_fields(mysqlresult);

		// get the row count
		nrows=mysql_num_rows(mysqlresult);

		// get the affected row count
		// (call after mysql_stmt_store_result or this will return
		// -1 when the query is a select, which we don't want if
		// foundrows is enabled)
		affectedrows=mysql_affected_rows(mysqlconn->mysqlptr);

#ifdef HAVE_MYSQL_STMT_PREPARE
	}
#endif

	// grab the field info
	if (mysqlresult) {
		mysql_field_seek(mysqlresult,0);
		for (unsigned int i=0; i<ncols; i++) {
			mysqlfields[i]=mysql_fetch_field(mysqlresult);
		}
	}

	return true;
}

#ifdef HAVE_MYSQL_COMMIT
bool mysqlcursor::queryIsNotSelect() {
	// Kludge.  The controller uses this to decide whether to run a
	// commit/rollback at the end of the session.  If it returns true
	// for any query during the session then commit/rollback will be run.
	// MySQL needs a commit/rollback to be run even if only selects were
	// run to release metadata locks.  (I originally thought this was only
	// true if the isolation level is set to repeatable-read (the default)
	// but it appears to be necessary for all isolation levels.)  We'll
	// trick the controller into running commit/rollback no matter what by
	// returning true for any query.
	return true;
}
#endif

void mysqlcursor::errorMessage(char *errorbuffer,
					uint32_t errorbufferlength,
					uint32_t *errorlength,
					int64_t *errorcode,
					bool *liveconnection) {

	*liveconnection=true;

	const char	*err;
	unsigned int	errn;
	#ifdef HAVE_MYSQL_STMT_PREPARE
	if (bindformaterror) {
		errn=SQLR_ERROR_INVALIDBINDVARIABLEFORMAT;
		err=SQLR_ERROR_INVALIDBINDVARIABLEFORMAT_STRING;
	} else {
		if (usestmtprepare) {
			err=mysql_stmt_error(stmt);
			errn=mysql_stmt_errno(stmt);
		} else {
	#endif
			err=mysql_error(mysqlconn->mysqlptr);
			errn=mysql_errno(mysqlconn->mysqlptr);
	#ifdef HAVE_MYSQL_STMT_PREPARE
		}
	}
	#endif

	// Below we check both queryresult and errn.  At one time, we only
	// checked queryresult.  This may have been a bug.  It's possible that
	// back then we should have only checked errn.  But I have a fuzzy
	// memory of some version of mysql returning these error codes in
	// queryresult, so for now I'll leave that code and check both.
	#if defined(HAVE_MYSQL_CR_SERVER_GONE_ERROR) || \
			defined(HAVE_MYSQL_CR_SERVER_LOST) 
		#ifdef HAVE_MYSQL_CR_SERVER_GONE_ERROR
		if (queryresult==CR_SERVER_GONE_ERROR ||
				errn==CR_SERVER_GONE_ERROR) {
			*liveconnection=false;
		} else
		#endif
		#ifdef HAVE_MYSQL_CR_SERVER_LOST
		if (queryresult==CR_SERVER_GONE_ERROR /*||
				errn==CR_SERVER_LOST*/) {
			*liveconnection=false;
		} else
		#endif
	#endif
	if (!charstring::compare(err,"") ||
		!charstring::compareIgnoringCase(err,
				"mysql server has gone away") ||
		!charstring::compareIgnoringCase(err,
				"Can't connect to local MySQL",28) ||
		!charstring::compareIgnoringCase(err,
				"Can't connect to MySQL",22)) {
		*liveconnection=false;
	}

	// set return values
	*errorlength=charstring::length(err);
	charstring::safeCopy(errorbuffer,errorbufferlength,err,*errorlength);
	*errorcode=errn;
}

uint32_t mysqlcursor::colCount() {
	return ncols;
}

bool mysqlcursor::knowsRowCount() {
#ifdef HAVE_MYSQL_STMT_PREPARE
	return !usestmtprepare;
#else
	return true;
#endif
}

uint64_t mysqlcursor::rowCount() {
	return nrows;
}

uint64_t mysqlcursor::affectedRows() {
	return affectedrows;
}

const char *mysqlcursor::getColumnName(uint32_t col) {
	return mysqlfields[col]->name;
}

uint16_t mysqlcursor::getColumnType(uint32_t col) {
	switch (mysqlfields[col]->type) {
		case FIELD_TYPE_STRING:
			return STRING_DATATYPE;
		case FIELD_TYPE_VAR_STRING:
			return CHAR_DATATYPE;
		case FIELD_TYPE_DECIMAL:
			return DECIMAL_DATATYPE;
#ifdef HAVE_MYSQL_FIELD_TYPE_NEWDECIMAL
		case FIELD_TYPE_NEWDECIMAL:
			return DECIMAL_DATATYPE;
#endif
		case FIELD_TYPE_TINY:
			return TINYINT_DATATYPE;
		case FIELD_TYPE_SHORT:
			return SMALLINT_DATATYPE;
		case FIELD_TYPE_LONG:
			return INT_DATATYPE;
		case FIELD_TYPE_FLOAT:
			return FLOAT_DATATYPE;
		case FIELD_TYPE_DOUBLE:
			return REAL_DATATYPE;
		case FIELD_TYPE_LONGLONG:
			return BIGINT_DATATYPE;
		case FIELD_TYPE_INT24:
			return MEDIUMINT_DATATYPE;
		case FIELD_TYPE_TIMESTAMP:
			return TIMESTAMP_DATATYPE;
		case FIELD_TYPE_DATE:
			return DATE_DATATYPE;
		case FIELD_TYPE_TIME:
			return TIME_DATATYPE;
		case FIELD_TYPE_DATETIME:
			return DATETIME_DATATYPE;
#ifdef HAVE_MYSQL_FIELD_TYPE_YEAR
		case FIELD_TYPE_YEAR:
			return YEAR_DATATYPE;
#endif
#ifdef HAVE_MYSQL_FIELD_TYPE_NEWDATE
		case FIELD_TYPE_NEWDATE:
			return NEWDATE_DATATYPE;
#endif
		case FIELD_TYPE_NULL:
			return NULL_DATATYPE;
#ifdef HAVE_MYSQL_FIELD_TYPE_ENUM
		case FIELD_TYPE_ENUM:
			return ENUM_DATATYPE;
#endif
#ifdef HAVE_MYSQL_FIELD_TYPE_SET
		case FIELD_TYPE_SET:
			return SET_DATATYPE;
#endif
	// For some versions of mysql, tinyblobs, mediumblobs and longblobs all
	// show up as FIELD_TYPE_BLOB despite field types being defined for
	// those types.  The different types have predictable lengths though,
	// so we'll use those to differentiate them. 
		case FIELD_TYPE_TINY_BLOB:
			return TINY_BLOB_DATATYPE;
		case FIELD_TYPE_BLOB:
			// I originally thought that these were the
			// lengths...
			/*if (mysqlfields[col]->length<256) {
				return TINY_BLOB_DATATYPE;
			} else if (mysqlfields[col]->length<65536) {
				return BLOB_DATATYPE;
			} else if (mysqlfields[col]->length<16777216) {
				return MEDIUM_BLOB_DATATYPE;
			} else {
				return LONG_BLOB_DATATYPE;
			}*/
			// But it appears that some platforms have larger
			// lengths.  These appear to work most reliably...
			if (mysqlfields[col]->length<766) {
				return TINY_BLOB_DATATYPE;
			} else if (mysqlfields[col]->length<196606) {
				return BLOB_DATATYPE;
			} else if (mysqlfields[col]->length<50441646) {
				return MEDIUM_BLOB_DATATYPE;
			} else {
				return LONG_BLOB_DATATYPE;
			}
		case FIELD_TYPE_MEDIUM_BLOB:
			return MEDIUM_BLOB_DATATYPE;
		case FIELD_TYPE_LONG_BLOB:
			return LONG_BLOB_DATATYPE;
		default:
			return UNKNOWN_DATATYPE;
	}
}

uint32_t mysqlcursor::getColumnLength(uint32_t col) {

	switch (getColumnType(col)) {
		case STRING_DATATYPE:
			return (uint32_t)mysqlfields[col]->length;
		case CHAR_DATATYPE:
			return (uint32_t)mysqlfields[col]->length+1;
		case DECIMAL_DATATYPE:
			{
			uint32_t	length=mysqlfields[col]->length+1;
			unsigned int	decimals=mysqlfields[col]->decimals;
			if (decimals>0) {
				length++;
			}
			if (mysqlfields[col]->length<decimals) {
				length=decimals+2;
			}
			return length;
			}
		case TINYINT_DATATYPE:
			return 1;
		case SMALLINT_DATATYPE:
			return 2;
		case INT_DATATYPE:
			return 4;
		case FLOAT_DATATYPE:
			return (mysqlfields[col]->length<=24)?4:8;
		case REAL_DATATYPE:
			return 8;
		case BIGINT_DATATYPE:
			return 8;
		case MEDIUMINT_DATATYPE:
			return 3;
		case TIMESTAMP_DATATYPE:
			return 4;
		case DATE_DATATYPE:
			return 3;
		case TIME_DATATYPE:
			return 3;
		case DATETIME_DATATYPE:
			return 8;
#ifdef HAVE_MYSQL_FIELD_TYPE_YEAR
		case YEAR_DATATYPE:
			return 1;
#endif
#ifdef HAVE_MYSQL_FIELD_TYPE_NEWDATE
		case NEWDATE_DATATYPE:
			return 1;
#endif
		case NULL_DATATYPE:
#ifdef HAVE_MYSQL_FIELD_TYPE_ENUM
		case ENUM_DATATYPE:
			// 1 or 2 bytes delepending
			// on the # of enum values (65535 max)
			return 2;
#endif
#ifdef HAVE_MYSQL_FIELD_TYPE_SET
		case SET_DATATYPE:
			// 1,2,3,4 or 8 bytes depending
			// on the # of members (64 max)
			return 8;
#endif
		case TINY_BLOB_DATATYPE:
			return 255;
		case BLOB_DATATYPE:
			return 65535;
		case MEDIUM_BLOB_DATATYPE:
			return 16777215;
		case LONG_BLOB_DATATYPE:
			return 2147483647;
	}
	return (uint32_t)mysqlfields[col]->length;
}

uint32_t mysqlcursor::getColumnPrecision(uint32_t col) {
	return mysqlfields[col]->length;
}

uint32_t mysqlcursor::getColumnScale(uint32_t col) {
	return mysqlfields[col]->decimals;
}

uint16_t mysqlcursor::getColumnIsNullable(uint32_t col) {
	return !(IS_NOT_NULL(mysqlfields[col]->flags));
}

uint16_t mysqlcursor::getColumnIsPrimaryKey(uint32_t col) {
	return IS_PRI_KEY(mysqlfields[col]->flags);
}

uint16_t mysqlcursor::getColumnIsUnique(uint32_t col) {
	return mysqlfields[col]->flags&UNIQUE_KEY_FLAG;
}

uint16_t mysqlcursor::getColumnIsPartOfKey(uint32_t col) {
	return mysqlfields[col]->flags&MULTIPLE_KEY_FLAG;
}

uint16_t mysqlcursor::getColumnIsUnsigned(uint32_t col) {
	return mysqlfields[col]->flags&UNSIGNED_FLAG;
}

uint16_t mysqlcursor::getColumnIsZeroFilled(uint32_t col) {
	return mysqlfields[col]->flags&ZEROFILL_FLAG;
}

uint16_t mysqlcursor::getColumnIsBinary(uint32_t col) {
	#ifdef BINARY_FLAG
		return mysqlfields[col]->flags&BINARY_FLAG;
	#else
		return 0;
	#endif
}

uint16_t mysqlcursor::getColumnIsAutoIncrement(uint32_t col) {
	#ifdef AUTO_INCREMENT_FLAG
		return mysqlfields[col]->flags&AUTO_INCREMENT_FLAG;
	#else
		return 0;
	#endif
}

bool mysqlcursor::noRowsToReturn() {
	// for DML or DDL queries, return no data
	return (!mysqlresult);
}

bool mysqlcursor::fetchRow() {
#ifdef HAVE_MYSQL_STMT_PREPARE
	if (usestmtprepare) {
		return !mysql_stmt_fetch(stmt);
	} else {
#endif
		return ((mysqlrow=mysql_fetch_row(mysqlresult))!=NULL &&
			(mysqlrowlengths=mysql_fetch_lengths(
						mysqlresult))!=NULL);
#ifdef HAVE_MYSQL_STMT_PREPARE
	}
#endif
}

void mysqlcursor::getField(uint32_t col,
				const char **fld, uint64_t *fldlength,
				bool *blob, bool *null) {

#ifdef HAVE_MYSQL_STMT_PREPARE
	if (usestmtprepare) {
		if (!isnull[col]) {
			uint16_t	coltype=getColumnType(col);
			if (coltype==TINY_BLOB_DATATYPE ||
				coltype==BLOB_DATATYPE ||
				coltype==MEDIUM_BLOB_DATATYPE ||
				coltype==LONG_BLOB_DATATYPE) {
				*blob=true;
				return;
			} else {
				*fld=&field[col*mysqlconn->maxitembuffersize];
				*fldlength=fieldlength[col];
			}
		} else {
			*null=true;
		}
	} else {
#endif
		if (mysqlrow[col]) {
			*fld=mysqlrow[col];
			*fldlength=mysqlrowlengths[col];
		} else {
			*null=true;
		}
#ifdef HAVE_MYSQL_STMT_PREPARE
	}
#endif
}

#ifdef HAVE_MYSQL_STMT_PREPARE
bool mysqlcursor::getLobFieldLength(uint32_t col, uint64_t *length)  {
	// lobfield needs to be zero'ed prior to each call to
	// mysql_stmt_fetch_column() because mysql_stmt_fetch_column()
	// fiddles with its member variables, and we don't want stale
	// values (especially pointers) lingering across uses
	bytestring::zero(&lobfield,sizeof(MYSQL_BIND));
	lobfield.buffer_type=MYSQL_TYPE_STRING;
	lobfield.buffer_length=fieldlength[col];
	*length=lobfield.buffer_length;
	return true;
}

bool mysqlcursor::getLobFieldSegment(uint32_t col,
					char *buffer, uint64_t buffersize,
					uint64_t offset, uint64_t charstoread,
					uint64_t *charsread) {

	// mysql can't fetch the lob in chunks so we need to fetch the
	// entire thing into a buffer here at the beginning
	if (!offset) {
		lobfield.buffer=new char[lobfield.buffer_length];
		if (mysql_stmt_fetch_column(stmt,&lobfield,col,0)) {
			return false;
		}
	}

	// sanity checks
	if (!lobfield.buffer || offset>lobfield.buffer_length) {
		return false;
	}

	// deterine how many characters to actually read
	*charsread=charstoread;
	if (charstoread>lobfield.buffer_length-offset) {
		*charsread=lobfield.buffer_length-offset;
	}

	// copy out the data
	bytestring::copy(buffer,
		(unsigned char *)lobfield.buffer+offset,*charsread);

	return true;
}

void mysqlcursor::closeLobField(uint32_t col) {
	delete[] (char *)lobfield.buffer;
	lobfield.buffer=NULL;
	lobfield.buffer_length=0;
	return;
}
#endif

void mysqlcursor::closeResultSet() {
#ifdef HAVE_MYSQL_STMT_PREPARE
	if (usestmtprepare) {
		boundvariables=false;
		bytestring::zero(bind,bindcount*sizeof(MYSQL_BIND));
		mysql_stmt_reset(stmt);
		if (stmtfreeresult) {
			mysql_stmt_free_result(stmt);
			stmtfreeresult=false;
		}
	}
#endif
	if (mysqlresult!=(MYSQL_RES *)NULL) {
		mysql_free_result(mysqlresult);
		mysqlresult=NULL;
#ifdef HAVE_MYSQL_NEXT_RESULT
		while (!mysql_next_result(mysqlconn->mysqlptr)) {
			mysqlresult=mysql_store_result(mysqlconn->mysqlptr);
			if (mysqlresult!=(MYSQL_RES *)NULL) {
				mysql_free_result(mysqlresult);
				mysqlresult=NULL;
			}
		}
#endif
	}
#ifdef HAVE_MYSQL_STMT_PREPARE
	if (usestmtprepare && mysqlconn->maxselectlistsize==-1) {
		deallocateResultSetBuffers();
	}
#endif
}
