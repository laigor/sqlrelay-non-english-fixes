// Copyright (c) 2012  David Muse
// See the file COPYING for more information

#ifndef SQLRPWDENC_H
#define SQLRPWDENC_H

#include <sqlrelay/private/sqlrserverdll.h>

#include <rudiments/xmldomnode.h>

class SQLRSERVER_DLLSPEC sqlrpwdenc {
	public:
			sqlrpwdenc(xmldomnode *parameters);
		virtual	~sqlrpwdenc();
		virtual const char	*getId();
		virtual	bool	oneWay();
		virtual	char	*encrypt(const char *value);
		virtual	char	*decrypt(const char *value);
	protected:
		xmldomnode	*parameters;
};

#endif