void RegComands()
{
	RegAdminCmd("ma_off_clear", 	CommandClear, 		ADMFLAG_ROOT, 	"Clear history");
	RegAdminCmd("ma_reload", 		CommandReload, 		ADMFLAG_RCON, 	"Reload config and ban reason menu options"); // перезагрузка меню и конфгов
	RegAdminCmd("ma_bd_connect",	CommandConnectBd, 	ADMFLAG_RCON, 	"Reload connect bd");
	RegAdminCmd("sm_ban", 			CommandBan, 		ADMFLAG_BAN, 	"Ban client");
	RegAdminCmd("sm_addban", 		CommandAddBan, 		ADMFLAG_RCON, 	"Add ban client");
	RegAdminCmd("sm_unban", 		CommandUnBan,		ADMFLAG_UNBAN, 	"Un ban client");
	RegAdminCmd("sm_gag", 			CommandGag, 		ADMFLAG_CHAT, 	"Add gag client");
	RegAdminCmd("sm_mute", 			CommandMute, 		ADMFLAG_CHAT, 	"Add mute client");
	RegAdminCmd("sm_silence", 		CommandSil, 		ADMFLAG_CHAT, 	"Add silence client");
	RegAdminCmd("sm_ungag", 		CommandUnGag, 		ADMFLAG_CHAT, 	"Un gag client");
	RegAdminCmd("sm_unmute", 		CommandUnMute, 		ADMFLAG_CHAT, 	"Un mute client");
	RegAdminCmd("sm_unsilence", 	CommandUnSil, 		ADMFLAG_CHAT, 	"Un silence client");

	RegServerCmd("ma_wb_ban", CommandWBan, "Ban player by command from web site");
	RegServerCmd("ma_wb_mute", CommandWMute, "Mute player by command from web site");
	RegServerCmd("ma_wb_unmute", CommandWUnMute, "Un mute player by command from web site");
	RegServerCmd("ma_wb_getinfo", CommandWGetInfo, "Get info by command from web site");
	RegServerCmd("ma_wb_rehashadm", CommandWRehashAdm, "Reload SQL admins");
	
	ConVar Cvar;
	Cvar = FindConVar("sv_alltalk");
	Cvar.AddChangeHook(ConVarChange_Alltalk);
	g_bCvar_Alltalk = Cvar.BoolValue;
	if (g_iGameTyp == GAMETYP_CSGO)
	{
		
		Cvar = FindConVar("sv_talk_enemy_living");
		Cvar.AddChangeHook(ConVarChange);
		Cvar = FindConVar("sv_full_alltalk");
		Cvar.AddChangeHook(ConVarChange);
		Cvar = FindConVar("sv_deadtalk");
	}
	else
	{
		Cvar = CreateConVar("sm_deadtalk", "0", "Controls how dead communicate. 0 - Off. 1 - Dead players ignore teams. 2 - Dead players talk to living teammates.", 0, true, 0.0, true, 2.0);
		g_iCvar_Deadtalk = Cvar.IntValue;
	}

	Cvar.AddChangeHook(ConVarChange_Deadtalk);
	Cvar = FindConVar("sm_immunity_mode");
	Cvar.AddChangeHook(ConVarChange);
	g_iCvar_ImmunityMode = Cvar.IntValue;
	
}

public Action OnClientSayCommand(int iClient, const char[] sCommand, const char[] sArgs)
{
	if (g_bReport && iClient && StrEqual(sArgs, "!report", false))
	{
		ReportMenu(iClient);
		return Plugin_Handled;
	}
	if (g_bSayReasonReport[iClient])
	{
	#if DEBUG
		LogToFile(g_sLogFile, "Chat report reason: %s", sArgs);
	#endif
		PrintToChat2(iClient, "%T", "Own reason", iClient, sArgs);
		g_bSayReasonReport[iClient] = false;
		SetBdReport(iClient, sArgs);
		return Plugin_Handled;
	}
	if (g_bSayReason[iClient])
	{
		strcopy(g_sTarget[iClient][TREASON], sizeof(g_sTarget[][]), sArgs);
	#if DEBUG
		LogToFile(g_sLogFile, "Chat reason: %s", sArgs);
	#endif
		PrintToChat2(iClient, "%T", "Own reason", iClient, sArgs);
		g_bSayReason[iClient] = false;
		OnlineClientSet(iClient);
		return Plugin_Handled;
	}
	if (g_iTargetMuteType[iClient] > 1)
	{
		char sLength[128];
		if (g_iTargenMuteTime[iClient] > 0)
			FormatVrema(iClient, g_iTargenMuteTime[iClient] - GetTime(), sLength, sizeof(sLength));
		else
			FormatVrema(iClient, g_iTargenMuteTime[iClient], sLength, sizeof(sLength));
		
		PrintToChat2(iClient, "%T", "Target no text chat", iClient, sLength, g_iTargetMuteReason[iClient]);
		return Plugin_Handled;
	}
	
	return Plugin_Continue;
}

public Action CommandClear(int iClient, int iArgc)
{
	ClearHistories();

	ReplyToCommand(iClient, "%sClear history", PREFIX);
	
	return Plugin_Handled;
}

public Action CommandReload(int iClient, int iArgc)
{
	ReadConfig();

	ReplyToCommand(iClient, "%sReload Config", PREFIX);
	
	return Plugin_Handled;
}

public Action CommandConnectBd(int iClient, int iArgc)
{
	if (ConnectBd(g_dDatabase, true))
	{
		ReplyToCommand(iClient, "%sYes connect bd", PREFIX);
		KillTimerBekap();
	}
	else
		ReplyToCommand(iClient, "%sNo connect bd", PREFIX);
	
	return Plugin_Handled;
}
//------------------------------------------------------------------------------------------------------------------
public Action CommandGag(int iClient, int iArgc)
{
	if (iArgc < 2)
	{
		ReplyToCommand(iClient, "%sUsage: sm_gag <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}
	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	
	if (!SBGetCmdArg2(iClient, sBuffer, sArg, sizeof(sArg)))
	{
		ReplyToCommand(iClient, "%sUsage: sm_gag <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	g_iTargetType[iClient] = TYPE_GAG;
	if (!ValidTime(iClient))
		return Plugin_Handled;
	
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_gag, arg %s, type %d, time %d, reason %s.", sArg, iTyp, g_iTarget[iClient][TTIME], g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);
	
	return Plugin_Handled;
}

public Action CommandMute(int iClient, int iArgc)
{
	if (iArgc < 2)
	{
		ReplyToCommand(iClient, "%sUsage: sm_mute <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}
	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	
	if (!SBGetCmdArg2(iClient, sBuffer, sArg, sizeof(sArg)))
	{
		ReplyToCommand(iClient, "%sUsage: sm_mute <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	g_iTargetType[iClient] = TYPE_MUTE;
	if (!ValidTime(iClient))
		return Plugin_Handled;
	
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_mute, arg %s, type %d, time %d, reason %s.", sArg, iTyp, g_iTarget[iClient][TTIME], g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);
	
	return Plugin_Handled;
}

public Action CommandSil(int iClient, int iArgc)
{
	if (iArgc < 2)
	{
		ReplyToCommand(iClient, "%sUsage: sm_silence <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	
	if (!SBGetCmdArg2(iClient, sBuffer, sArg, sizeof(sArg)))
	{
		ReplyToCommand(iClient, "%sUsage: sm_silence <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	g_iTargetType[iClient] = TYPE_SILENCE;
	if (!ValidTime(iClient))
		return Plugin_Handled;
	
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_silence, arg %s, type %d, time %d, reason %s.", sArg, iTyp, g_iTarget[iClient][TTIME], g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);
	
	return Plugin_Handled;
}

public Action CommandUnGag(int iClient, int iArgc)
{
	if (iArgc < 1)
	{
		ReplyToCommand(iClient, "%sUsage:  sm_ungag <#userid|#all|#ct|#t|#blue|#red> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	SBGetCmdArg1(iClient, sBuffer, sArg, sizeof(sArg));
	
	g_iTargetType[iClient] = TYPE_UNGAG;
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_ungag, arg %s, type %d, reason %s.", sArg, iTyp, g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);

	return Plugin_Handled;
}

public Action CommandUnMute(int iClient, int iArgc)
{
	if (iArgc < 1)
	{
		ReplyToCommand(iClient, "%sUsage:  sm_unmute <#userid|#all|#ct|#t|#blue|#red> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	SBGetCmdArg1(iClient, sBuffer, sArg, sizeof(sArg));
	
	g_iTargetType[iClient] = TYPE_UNMUTE;
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_unmute, arg %s, type %d, reason %s.", sArg, iTyp, g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);

	return Plugin_Handled;
}

public Action CommandUnSil(int iClient, int iArgc)
{
	if (iArgc < 1)
	{
		ReplyToCommand(iClient, "%sUsage:  sm_unsilence <#userid|#all|#ct|#t|#blue|#red> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	SBGetCmdArg1(iClient, sBuffer, sArg, sizeof(sArg));
	
	g_iTargetType[iClient] = TYPE_UNSILENCE;
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_unsilence, arg %s, type %d, reason %s.", sArg, iTyp, g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);

	return Plugin_Handled;
}

//-------------------------------------------------------------------------------------------------------------------
public Action CommandBan(int iClient, int iArgc)
{
	if (iArgc < 2)
	{
		ReplyToCommand(iClient, "%sUsage: sm_ban <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	
	if (!SBGetCmdArg2(iClient, sBuffer, sArg, sizeof(sArg)))
	{
		ReplyToCommand(iClient, "%sUsage: sm_ban <#userid|#all|#ct|#t|#blue|#red> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	g_iTargetType[iClient] = TYPE_BAN;
	if (!ValidTime(iClient))
		return Plugin_Handled;
	
	int iTyp = GetTypeClient(sArg);
#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_ban, arg %s, type %d, time %d, reason %s.", sArg, iTyp, g_iTarget[iClient][TTIME], g_sTarget[iClient][TREASON]);
#endif
	GetClientToBd(iClient, iTyp, sArg);
	
	return Plugin_Handled;
}

public Action CommandAddBan(int iClient, int iArgc)
{
	if(!g_bAddBan)
		return Plugin_Handled;

	if (iArgc < 2)
	{
		ReplyToCommand(iClient, "%sUsage: sm_addban <steamid|ip> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	
	if (!SBGetCmdArg2(iClient, sBuffer, sArg, sizeof(sArg)))
	{
		ReplyToCommand(iClient, "%sUsage: sm_addban <steamid|ip> <time> [reason]", PREFIX);
		return Plugin_Handled;
	}

	g_iTargetType[iClient] = TYPE_ADDBAN;
	if (!ValidTime(iClient))
		return Plugin_Handled;

	int iTarget;
	if (strncmp(sArg, "STEAM_", 6) == 0)
		iTarget = FindTargetSteam(sArg);
	else
		iTarget = FindTargetIp(sArg);

#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_addban, arg %s, target %d, time %d, reason %s.", sArg, iTarget, g_iTarget[iClient][TTIME], g_sTarget[iClient][TREASON]);
#endif

	if(iTarget)
	{
		if(GetUserAdmin(iTarget) == INVALID_ADMIN_ID)
			CheckBanInBd(iClient, iTarget, 1, sArg);
		else
		{
			if (iClient)
				PrintToChat2(iClient, "%T", "No admin", iClient);
			else
				ReplyToCommand(iClient, "%sThis Admin immunity.", PREFIX);
		}
	}
	else
		CheckBanInBd(iClient, iTarget, 1, sArg);

	return Plugin_Handled;
}

public Action CommandUnBan(int iClient, int iArgc)
{
	if (!g_bUnBan)
		return Plugin_Handled;

	if (iArgc < 1)
	{
		ReplyToCommand(iClient, "%sUsage:  sm_unban <steamid|ip> [reason]", PREFIX);
		return Plugin_Handled;
	}

	char sArg[64],
		sBuffer[256];	
	GetCmdArgString(sBuffer, sizeof(sBuffer));
	SBGetCmdArg1(iClient, sBuffer, sArg, sizeof(sArg));
	
	g_iTargetType[iClient] = TYPE_UNBAN;

#if DEBUG
	LogToFile(g_sLogFile,"Command: sm_unban, arg %s, reason %s.", sArg, g_sTarget[iClient][TREASON]);
#endif
	CheckBanInBd(iClient, 0, 0, sArg);

	return Plugin_Handled;
}
//------------------------------------------------------------------------------------------------------------------------
public Action CommandWRehashAdm(int iArgc)
{
	AdminHash();
	ReplyToCommand(0, "Rehash Admin");
	return Plugin_Handled;
}

public Action CommandWMute(int iArgc)
{
	char sArgs[256],
		sArg[4][264];
	GetCmdArgString(sArgs, sizeof(sArgs));

	int iType, iTime;
	if (ExplodeString(sArgs, " ", sArg, 4, 264) != 4 || !StringToIntEx(sArg[0], iType) || iType < 1 || iType > 4 || !StringToIntEx(sArg[1], iTime))
	{
		LogToFile(g_sLogFile, "Wrong usage of ma_wb_mute");
		return Plugin_Stop;
	}
	
	int iClient = FindTargetSteam(sArg[2]);
	
	if(iClient)
	{
		if (iTime > 0)
			g_iTargenMuteTime[iClient] = GetTime() + iTime;
		else
			g_iTargenMuteTime[iClient] = iTime;
		strcopy(g_iTargetMuteReason[iClient], sizeof(g_iTargetMuteReason[]), sArg[3]);
		ReplyToCommand(0, "ok");
		switch(iType)
		{
			case TYPEMUTE:		AddMute(iClient, iTime);
			case TYPEGAG: 		AddGag(iClient, iTime);
			case TYPESILENCE:	AddSilence(iClient, iTime);
		}	
	}
	else
		ReplyToCommand(0, "nope");
	
#if DEBUG
	LogToFile(g_sLogFile,"CommandWMute: %s", sArgs);
#endif
	
	return Plugin_Handled;
}

public Action CommandWUnMute(int iArgc)
{
	char sArgs[256],
		sArg[2][64];
	GetCmdArgString(sArgs, sizeof(sArgs));

	int iType;
	if (ExplodeString(sArgs, " ", sArg, 2, 64) != 2 || !StringToIntEx(sArg[0], iType) || iType < 1 || iType > 4)
	{
		LogToFile(g_sLogFile, "Wrong usage of ma_wb_unmute");
		return Plugin_Stop;
	}
	
	int iClient = FindTargetSteam(sArg[1]);
	
	if(iClient)
	{
		ReplyToCommand(0, "ok");
		switch(iType)
		{
			case TYPEMUTE:		UnMute(iClient);
			case TYPEGAG: 		UnGag(iClient);
			case TYPESILENCE:	UnSilence(iClient);
		}	
	}
	else
		ReplyToCommand(0, "nope");
	
#if DEBUG
	LogToFile(g_sLogFile,"CommandWUnMute: %s", sArgs);
#endif
	
	return Plugin_Handled;
}

public Action CommandWBan(int iArgc)
{
	char sArgs[256],
		sArg[1][64];
	GetCmdArgString(sArgs, sizeof(sArgs));

	if (!ExplodeString(sArgs, " ", sArg, 1, 64))
	{
		LogToFile(g_sLogFile, "Wrong usage of ma_wb_ban");
		return Plugin_Stop;
	}
	
	int iClient;
	if (strncmp(sArg[0], "STEAM_", 6) == 0)
		iClient = FindTargetSteam(sArg[0]);
	else
		iClient = FindTargetIp(sArg[0]);
	
	if(iClient)
	{
		ReplyToCommand(0, "ok");
		CheckClientBan(iClient);
	}
	else
		ReplyToCommand(0, "nope");
	
#if DEBUG
	LogToFile(g_sLogFile,"CommandWBan: %s", sArgs);
#endif
	
	return Plugin_Handled;
}

public Action CommandWGetInfo(int iArgc)
{
	char sArgs[256],
		sArg[1][64];
	GetCmdArgString(sArgs, sizeof(sArgs));

	if (!ExplodeString(sArgs, " ", sArg, 1, 64))
	{
		LogToFile(g_sLogFile, "Wrong usage of ma_wb_getinfo");
		return Plugin_Stop;
	}

	int iClient = FindTargetName(sArg[0]);
	
	if(iClient)
	{
		char sSteamID[MAX_STEAMID_LENGTH],
			sIp[MAX_IP_LENGTH]; 
		GetClientAuthId(iClient, TYPE_STEAM, sSteamID, sizeof(sSteamID));
		GetClientIP(iClient, sIp, sizeof(sIp));
		ReplyToCommand(0, "%s|%s", sSteamID, sIp);
	}
	else
		ReplyToCommand(0, "nope");
	
#if DEBUG
	LogToFile(g_sLogFile,"CommandWGetInfo: %s", sArgs);
#endif
	
	return Plugin_Handled;
}
