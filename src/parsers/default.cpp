// Copyright (c) 2014  David Muse
// See the file COPYING for more information

#include <sqlrelay/sqlrserver.h>

extern "C" {
	SQLRSERVER_DLLSPEC sqlrparser *new_sqlrparser_default() {
		return new sqlrparser();
	}
}
