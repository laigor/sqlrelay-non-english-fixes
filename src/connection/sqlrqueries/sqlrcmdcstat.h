// Copyright (c) 2012  David Muse
// See the file COPYING for more information

#ifndef SQLRCMDCSTAT_H
#define SQLRCMDCSTAT_H

#include <sqlrcontroller.h>
#include <sqlrconnection.h>
#include <sqlrquery.h>

class sqlrcmdcstat : public sqlrquery {
	public:
			sqlrcmdcstat(rudiments::xmldomnode *parameters);
		bool	match(const char *querystring, uint32_t querylength);
		sqlrquerycursor	*getCursor(sqlrconnection_svr *conn);
};

class sqlrcmdcstatcursor : public sqlrquerycursor {
	public:
			sqlrcmdcstatcursor(sqlrconnection_svr *sqlrcon,
					rudiments::xmldomnode *parameters);
			~sqlrcmdcstatcursor();

		bool		executeQuery(const char *query,
						uint32_t length);
		uint32_t	colCount();
		const char	*getColumnName(uint32_t col);
		uint16_t	getColumnType(uint32_t col);
		uint32_t	getColumnLength(uint32_t col);
		uint32_t	getColumnPrecision(uint32_t col);
		uint32_t	getColumnScale(uint32_t col);
		uint16_t	getColumnIsNullable(uint32_t col);
		bool		noRowsToReturn();
		bool		fetchRow();
		void		getField(uint32_t col,
					const char **field,
					uint64_t *fieldlength,
					bool *blob, bool *null);
	private:
		uint64_t	currentrow;
		char		*fieldbuffer;

		sqlrconnstatistics	*cs;

};

#endif
