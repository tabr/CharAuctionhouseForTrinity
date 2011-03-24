<?php
$me=2;
define (LANG	,'RU');

##############
require('config/config.php');
require('kernel/auc.php');
require_once('lang/'.LANG.'.php');
mysql_connect(DB_AUC_HOST, DB_AUC_USER, DB_AUC_PASS);
mysql_select_db(DB_AUC_BASE);
#die(DB_AUC_BASE);
$UI = new CustomUserInterface();
$A = new CharAuctionhouse($me);
$tmp	= $A->Error->GetLast();
if (!empty($tmp))
	{
	$UI->ShowMessage($tmp);
	}
$A->CheckExpired();
if (isset($_GET['add']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->CreateAuction($_GET['guid'],$_GET['startbid'],$_GET['expires'],$_GET['buyout']))
			{
			$UI->ShowMessage(MSG_AUCTION_CREATE_OK);
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage(MSG_AUCTION_CREATE_ERROR.': '.addslashes($A->Error->GetLast()));
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage(MSG_TRANSACTION_START_ERROR);
		}
	}
if (isset($_GET['cancelmy']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->CancelMyAuction($_GET['guid']))
			{
			$UI->ShowMessage(MSG_AUCTION_CANCEL_OK);
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage(MSG_AUCTION_CANCEL_ERROR.': '.addslashes($A->Error->GetLast()));
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage(MSG_TRANSACTION_START_ERROR);
		}
	}

if (isset($_GET['dobuyout']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->Buyout($_GET['guid']))
			{
			$UI->ShowMessage(MSG_AUCTION_BYUOUT_OK);
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage(MSG_AUCTION_BYUOUT_ERROR.': '.addslashes($A->Error->GetLast()));
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage(MSG_TRANSACTION_START_ERROR);
		}
	}
if (isset($_GET['dobid']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->Bid($_GET['guid'],$_GET['bid']))
			{
			$UI->ShowMessage(MSG_AUCTION_BID_OK);
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage(MSG_AUCTION_BID_ERROR.': '.addslashes($A->Error->GetLast()));
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage(MSG_TRANSACTION_START_ERROR);
		}
	}
#создаем ПРОИЗВОЛЬНУЮ ссылку
$UI->CreateCustomAuctionLink('армори','http://крутой-сервер/armory/character-sheet.xml?r=реалм&cn={$name}');
$UI->CreateCustomAuctionLink('ставка','DoBid({$guid})',1);
$UI->CreateCustomAuctionLink('выкупить {$name}','index.php?dobuyout&guid={$guid}');
$UI->CreateCustomAuctionLink('отменить','index.php?cancelmy&guid={$guid}');
$UI->CreateCustomAuctionCol('столбец','пример{$name}');
$UI->CreateCustomAuctionCol('userbar','<img src="http://крутой-сервер/userbar/{$name}.png">');
$UI->ShowAuctions($A->GetAuction());
?>
<script language="JavaScript" src="js/calendar_db_ru.js"></script>
<link rel="stylesheet" href="css/calendar.css">

<form name="addform" action = "index.php">
guid:<input type="text" name="guid">
expires:<input type="text" name="expires" id="expires" value="">
<script language="JavaScript">
var m_expires = document.getElementById("expires");
var tmp = new Date();
tmp.setDate(tmp.getDate()+3);
m_expires.value = f_tcalGenerDate(tmp);
new tcal ({
'formname': 'addform',
'controlname': 'expires'
});
</script>
startbid:<input type="text" name="startbid">
buyout:<input type="text" name="buyout">
<input type="submit" name="add" value="добавить аукцион">

</form>
<script>
function DoBid(guid)
	{
	guid = parseInt(guid);
	if (guid < 1)
		alert("Ошибка в номере персонажа");
	var answer = prompt("Какая ставка?")
	if (answer)
		{
		var bid = parseInt(answer);
		if (bid>0)
			{
			var link = "index.php?dobid&guid="+guid+"&bid="+bid;
			document.location.href=link;
			}
		else
			alert("ошибка в ставке");
		}
	}
</script>
