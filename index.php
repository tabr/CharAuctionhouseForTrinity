<?php
require('config.php');
require('auc.php');
mysql_connect(DB_AUC_HOST, DB_AUC_USER, DB_AUC_PASS);
mysql_select_db(DB_AUC_BASE);
$UI = new CustomUserInterface();
$me=2;
#$b  = new Balance($me);var_dump($b->Replenish(1000));
$A = new CharAuctionhouse($me);
$A->CheckExpired();
if (isset($_GET['add']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->CreateAuction($_GET['guid'],$_GET['startbid'],$_GET['expires'],$_GET['buyout']))
			{
			$UI->ShowMessage('аукцион создан');
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage('ошибка создания аукциона: '.$A->Error->GetLast());
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage('ошибка начала транзакции');
		}
	}
if (isset($_GET['cancelmy']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->CancelMyAuction($_GET['guid']))
			{
			$UI->ShowMessage('аукцион отменен');
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage('ошибка отмены аукциона: '.$A->Error->GetLast());
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage('ошибка начала транзакции');
		}
	}

if (isset($_GET['dobuyout']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->Buyout($_GET['guid']))
			{
			$UI->ShowMessage('персонаж выкуплен');
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage('ошибка выкупа персонажа: '.$A->Error->GetLast());
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage('ошибка начала транзакции');
		}
	}
if (isset($_GET['dobid']))
	{
	if ($UI->mysqlTransactionStart())
		{
		if ($A->Bid($_GET['guid'],$_GET['bid']))
			{
			$UI->ShowMessage('ставка принята');
			$UI->mysqlTransactionCommit();
			}
		else
			{
			$UI->ShowMessage('ошибка ставки: '.$A->Error->GetLast());
			$UI->mysqlTransactionRollback();
			}
		}
	else
		{
		$UI->ShowMessage('ошибка начала транзакции');
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
<form action = "index.php">
guid:<input type="text" name="guid">
expires:<input type="text" name="expires" value="2011-04-01">
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
