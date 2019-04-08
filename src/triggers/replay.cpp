// Copyright (c) 1999-2018  David Muse
// All rights reserved

#include <sqlrelay/sqlrserver.h>
#include <rudiments/snooze.h>

class querydetails {
	public:
		char		*query;
		uint32_t	querylen;
		linkedlist<sqlrserverbindvar *>	inbindvars;
		linkedlist<sqlrserverbindvar *>	outbindvars;
		linkedlist<sqlrserverbindvar *>	inoutbindvars;
};

enum condition_t {
	CONDITION_ERROR=0,
	CONDITION_ERRORCODE
};

class condition {
	public:
		condition_t	cond;
		const char	*error;
		uint32_t	errorcode;
};

class SQLRSERVER_DLLSPEC sqlrtrigger_replay : public sqlrtrigger {
	public:
		sqlrtrigger_replay(sqlrservercontroller *cont,
						sqlrtriggers *ts,
						domnode *parameters);
		~sqlrtrigger_replay();
		bool	run(sqlrserverconnection *sqlrcon,
						sqlrservercursor *sqlrcur,
						bool before,
						bool *success);

		void	endTransaction(bool commit);

	private:
		bool	logQuery(sqlrservercursor *sqlrcur);
		bool	replayLog(sqlrservercursor *sqlrcur);
		bool	replayCondition(sqlrservercursor *sqlrcur);


		void	copyBind(memorypool *pool,
					sqlrserverbindvar *dest,
					sqlrserverbindvar *source);

		sqlrservercontroller	*cont;

		bool		debug;

		bool		replaytx;

		uint32_t	maxretries;

		linkedlist<querydetails *>	log;
		linkedlist<condition *>		conditions;
		memorypool			logpool;

		bool	inreplay;
};

sqlrtrigger_replay::sqlrtrigger_replay(sqlrservercontroller *cont,
					sqlrtriggers *ts,
					domnode *parameters) :
					sqlrtrigger(cont,ts,parameters) {
	this->cont=cont;

	debug=cont->getConfig()->getDebugTriggers();

	// get the scope (query or tx)
	replaytx=!charstring::compareIgnoringCase(
				parameters->getAttributeValue("scope"),
				"transaction");

	// get the max retries
	maxretries=charstring::toInteger(
				parameters->getAttributeValue("maxretries"));

	// get the replay conditions...
	for (domnode *cond=parameters->getFirstTagChild("condition");
				!cond->isNullNode();
				cond=cond->getNextTagSibling("condition")) {

		condition	*c=new condition;

		// for now we only support <condition error="..."/>
		const char	*err=cond->getAttributeValue("error");
		if (charstring::isNumber(err)) {
			c->cond=CONDITION_ERRORCODE;
			c->errorcode=charstring::toInteger(err);
		} else {
			c->cond=CONDITION_ERROR;
			c->error=err;
		}

		conditions.append(c);
	}

	inreplay=false;
}

sqlrtrigger_replay::~sqlrtrigger_replay() {
	conditions.clearAndDelete();
}

bool sqlrtrigger_replay::run(sqlrserverconnection *sqlrcon,
						sqlrservercursor *sqlrcur,
						bool before,
						bool *success) {
	if (before) {
		return logQuery(sqlrcur);
	} else {
		*success=replayLog(sqlrcur);
		return *success;
	}
}

bool sqlrtrigger_replay::logQuery(sqlrservercursor *sqlrcur) {

	// bail if we're currently replaying the log...
	if (inreplay) {
		return true;
	}

	if (replaytx) {

		// bail if we're not in a transaction
		if (!cont->inTransaction()) {
			if (debug) {
				stdoutput.printf("not in a trasaction\n");
			}
			return true;
		}

	} else {

		// clear the log and logpool
		logpool.clear();
		log.clearAndDelete();
	}

	// log the query...

	querydetails	*qd=new querydetails;

	if (debug) {
		stdoutput.printf("log {\n");
	}

	// copy the query itself (make sure to null terminate)
	qd->querylen=sqlrcur->getQueryLength();
	qd->query=(char *)logpool.allocate(qd->querylen+1);
	bytestring::copy(qd->query,sqlrcur->getQueryBuffer(),qd->querylen);
	qd->query[qd->querylen]='\0';

	if (debug) {
		stdoutput.printf("	query:\n%.*s\n",qd->querylen,qd->query);
	}

	// copy in input binds
	uint16_t		incount=sqlrcur->getInputBindCount();
	sqlrserverbindvar	*invars=sqlrcur->getInputBinds();
	if (debug && incount) {
		stdoutput.printf("	input binds {\n");
	}
	for (uint16_t i=0; i<incount; i++) {
		if (debug) {
			stdoutput.printf("		%.*s\n",
						invars[i].variablesize,
						invars[i].variable);
		}
		sqlrserverbindvar	*bv=new sqlrserverbindvar;
		copyBind(&logpool,bv,&(invars[i]));
		qd->inbindvars.append(bv);
	}
	if (debug && incount) {
		stdoutput.printf("	}\n");
	}
	
	// copy in output binds
	uint16_t		outcount=sqlrcur->getOutputBindCount();
	sqlrserverbindvar	*outvars=sqlrcur->getOutputBinds();
	if (debug && outcount) {
		stdoutput.printf("	output binds {\n");
	}
	for (uint16_t i=0; i<outcount; i++) {
		if (debug) {
			stdoutput.printf("		%.*s\n",
						outvars[i].variablesize,
						outvars[i].variable);
		}
		sqlrserverbindvar	*bv=new sqlrserverbindvar;
		copyBind(&logpool,bv,&(outvars[i]));
		qd->outbindvars.append(bv);
	}
	if (debug && outcount) {
		stdoutput.printf("	}\n");
	}

	// copy in input-output binds
	uint16_t		inoutcount=sqlrcur->getInputOutputBindCount();
	sqlrserverbindvar	*inoutvars=sqlrcur->getInputOutputBinds();
	if (debug && inoutcount) {
		stdoutput.printf("	input-output binds {\n");
	}
	for (uint16_t i=0; i<inoutcount; i++) {
		if (debug) {
			stdoutput.printf("		%.*s\n",
						inoutvars[i].variablesize,
						inoutvars[i].variable);
		}
		sqlrserverbindvar	*bv=new sqlrserverbindvar;
		copyBind(&logpool,bv,&(inoutvars[i]));
		qd->inoutbindvars.append(bv);
	}
	if (debug && inoutcount) {
		stdoutput.printf("	}\n");
	}

	// log copied query and binds
	log.append(qd);

	if (debug) {
		stdoutput.printf("}\n");
	}
	
	return true;
}

void sqlrtrigger_replay::copyBind(memorypool *pool,
					sqlrserverbindvar *dest,
					sqlrserverbindvar *source) {

	// byte-copy everything
	bytestring::copy(dest,source,sizeof(sqlrserverbindvar));

	// (re)copy variable name
	dest->variablesize=source->variablesize;
	dest->variable=(char *)pool->allocate(dest->variablesize+1);
	charstring::copy(dest->variable,source->variable);
	
	// (re)copy strings
	if (source->type==SQLRSERVERBINDVARTYPE_STRING) {
		dest->value.stringval=
			(char *)pool->allocate(source->valuesize+1);
		charstring::copy(dest->value.stringval,
					source->value.stringval);
	} else if (source->type==SQLRSERVERBINDVARTYPE_DATE) {
		dest->value.dateval.tz=
			(char *)pool->allocate(
				charstring::length(source->value.dateval.tz)+1);
		charstring::copy(dest->value.dateval.tz,
					source->value.dateval.tz);
		dest->value.dateval.buffer=
			(char *)pool->allocate(
				source->value.dateval.buffersize);
		charstring::copy(dest->value.dateval.buffer,
					source->value.dateval.buffer,
					source->value.dateval.buffersize);
	}
}

bool sqlrtrigger_replay::replayLog(sqlrservercursor *sqlrcur) {

	bool	retval=true;

	// we're replaying the log
	inreplay=true;

	uint32_t	sec=0;
	uint32_t	usec=0;
	uint32_t	retry=0;
	while (replayCondition(sqlrcur)) {

		// delay before trying again...
		// delay a little longer before each retry, up to 10 seconds
		if (retry==1) {
			usec=10000;
		} else {
			if (sec) {
				sec*=2;
				if (sec>=10) {
					sec=10;
				}
			} else {
				usec*=2;
				if (usec>=1000000) {
					usec=0;
					sec=1;
				}
			}
		}
		if (sec || usec) {
			if (debug) {
				stdoutput.printf("delay %d sec, %d usec...",
								sec,usec);
			}
			snooze::microsnooze(sec,usec);
		}


		if (debug) {
			stdoutput.printf("replay {\n");
		}

		if (debug) {
			stdoutput.printf("	triggering query:\n%.*s\n",
						sqlrcur->getQueryLength(),
						sqlrcur->getQueryBuffer());
		}

		// clear the error
		cont->clearError();
		cont->clearError(sqlrcur);

		if (replaytx) {

			// roll back the current transaction
			if (debug) {
				stdoutput.printf("	rollback {\n");
			}
			if (!cont->rollback()) {
				if (debug) {
					stdoutput.printf("	error\n");
					stdoutput.printf("}\n");
				}
				logpool.clear();
				log.clearAndDelete();
				return false;
			}

			// start a new transaction
			if (debug) {
				stdoutput.printf("	}\n");
				stdoutput.printf("	begin {\n");
			}
			if (!cont->begin()) {
				if (debug) {
					stdoutput.printf("	error\n");
					stdoutput.printf("}\n");
				}
				logpool.clear();
				log.clearAndDelete();
				return false;
			}
			if (debug) {
				stdoutput.printf("	}\n");
			}
		}

		// get the bind pool
		memorypool	*pool=cont->getBindPool(sqlrcur);

		// replay the log
		retval=true;
		for (linkedlistnode<querydetails *> *node=log.getFirst();
						node; node=node->getNext()) {

			// get the query details
			querydetails	*qd=node->getValue();
		
			// prepare the query
			if (debug) {
				stdoutput.printf("	prepare query {\n");
				stdoutput.printf("		query:\n%.*s\n",
							qd->querylen,qd->query);
			}
			if (!cont->prepareQuery(sqlrcur,
						qd->query,qd->querylen)) {
				if (debug) {
					stdoutput.printf(
						"		"
						"prepare error: %.*s\n",
						sqlrcur->getErrorLength(),
						sqlrcur->getErrorBuffer());
					stdoutput.printf("	}\n");
				}
				retval=false;
				break;
			}
			if (debug) {
				stdoutput.printf("	}\n");
			}

			// copy out input binds
			uint16_t	incount=qd->inbindvars.getLength();
			sqlrcur->setInputBindCount(incount);
			sqlrserverbindvar	*invars=
						sqlrcur->getInputBinds();
			if (debug && incount) {
				stdoutput.printf("	input binds {\n");
			}
			linkedlistnode<sqlrserverbindvar *>	*inbindnode=
						qd->inbindvars.getFirst();
			for (uint16_t i=0; i<incount; i++) {
				sqlrserverbindvar	*bv=
						inbindnode->getValue();
				if (debug) {
					stdoutput.printf("		%.*s\n",
							bv->variablesize,
							bv->variable);
				}
				copyBind(pool,&(invars[i]),bv);
				inbindnode=inbindnode->getNext();
			}
			if (debug && incount) {
				stdoutput.printf("	}\n");
			}

			// copy out output binds
			uint16_t	outcount=qd->outbindvars.getLength();
			sqlrcur->setInputBindCount(outcount);
			sqlrserverbindvar	*outvars=
						sqlrcur->getOutputBinds();
			if (debug && outcount) {
				stdoutput.printf("	output binds {\n");
			}
			linkedlistnode<sqlrserverbindvar *>	*outbindnode=
						qd->outbindvars.getFirst();
			for (uint16_t i=0; i<outcount; i++) {
				sqlrserverbindvar	*bv=
						outbindnode->getValue();
				if (debug) {
					stdoutput.printf("		%.*s\n",
							bv->variablesize,
							bv->variable);
				}
				copyBind(pool,&(outvars[i]),bv);
				outbindnode=outbindnode->getNext();
			}
			if (debug && outcount) {
				stdoutput.printf("	}\n");
			}
	
			// copy out input-output binds
			uint16_t		inoutcount=
						qd->inoutbindvars.getLength();
			sqlrcur->setInputBindCount(inoutcount);
			sqlrserverbindvar	*inoutvars=
						sqlrcur->getInputOutputBinds();
			if (debug && inoutcount) {
				stdoutput.printf("	"
						"input-output binds {\n");
			}
			linkedlistnode<sqlrserverbindvar *>	*inoutbindnode=
						qd->inoutbindvars.getFirst();
			for (uint16_t i=0; i<inoutcount; i++) {
				sqlrserverbindvar	*bv=
						inoutbindnode->getValue();
				if (debug) {
					stdoutput.printf("		%.*s\n",
							bv->variablesize,
							bv->variable);
				}
				copyBind(pool,&(inoutvars[i]),bv);
				inoutbindnode=inoutbindnode->getNext();
			}
			if (debug && inoutcount) {
				stdoutput.printf("	}\n");
			}

			// execute the query
			if (debug) {
				stdoutput.printf("	execute query {\n");
			}
			if (!cont->executeQuery(sqlrcur)) {
				if (debug) {
					stdoutput.printf(
						"		"
						"execute error: %.*s\n",
						sqlrcur->getErrorLength(),
						sqlrcur->getErrorBuffer());
					stdoutput.printf("	}\n");
				}
				retval=false;
				break;
			}
			if (debug) {
				stdoutput.printf("	}\n");
			}
		}

		if (debug) {
			stdoutput.printf("}\n");
		}

		// bump retry count
		retry++;

		// bail if we've tried too many times already
		if (retry>maxretries) {
			break;
		}
	}

	if (replaytx && !retval) {
		// clear the log and roll back on error
		cont->rollback();
		logpool.clear();
		log.clearAndDelete();
	}

	// we're no longer replaying the log
	inreplay=false;

	return retval;
}

bool sqlrtrigger_replay::replayCondition(sqlrservercursor *sqlrcur) {

	// did we get a replay condition?
	bool	found=false;
	for (linkedlistnode<condition *> *node=conditions.getFirst();
						node; node=node->getNext()) {

		condition	*val=node->getValue();

		if (val->cond==CONDITION_ERROR) {

			// FIXME: error buffer might not be terminated
			if (charstring::contains(
				sqlrcur->getErrorBuffer(),val->error)) {
				found=true;
				if (debug) {
					stdoutput.printf(
						"replay condition "
						"detected {\n");
					stdoutput.printf("	"
						"pattern: %s\n",val->error);
					stdoutput.printf("	"
						"error string: %.*s\n",
						sqlrcur->getErrorLength(),
						sqlrcur->getErrorBuffer());
					stdoutput.printf("}\n");
				}
			}

		} else if (val->cond==CONDITION_ERRORCODE) {

			if (sqlrcur->getErrorNumber()==val->errorcode) {
				found=true;
				if (debug) {
					stdoutput.printf(
						"replay condition "
						"detected {\n");
					stdoutput.printf("	"
						"error code: %d\n",
						val->errorcode);
					stdoutput.printf("}\n");
				}
			}
		}
	}
	return found;
}

void sqlrtrigger_replay::endTransaction(bool commit) {

	// bail if we're currently replaying the log...
	if (inreplay) {
		return;
	}

	// bail if we're not replaying transactions queries...
	if (!replaytx) {
		return;
	}

	if (debug) {
		stdoutput.printf("clear {\n");
	}
	logpool.clear();
	log.clearAndDelete();
	if (debug) {
		stdoutput.printf("}\n");
	}
}

extern "C" {
	SQLRSERVER_DLLSPEC
	sqlrtrigger	*new_sqlrtrigger_replay(
						sqlrservercontroller *cont,
						sqlrtriggers *ts,
						domnode *parameters) {

		return new sqlrtrigger_replay(cont,ts,parameters);
	}
}