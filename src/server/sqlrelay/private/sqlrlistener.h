// Copyright (c) 1999-2014  David Muse
// See the file COPYING for more information

	private:
		void	cleanUp();
		void	setUserAndGroup();
		bool	verifyAccessToConfigFile(const char *configfile);
		bool	handlePidFile(const char *id);
		void	handleDynamicScaling();
		void	setSessionHandlerMethod();
		void	setHandoffMethod();
		void	setIpPermissions();
		bool	createSharedMemoryAndSemaphores(const char *id);
		void	ipcFileError(const char *idfilename);
		void	keyError(const char *idfilename);
		void	shmError(const char *id, int shmid);
		void	semError(const char *id, int semid);
		void	setStartTime();
		bool	listenOnClientSockets();
		bool	listenOnClientSocket(listenercontainer *lc);
		bool	listenOnHandoffSocket(const char *id);
		bool	listenOnDeregistrationSocket(const char *id);
		bool	listenOnFixupSocket(const char *id);
		filedescriptor	*waitForTraffic();
		bool	handleTraffic(filedescriptor *fd);
		bool	registerHandoff(filedescriptor *sock);
		bool	deRegisterHandoff(filedescriptor *sock);
		bool	fixup(filedescriptor *sock);
		bool	deniedIp(filedescriptor *clientsock);
		void	forkChild(filedescriptor *clientsock,
					const char *protocol);
		static void	clientSessionThread(void *attr);
		void	clientSession(filedescriptor *clientsock,
					const char *protocol, thread *thr);
		void    errorClientSession(filedescriptor *clientsock,
					int64_t errnum, const char *err);
		bool	acquireShmAccess();
		bool	releaseShmAccess();
		bool	acceptAvailableConnection(bool *alldbsdown);
		bool	doneAcceptingAvailableConnection();
		void	waitForConnectionToBeReadyForHandoff();
		bool	handOffOrProxyClient(filedescriptor *sock,
					const char *protocol, thread *thr);
		bool	getAConnection(uint32_t *connectionpid,
					uint16_t *inetport,
					char *unixportstr,
					uint16_t *unixportstrlen,
					filedescriptor *sock,
					thread *thr);
		static void	alarmThread(void *attr);
		bool	findMatchingSocket(uint32_t connectionpid,
					filedescriptor *connectionsock);
		bool	requestFixup(uint32_t connectionpid,
					filedescriptor *connectionsock);
		bool	proxyClient(pid_t connectionpid,
					filedescriptor *connectionsock,
					filedescriptor *clientsock);
		bool	connectionIsUp(const char *connectionid);
		void	pingDatabase(uint32_t connectionpid,
					const char *unixportstr,
					uint16_t inetport);
		static void	pingDatabaseThread(void *attr);
		void	pingDatabaseInternal(uint32_t connectionpid,
						const char *unixportstr,
						uint16_t inetport);
		void	waitForClientClose(bool passstatus,
					filedescriptor *clientsock);

		void		setMaxListeners(uint32_t maxlisteners);
		void		incrementMaxListenersErrors();
		void		incrementConnectedClientCount();
		void		decrementConnectedClientCount();
		uint32_t	incrementForkedListeners();
		uint32_t	decrementForkedListeners();
		void		incrementBusyListeners();
		void		decrementBusyListeners();
		int32_t		getBusyListeners();

		void	logDebugMessage(const char *info);
		void	logClientProtocolError(const char *info,
						ssize_t result);
		void	logClientConnectionRefused(const char *info);
		void	logInternalError(const char *info);

		static void	alarmHandler(int32_t signum);

		uint32_t	maxconnections;
		bool		dynamicscaling;

		int64_t		maxlisteners;
		uint64_t	listenertimeout;

		char		*pidfile;
		sqlrtempdir	*tmpdir;

		char		*logdir;
		char		*debugdir;

		sqlrloggers	*sqlrlg;

		stringbuffer	debugstr;

		semaphoreset	*semset;
		sharedmemory	*shmem;
		shmdata		*shm;
		char		*idfilename;

		bool	initialized;

		inetsocketserver	**clientsockin;
		const char 		**clientsockinproto;
		uint64_t		clientsockincount;
		uint64_t		clientsockinindex;

		unixsocketserver	**clientsockun;
		const char		**clientsockunproto;
		uint64_t		clientsockuncount;
		uint64_t		clientsockunindex;

		unixsocketserver	*handoffsockun;
		char			*handoffsockname;
		unixsocketserver	*removehandoffsockun;
		char			*removehandoffsockname;
		unixsocketserver	*fixupsockun;
		char			*fixupsockname;

		uint16_t		handoffmode;
		handoffsocketnode	*handoffsocklist;

		regularexpression	*allowed;
		regularexpression	*denied;

		sqlrcmdline	*cmdl;

		uint32_t	maxquerysize;
		uint16_t	maxbindcount;
		uint16_t	maxbindnamelength;
		int32_t		idleclienttimeout;

		bool	isforkedchild;
		bool	isforkedthread;

		sqlrconfigfile		cfgfl;

		static	signalhandler		alarmhandler;
		static	volatile sig_atomic_t	alarmrang;

		bool	usethreads;