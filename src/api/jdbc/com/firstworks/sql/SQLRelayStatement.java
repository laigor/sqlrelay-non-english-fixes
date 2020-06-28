package com.firstworks.sql;

import java.sql.*;
import java.util.List;
import java.util.ArrayList;

import com.firstworks.sqlrelay.*;

public class SQLRelayStatement extends SQLRelayDebug implements Statement {

	private Connection		connection;
	private SQLRConnection		sqlrcon;
	protected SQLRCursor		sqlrcur;
	private List<String>		batch;
	private boolean			closeoncompletion;
	protected SQLRelayResultSet	resultset;
	private int			fetchdirection;
	private int			maxfieldsize;
	private int			maxrows;
	private boolean			poolable;
	protected int			updatecount;
	private boolean			escapeprocessing;

	public SQLRelayStatement() {
		debugFunction();
		reset();
	}

	private void	reset() {
		debugFunction();
		connection=null;
		sqlrcon=null;
		sqlrcur=null;
		batch=new ArrayList<String>();
		closeoncompletion=false;
		resultset=null;
		fetchdirection=ResultSet.FETCH_FORWARD;
		maxfieldsize=0;
		maxrows=0;
		poolable=false;
		updatecount=-1;
		escapeprocessing=true;
	}

	public void	setConnection(Connection connection) {
		debugFunction();
		this.connection=connection;
	}

	public void	setSQLRConnection(SQLRConnection sqlrcon) {
		debugFunction();
		this.sqlrcon=sqlrcon;
	}

	public void	setSQLRCursor(SQLRCursor sqlrcur) {
		debugFunction();
		this.sqlrcur=sqlrcur;
	}

	public void 	addBatch(String sql) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		batch.add(sql);
	}

	public void 	cancel() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		// FIXME: maybe we can support this somehow?
	}

	public void 	clearBatch() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		batch.clear();
	}

	public void 	clearWarnings() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
	}

	public void 	close() throws SQLException {
		debugFunction();
		if (resultset!=null) {
			resultset.close();
		}
		reset();
	}

	public void 	closeOnCompletion() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		closeoncompletion=true;
	}

	public boolean 	execute(String sql) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: handle timeout
		resultset=null;
		updatecount=-1;
		boolean	result=sqlrcur.sendQuery(sql);
		if (result) {
			updatecount=(int)sqlrcur.affectedRows();
			if (sqlrcur.colCount()>0) {
				resultset=new SQLRelayResultSet();
				resultset.setStatement(this);
				resultset.setSQLRCursor(sqlrcur);
			}
		} else {
			throwErrorMessageException();
		}
		return result;
	}

	public boolean 	execute(String sql, int autoGeneratedKeys)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		return false;
	}

	public boolean 	execute(String sql, int[] columnIndexes)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		return false;
	}

	public boolean 	execute(String sql, String[] columnNames)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		return false;
	}

	public int[] 	executeBatch() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: handle timeout
		int[]	results=new int[batch.size()];
		int	count=0;
		for (String sql: batch) {
			results[count++]=executeUpdate(sql);
		}
		return results;
	}

	public ResultSet 	executeQuery(String sql) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: handle timeout
		resultset=null;
		updatecount=-1;
		if (sqlrcur.sendQuery(sql)) {
			resultset=new SQLRelayResultSet();
			resultset.setStatement(this);
			resultset.setSQLRCursor(sqlrcur);
		} else {
			throw new SQLException(sqlrcur.errorMessage());
		}
		return resultset;
	}

	public int 	executeUpdate(String sql) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: handle timeout
		resultset=null;
		updatecount=-1;
		if (sqlrcur.sendQuery(sql)) {
			updatecount=(int)sqlrcur.affectedRows();
		} else {
			throwErrorMessageException();
		}
		return updatecount;
	}

	public int 	executeUpdate(String sql, int autoGeneratedKeys)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		return 0;
	}

	public int 	executeUpdate(String sql, int[] columnIndexes)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		return 0;
	}

	public int 	executeUpdate(String sql, String[] columnNames)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		return 0;
	}

	public Connection 	getConnection() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return connection;
	}

	public int 	getFetchDirection() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return fetchdirection;
	}

	public int 	getFetchSize() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return (int)sqlrcur.getResultSetBufferSize();
	}

	public ResultSet 	getGeneratedKeys() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		// FIXME: maybe we can support this somehow?
		return null;
	}

	public int 	getMaxFieldSize() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return maxfieldsize;
	}

	public int 	getMaxRows() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return maxrows;
	}

	public boolean 	getMoreResults() throws SQLException {
		debugFunction();
		return getMoreResults(Statement.CLOSE_CURRENT_RESULT);
	}

	public boolean 	getMoreResults(int current) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		switch (current) {
			case Statement.CLOSE_CURRENT_RESULT:
				if (resultset!=null) {
					resultset.close();
					resultset=null;
					updatecount=-1;
				}
				// FIXME: we could support this...
				return false;
			case Statement.KEEP_CURRENT_RESULT:
				throwNotSupportedException();
				return false;
			case Statement.CLOSE_ALL_RESULTS:
				throwNotSupportedException();
				return false;
			default:
				throwNotSupportedException();
				return false;
		}
	}

	public int 	getQueryTimeout() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
		// FIXME: this can be supported
		//return sqlrcon.getResponseTimeout();
		return 0;
	}

	public ResultSet 	getResultSet() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return resultset;
	}

	public int 	getResultSetConcurrency() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: is this correct?
		return ResultSet.CONCUR_READ_ONLY;
	}

	public int 	getResultSetHoldability() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: is this correct?
		return ResultSet.CLOSE_CURSORS_AT_COMMIT;
	}

	public int 	getResultSetType() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return ResultSet.TYPE_FORWARD_ONLY;
	}

	public int 	getUpdateCount() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		debugPrintln("	updatecount="+updatecount);
		return updatecount;
	}

	public SQLWarning 	getWarnings() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return null;
	}

	public boolean 	isClosed() throws SQLException {
		debugFunction();
		return sqlrcur==null;
	}

	public boolean 	isCloseOnCompletion() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return false;
	}

	public boolean 	isPoolable() throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		return poolable;
	}

	public void 	setCursorName(String name) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		throwNotSupportedException();
	}

	public void 	setEscapeProcessing(boolean enable)
						throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		escapeprocessing=enable;
		// FIXME: do something with this...
	}

	public void 	setFetchDirection(int direction) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		fetchdirection=direction;
	}

	public void 	setFetchSize(int rows) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		sqlrcur.setResultSetBufferSize(rows);
	}

	public void 	setMaxFieldSize(int max) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		maxfieldsize=max;
	}

	public void 	setMaxRows(int max) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		maxrows=max;
	}

	public void 	setPoolable(boolean poolable) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		this.poolable=poolable;
	}

	public void 	setQueryTimeout(int seconds) throws SQLException {
		debugFunction();
		throwExceptionIfClosed();
		// FIXME: hmm... this is at the connection level
		sqlrcon.setResponseTimeout(seconds,0);
	}

	public boolean	isWrapperFor(Class<?> iface) throws SQLException {
		debugFunction();
		return (iface==SQLRCursor.class);

	}

	@SuppressWarnings({"unchecked"})
	public <T> T	unwrap(Class<T> iface) throws SQLException {
		debugFunction();
		return (T)((iface==SQLRCursor.class)?sqlrcur:null);
	}

	protected void throwExceptionIfClosed() throws SQLException {
		if (sqlrcur==null) {
			throw new SQLException("FIXME: Statement is closed");
		}
	}

	protected void throwErrorMessageException() throws SQLException {
		debugFunction();
		throw new SQLException(sqlrcur.errorMessage());
	}

	protected void throwNotSupportedException() throws SQLException {
		debugFunction();
		throw new SQLFeatureNotSupportedException();
	}
};
