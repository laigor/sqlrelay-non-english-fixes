// Copyright (c) 1999-2012  David Muse
// See the file COPYING for more information

#include <sqlrelay/sqlrserverconnection.h>
#include <sqlrelay/sqlrservercursor.h>
#include <sqlrelay/sqlparser.h>
#include <sqlrelay/sqlrtranslation.h>
#include <debugprint.h>

class locksmssqlserverize : public sqlrtranslation {
	public:
			locksmssqlserverize(sqlrtranslations *sqlts,
						xmldomnode *parameters,
						bool debug);
		bool	run(sqlrconnection_svr *sqlrcon,
					sqlrcursor_svr *sqlrcur,
					xmldom *querytree);
};

locksmssqlserverize::locksmssqlserverize(sqlrtranslations *sqlts,
					xmldomnode *parameters,
					bool debug) :
				sqlrtranslation(sqlts,parameters,debug) {
}

bool locksmssqlserverize::run(sqlrconnection_svr *sqlrcon,
					sqlrcursor_svr *sqlrcur,
					xmldom *querytree) {

	xmldomnode	*query=querytree->getRootNode();

	// lock query...
	xmldomnode	*locknode=query->getFirstTagChild(
						sqlparser::_lock);
	if (locknode->isNullNode()) {
		return true;
	}

	// table
	xmldomnode	*tablenode=locknode->getFirstTagChild(
						sqlparser::_table);
	if (tablenode->isNullNode()) {
		return true;
	}

	// table name
	xmldomnode	*tablenamedbnode=
				tablenode->getFirstTagChild(
					sqlparser::_table_name_database);
	xmldomnode	*tablenameschemanode=
				tablenode->getFirstTagChild(
					sqlparser::_table_name_schema);
	xmldomnode	*tablenametablenode=
				tablenode->getFirstTagChild(
					sqlparser::_table_name_table);
	if (tablenametablenode->isNullNode()) {
		return true;
	}
	const char	*tablenamedb=
			tablenamedbnode->getAttributeValue("value");
	const char	*tablenameschema=
			tablenameschemanode->getAttributeValue("value");
	const char	*tablenametable=
			tablenametablenode->getAttributeValue("value");
	if (!charstring::length(tablenametable)) {
		return true;
	}

	// in
	xmldomnode	*innode=tablenametablenode->getNextTagSibling(
							sqlparser::_in_mode);
	if (innode->isNullNode()) {
		return true;
	}

	// lock mode
	xmldomnode	*lockmodenode=tablenode->getNextTagSibling(
						sqlparser::_lock_mode);
	if (lockmodenode->isNullNode()) {
		return true;
	}
	const char	*value=lockmodenode->getAttributeValue("value");
	bool	exclusive=!charstring::compareIgnoringCase(value,"exclusive");
	bool	share=!charstring::compareIgnoringCase(value,"share");
	if (!exclusive && !share) {
		return true;
	}

	// mode
	xmldomnode	*modenode=tablenode->getNextTagSibling(
						sqlparser::_mode);
	if (modenode->isNullNode()) {
		return true;
	}

	// build a new query using tablockx for exclusive and tablock for share
	stringbuffer	newquery;
	newquery.append("select 1 from ");
	if (charstring::length(tablenamedb)) {
		newquery.append(tablenamedb)->append('.');
	}
	if (charstring::length(tablenameschema)) {
		newquery.append(tablenameschema)->append('.');
	}
	newquery.append(tablenametable);
	newquery.append(" with ");
	newquery.append((exclusive)?"(tablockx)":"(tablock)");

	// dump the old query
	query->deleteChild(locknode);

	// parse and attach the new query
	sqlparser	sqlp;
	sqlp.useTree(querytree);
	sqlp.parse(newquery.getString());

	return true;
}

extern "C" {
	sqlrtranslation	*new_locksmssqlserverize(
					sqlrtranslations *sqlts,
					xmldomnode *parameters,
					bool debug) {
		return new locksmssqlserverize(sqlts,parameters,debug);
	}
}
