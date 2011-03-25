<?php
/*
Автор Tabr: полдня для trinity-core.ru
version: 0.1.10
*/
class Log
	{
	const LEVEL_STRING	= 0;
	const LEVEL_NOTIFY	= 1;
	const LEVEL_ERROR	= 2;
	const LEVEL_DEBUG	= 3;
	const LEVEL_ELSE	= -1;
	const LEVEL_ALL		= -2;
	const LOGS_DIR		= LOGS_DIR;
	protected $path;
	protected $account;
	public function CheckLogsPath()
		{
		if (!is_writable(self::LOGS_DIR))
			{
			/*может, созданы и доступны для записи файлы?*/
			if (is_writable($path[self::LEVEL_STRING])
			&& is_writable($path[self::LEVEL_NOTIFY])
			&& is_writable($path[self::LEVEL_ERROR])
			&& is_writable($path[self::LEVEL_DEBUG])
			&& is_writable($path[self::LEVEL_ELSE])
			&& is_writable($path[self::LEVEL_ALL])
			)
				return true;
			return false;
			}
		return true;
		}
	public function Write($str,$level=0)
		{
		switch ($level)
			{
			case self::LEVEL_STRING:
			$fp	= fopen($this->path[self::LEVEL_STRING],'a');
			break;
			case self::LEVEL_NOTIFY:
			$fp	= fopen($this->path[self::LEVEL_NOTIFY],'a');
			break;
			case self::LEVEL_ERROR:
			$fp	= fopen($this->path[self::LEVEL_ERROR],'a');
			break;
			case self::LEVEL_DEBUG:
			$fp	= fopen($this->path[self::LEVEL_DEBUG],'a');
			break;
			default:
			$fp	= fopen($this->path[self::LEVEL_ELSE],'a');
			}
		$str	= date('Y-m-d H:i:s').'['.$this->account.']['.$_SERVER['REMOTE_ADDR'].']['.$level.']	'.$str."\n";
		fputs($fp,$str);
		fclose ($fp);
		$fpAll	= fopen($this->path[self::LEVEL_ALL],'a');
		fputs($fpAll,$str);
		fclose ($fpAll);
		}
	public function outString($str)
		{
		$this->Write($str,self::LEVEL_STRING);
		}
	public function outNotify($str)
		{
		$this->Write($str,self::LEVEL_NOTIFY);
		}
	public function outError($str)
		{
		$this->Write($str,self::LEVEL_ERROR);
		}
	public function outDebug($str)
		{
		$this->Write($str,self::LEVEL_DEBUG);
		}
	function __construct($account = 0)
		{
		$this->account	= $account;
		$this->path[self::LEVEL_STRING]	= self::LOGS_DIR.'/string.log';
		$this->path[self::LEVEL_NOTIFY]	= self::LOGS_DIR.'/notify.log';
		$this->path[self::LEVEL_ERROR]	= self::LOGS_DIR.'/error.log';
		$this->path[self::LEVEL_DEBUG]	= self::LOGS_DIR.'/debug.log';
		$this->path[self::LEVEL_ELSE]	= self::LOGS_DIR.'/else.log';
		$this->path[self::LEVEL_ALL]	= self::LOGS_DIR.'/all.log';
		}
	}
class Errors
	{
	protected $errors;
	protected $counter;
	public function Add($text)
		{
		$this->errors[++$this->counter]=$text;
		}
	public function GetLast()
		{
		return $this->errors[$this->counter];
		}
	function __construct()
		{
		$this->counter = 0;
		}
	}
interface IUserInterface
	{
	public function CreateCustomAuctionLink($name,$link,$type = 0/*0=link,1=onClick*//*,$position - надобы сделать*/);
	/*создает дополнительную ссылку в каждой строке лотов*/
	public function CreateCustomAuctionCol($name,$text);
	/*создает колонку с произвольным текстом в каждой строке лотов*/
	public function ShowMessage($m/*essage*/);
	/*отображение пользователю сообщения(тут все зависит от того, как реализован интерфейс пользователя(например айакс,хтмл,etc...))*/
	public function ShowAuctions($a/*uctions*/, $h/*ead*/ = array()); /*возможно стоит каким-то другим МАКАРОМ реализовать...*/
	/*отображение пользователю ВСЕХ лотов*/
	}
class UserInterface implements IUserInterface
	{
	/*давно хотел попробывать*/
	/*сейчас класс несколько не корректен...*/
	protected $insideJS;
	protected $mainLink;
	protected $customAuctionLinks;
	protected $customAuctionCols;
	protected $sLog;
	protected $UC;
	public $Error;
	public function mysqlTransactionStart()
		{
		if (mysql_query('SET AUTOCOMMIT=0') && mysql_query('START TRANSACTION'))
			return true;
		return false;
		}
	public function mysqlTransactionCommit()
		{
		if (mysql_query('COMMIT'))
			return true;
		return false;
		}
	public function mysqlTransactionRollback()
		{
		mysql_query('ROLLBACK');
			return false;
		}
	public function CreateCustomAuctionLink($name,$link,$type = 0/*0=link,1=onClick*//*,$position - надобы сделать*/)
		{
		/*
		$guid - гуид персонажа
		*/
		$this->customAuctionLinks[] = array('name' => $name, 'link'=>$link, 'type'=>$type);
		}
	protected function GetCustomAuctionLinks($auctions)
		{
#		$auction = array();
#		return $auction;
#		echo '<PRE>';var_dump($auctions);die();
		if (!empty($this->customAuctionLinks))
			{
			foreach ($auctions as &$auction)
				{
				foreach ($this->customAuctionLinks as $auc)
					{
					$name	= &$auc['name'];
					$link	= &$auc['link'];
					$type	= &$auc['type'];
					$link	= $this->ParseAuctionText($link,$auction);
					$name	= $this->ParseAuctionText($name,$auction);
#					$name	= str_replace('{$guid}',$auction['guid'],$name);
#					var_dump($type);die();
					switch ($type)
						{
						case 1:
						$auction[$name] = '<a href="#" onClick="'.$link.'">'.$name.'</a>';
						break;
						default: //0
						$auction[$name] = '<a href="'.$link.'">'.$name.'</a>';
						}
					}
				}
			}
#		echo '<PRE>';var_dump($auctions);die();
		reset($auctions);
		return $auctions;
		}
	public function CreateCustomAuctionCol($name,$text)
		{
		$this->customAuctionCols[] = array('name'=>$name, 'text'=>$text);
		}
	protected function GetCustomAuctionCols($auctions)
		{
		if (!empty($this->customAuctionCols))
			{
			foreach ($auctions as &$auction)
				{
				foreach ($this->customAuctionCols as $col)
					{
					$col['text']	= $this->ParseAuctionText($col['text'],$auction);
					$col['name']	= $this->ParseAuctionText($col['name'],$auction);
					$auction[$col['name']]	= $col['text'];
					}
#				$text=str_replace();
				}
			}
		reset($auctions);
		return $auctions;
		}
	protected function ParseAuctionText($text,$a)/*плохое название, лень думать*/
		{
		$text	= str_replace('{$guid}',$a['guid'],$text);
		$text	= str_replace('{$name}',$a['name'],$text);
		return $text;
		}
	public function ShowMessage($m/*essage*/)
		{
		$m='alert("'.$m.'")';
		if (!$this->insideJS)
			$m=$this->AddScriptTag($m);
		echo $m;
		}
	protected function AppendName($a)
		{
		foreach ($a as &$tmp)
			{
			$tmp['name'] = $this->UC->GetNames($tmp['guid']);
			}
		reset($a);
		return $a;
		}
	public function ShowAuctions($a/*uctions*/, $h/*ead*/ = array()) /*возможно стоит каким-то другим МАКАРОМ реализовать...*/
		{
		echo '<table border="1" width="100%">';
		if (empty($a))
			{
			echo '<tr><td align="center">(пусто)</td></tr>';
#			return true;
			}
		else
			{
			$a	= $this->AppendName($a);
			$a	= $this->GetCustomAuctionLinks($a);
			$a	= $this->GetCustomAuctionCols($a);
			if (empty($h))
				{
				$h	= array_keys($a[key($a)]);
				}
			echo '<tr><td>',implode('</td><td>',$h),'</td></tr>';
			foreach ($a as $auction)
				{
				echo '<tr><td>',implode('</td><td>',$auction),'</td></tr>';
				}
			}
		echo '</table>';
		}
	protected function AddScriptTag($str)
		{
		/*добавить бы более корректное...*/
		return '<script>'.$str.'</script>';
		}
	function __construct($insideJS = false)
		{
		$this->insideJS	= (bool)$insideJS;
		$this->mainLink	= 'index.php';
		$this->Error	= new Errors;
		$this->sLog	= new Log;
		$this->UC	= new UserClass;
		}
	}
class Balance
	{
/*Пример класса баланса...*/
	protected $account;
	protected $sLog;
	protected $UC;
	public $Error;
	public function GetBalance()
		{
		$query		= 'SELECT IFNULL(balance,0) FROM balance WHERE account='.$this->account;
		$balance	= (int)@mysql_result(mysql_query($query),0);
		return $balance;
		}
	public function Init()
		{
		mysql_query('INSERT IGNORE INTO balance (account) VALUES ('.$this->account.')');
#		echo mysql_error();
		}
	public function ModBalance($mod /*+value(value) или -value*/)
		{
		$mod		= (int)$mod;
		if ($mod == 0)
			{
			$this->Error->Add('какой смысл изменять баланс на нуль?');
			return false;
			}
		$query		= 'UPDATE balance SET balance=balance+'.$mod.' WHERE account='.$this->account;
		mysql_query($query);
		$rows		= mysql_affected_rows();
		if ($rows >=1)
			{
			return true;
			}
		$this->Error->Add('ошибка: rows=['.$rows.'], '.mysql_error());
		return false;
		}
	public function Replenish($amount)
		{
		return $this->ModBalance((int)$amount);
		}
	public function Spend($amount)
		{
		return $this->ModBalance('-'.(int)$amount);
		}
	function __construct($account)
		{
		$account	= (int)$account;
		$this->account	= $account;
		$this->Error	= new Errors;
		$this->sLog	= new Log;
		$this->UC	= new UserClass($account);
		$this->Init();
		if (!$this->sLog->CheckLogsPath())
			{
			$this->Error->Add('внимание! один или несколько файлов системы логгирование недоступен для записи.');
			}
		}
	}
class CharAuctionhouse extends Balance
	{
	protected $cut;
	const CUT_TYPE_DIRECT	= 1;
	const CUT_TYPE_PERCENT	= 2;
	public function CheckExpired()
		{
		$query	= 'SELECT * FROM CharAuctionhouse WHERE DATEDIFF(created,expires)>0 AND lastbid=0';
		$query	= mysql_query($query);
		if (!$query)
			return true;
		while ($row = mysql_fetch_assoc($query))
			{
			/*персонажи, у которых вышел "срок реализации" и ставок по ним нет*/
			$owner	= &$row['owner'];
			$guid 	= &$row['guid'];
			if ($this->UC->MoveCharToAccount($guid,$owner))
				{
				mysql_query('DELETE FROM FROM CharAuctionhouse WHERE guid='.$guid);
				$this->sLog->outString('CheckExpired(): персонаж '.$guid.' возвращен на аккаунт '.$owner);
				}
			else
				{
				$this->sLog->outError('CheckExpired(): Ошибка возврата персонажа '.$guid.' на аккаунт '.$owner);
				}
			}
		$query	= 'SELECT * FROM CharAuctionhouse WHERE DATEDIFF(created,expires)>0';
		$query	= mysql_query($query);
		while ($row = mysql_fetch_assoc($query))
			{
			/*Те, которые нужно распределить*/
			$mod	= $row['lastbid'];
			$owner	= $row['owner'];
			$guid 	= $row['guid'];
			$bidder	= $row['bidder'];
			if (!$this->UC->UnlockChar($guid))
				{
				$this->Error->Add('ошибка разблокирования персонажа '.$guid);
				continue;
				}
			if ($this->UC->MoveCharToAccount($guid,$bidder))
				{
				$tmp = new Balance($owner);/*как кроссовки*/
				$sum = $mod - $this->GetCut();/*начисляем чуть меньше*/
				$tmp->Replenish($sum);
				mysql_query('DELETE FROM CharAuctionhouse WHERE guid='.$guid);
				$this->sLog->outString('CheckExpired(): персонаж '.$guid.' перемещен на аккаунт '.$bidder);
				}
			}
		}
	public function ForceStop($guid)
		{
		/*еще не тестил*/
		$guid	= (int)$guid;
		if (!$this->UC->IsItMyChar($guid))
			{
			$this->Error->Add('персонаж['.$guid.'] не принадлежит данному аккаунту['.$this->account.']');
			return false;
			}
		$query	= 'SELECT * FROM CharAuctionhouse WHERE guid='.$guid;
		$query	= mysql_query($query);
		$row = mysql_fetch_assoc($query);
		$mod	= $row['lastbid'];
		if ($mod == 0)
			{
			/*ставок нет, просто удаляю*/
			if ($this->CancelMyAuction($guid))
				return true;
			$this->Error->Add('ошибка отмены моего аукциона: '.$this->Error->GetLast());
			return false;
			}
		$owner	= $row['owner'];
		$guid 	= $row['guid'];
		$bidder	= $row['bidder'];
		if (!$this->UC->UnlockChar($guid))
			{
			$this->Error->Add('(2)ошибка разблокирования персонажа '.$guid);
			return false;
			}
		if ($this->UC->MoveCharToAccount($guid,$bidder))
			{
			$tmp = new Balance($owner);/*как кроссовки*/
			$sum = $mod - $this->GetCut();/*начисляем чуть меньше*/
			$tmp->Replenish($sum);
			mysql_query('DELETE FROM CharAuctionhouse WHERE guid='.$guid);
			$this->sLog->outString('закончено форсирование остановки аукциона для персонажа '.$guid);
			return true;
			}
		$this->Error->Add('ошибка, персонаж не был перемещен');
		return false;
		}
	public function GetAuction()
		{
		$query	= mysql_query('SELECT * FROM CharAuctionhouse');
#		echo mysql_error();
		$return	= array();
		if (!$query)
			return $return;
		while ($row = mysql_fetch_assoc($query))
			{
			$return[]=$row;
			}
		return $return;
		}
	public function Buyout($guid)
		{
		$guid	= (int)$guid;
		if (empty($guid))
			{
			$this->Error->Add('пустой ГУИД');
			return false;
			}
		if ($this->IsItMyAuction($guid))
			{
			$this->Error->Add('нельзя выкупать своих персонажей');
			return false;
			}
		$balance = $this->getBalance();
		if ($balance <= 0) /*чувак - банкрот :)*/
			{
			$this->Error->Add('нулевой балланс');
			return false;
			}
		$query		= 'SELECT * FROM CharAuctionhouse WHERE guid='.$guid;
		$query		= mysql_query($query);
		$result		= mysql_fetch_assoc($query);
#		echo '<pre>';var_dump($result);
		$lastbid	= $result['lastbid'];
		$bidder		= $result['bidder'];
		$buyout		= $result['buyout'];
		$owner		= $result['owner'];
		if ($buyout <= 0)
			{
			$this->Error->Add('цена выкупа меньше или равна нулю: '.$buyout);
			return false;
			}
		if ($balance < $buyout)
			{
			$this->Error->Add('недостаточно средств для выкупа');
			return false;
			}
		if ($lastbid != 0)
			{
			/*возврат баланса предыдущему*/
			$tmp = new Balance($bidder);
			if (!$tmp->Replenish($lastbid))
				{
				$this->Error->Add('Ошибка возврата балланса игроку, сделавшему последнюю ставку: '.$tmp->Error->GetLast());
				return false;
				}
			}
		if (!$this->UC->UnlockChar($guid))
			{
			$this->Error->Add('(3)ошибка разблокирования персонажа '.$guid);
			return false;
			}
		if ($this->UC->MoveCharToAccount($guid,$bidder))
			{
			$this->Spend($buyout);/*срубаем с покупателя*/
			$tmp = new Balance($owner);
			$sum = $buyout - $this->GetCut($buyout);/*начисляем чуть меньше))*/
			$tmp->Replenish($sum);
			mysql_query('DELETE FROM CharAuctionhouse WHERE guid='.$guid);
			$rows	= mysql_affected_rows();
			if ($rows == 1)
				{
				$this->sLog->outString('персонаж '.$guid.' выкуплен');
				return true;
				}
			}
		$this->Error->Add('ошибка, персонаж не был перемещен');
		return false;
		}
	public function Bid($guid, $value)
		{
		$guid	= (int)$guid;
		if (empty($guid))
			{
			$this->Error->Add('пустой гуид');
			return false;
			}
		if ($this->IsItMyAuction($guid))
			{
			$this->Error->Add('нельзя делать ставку на своих персонажей');
			return false;
			}
		$value	= (int)$value;
		/*по идее не нужно, т.к. последующие проверки отбросят*/
		if ($value <= 0)
			{
			$this->Error->Add('ставка меньше нуля');
			return false;
			}
		$balance	= $this->GetBalance();
		if ($balance < $value) /*не хватает*/
			{
			$this->Error->Add('недостаточно средств для ставки');
			return false;
			}
		$query		= 'SELECT startbid,lastbid,bidder FROM CharAuctionhouse WHERE guid='.$guid;
		$query		= mysql_query($query);
		$result		= mysql_fetch_assoc($query);
		$startbid	= $result['startbid'];
		$lastbid	= $result['lastbid'];
		$bidder		= $result['bidder'];
		if ($lastbid >= $value) /*текущая ставка меньше или равна существующей*/
			{
			$this->Error->Add('текущая ставка меньше или равна существующей');
			return false;
			}
		if ($startbid > $value)
			{
			$this->Error->Add('ставка меньше минимальной');
			return false;
			}
		/*все норм, начинаю (re)ставку*/
		if ($lastbid != 0)
			{
			/*возврат баланса предыдущему*/
			$tmp = new Balance($bidder);
			if (!$tmp->Replenish($lastbid))
				{
				$this->Error->Add('Ошибка возврата балланса игроку, сделавшему последнюю ставку: '.$tmp->Error->GetLast());
				return false;
				}
			}
		$this->Spend($value);
		$query	= 'UPDATE CharAuctionhouse SET lastbid='.$value.',bidder='.$this->account.' WHERE guid='.$guid;
		mysql_query($query);
		$rows	= mysql_affected_rows();
		if ($rows == 1)
			{
			$this->sLog->outString('ставка ['.$value.'] на персонажа '.$guid.' принята');
			return true;
			}
		$this->Error->Add('ошибка ставки rows=['.$rows.'], '.mysql_error());
		return false;
		}
	public function CreateAuction($guid, $startbid, $expires/*возможные значения смотри  на http://docs.php.net/manual/en/function.strtotime.php*/, $buyout = 0)
		{
		$guid	= (int)$guid;
		if (empty($guid))
			{
			$this->Error->Add('гуид персонажа пуст');
			return false;
			}
		if (!$this->UC->IsItMyChar($guid))
			{
			$this->Error->Add('персонаж['.$guid.'] не принадлежит данному аккаунту['.$this->account.']');
			return false;
			}
		$buyout		= (int)$buyout;
		$startbid	= (int)$startbid;
		if (empty($startbid))
			{
			$this->Error->Add('минимальная ставка пуста!');//может стоит разрешить либо ставку либо выкуп
			return false;
			}
		if (!empty($buyout) && $buyout < $startbid)
			{
			$this->Error->Add('Цена выкупа меньше начальной ставки');
			return false;
			}
		$datecheck	= strtotime($expires);
		if ($datecheck === false)
			{
			$this->Error->Add('ошибочная дата');
			return false;
			}
		$expires	= date('Y-m-d',$datecheck);
		if ($buyout < 0 || $startbid < 0)
			{
			$this->Error->Add('цена выкупа['.$buyout.'] или начальная ставка['.$startbid.'] меньше нуля');
			return false;
			}
		if ($this->AuctionExists($guid))
			{
			$this->Error->Add('на персонажа ['.$guid.'] аукцион уже существует');
			return false;
			}
		if (!$this->UC->LockChar($guid))
			{
			$this->Error->Add('ошибка блокирования персонажа');
			return false;
			}
		$query	= 'INSERT INTO CharAuctionhouse (guid,owner,startbid,buyout,created,expires) VALUES ('.$guid.','.$this->account.','.$startbid.','.$buyout.',NOW(),"'.$expires.'")';
		mysql_query($query);
		$rows	= mysql_affected_rows();
		if ($rows == 1)
			{
			$this->sLog->outString('аукцион на персонажа '.$guid.' создан');
			return true;
			}
		$this->Error->Add('ошибка создания аукциона rows=['.$rows.'], '.mysql_error());
		echo $query;
		return false;
		}
	public function IsItMyAuction($guid)
		{
		$guid	= (int)$guid;
		$query	= 'SELECT 1 FROM CharAuctionhouse WHERE owner='.$this->account.' AND guid='.$guid;
		mysql_query($query);
		$rows	= mysql_affected_rows();
		if ($rows >= 1)
			return true;
		return false;
		}
	public function AuctionExists($guid)
		{
		$guid	= (int)$guid;
		$query	= 'SELECT 1 FROM CharAuctionhouse WHERE guid='.$guid;
		mysql_query($query);
		$rows	= mysql_affected_rows();
		if ($rows >= 1)
			return true;
		return false;
		}
	public function CancelMyAuction($guid/*добавить возможность отмены при существующих ставках.... или не надо*/)
		{
		$guid	= (int)$guid;
		if (!$this->UC->IsItMyChar($guid))
			{
			$this->Error->Add('персонаж['.$guid.'] не принадлежит данному аккаунту['.$this->account.']');
			return false;
			}
		if (!$this->AuctionExists($guid))
			{
			$this->Error->Add('аукцион на персонажа ['.$guid.'] не существует');
			return false;
			}
		$query	= 'DELETE FROM CharAuctionhouse WHERE guid='.$guid.' AND lastbid=0';
#		$query	= 'DELETE FROM CharAuctionhouse WHERE guid='.$guid;
		mysql_query($query);
		$rows	= mysql_affected_rows();
		if ($rows == 1)
			{
			if ($this->UC->UnlockChar($guid))
				{
				$this->sLog->outString('аукцион на персонажа '.$guid.' отменен');
				return true;
				}
			else
				{
				$this->sLog->outString('(4)ошибка разблокирования персонажа '.$guid);
				return false;
				}
			}
		$this->Error->Add('ошибка отмены аукциона(уже есть ставки?) rows=['.$rows.'], '.mysql_error());
		return false;
		}
	public function SetCut($type,$value)
		{
		$this->cut['type']	= (int)$type;
		$this->cut['value']	= (int)$value;
		}
	protected function GetCut($value)
		{
		if (empty($this->cut))
			return 0;
		/*срезаем бабло при ... покупке? у того, кто продает?*/
		switch ($this->cut['type'])
			{
			case self::CUT_TYPE_DIRECT:
			return (int)$this->cut['value'];
			break;

			case self::CUT_TYPE_PERCENT:
			return $this->cut['value']*$value;
			break;
			}
		}
	function __construct($param)
		{
		parent::__construct($param);
		$this->SetCut(self::CUT_TYPE_DIRECT, 10);
		}
	}
########################################################
class CustomUserInterface extends UserInterface
	{
	/*тут переопределять то, что не понравилось в UserInterface*/
	}
class UserClass
	{
	public function UnlockChar($guid)
		{
		/*пример*/
		/*может делать те же действия что и ядро, т.к. не родная...*/
		$guid	= (int)$guid;
		$query	= 'SELECT owner FROM '.DB_AUC_BASE.'.CharAuctionhouse WHERE guid='.$guid;
		$query	= mysql_query($query);
		$owner	= mysql_result($query,0);
		mysql_query('UPDATE characters.character SET account='.$owner.' WHERE guid='.$guid);
		return true;
		}
	public function LockChar($guid)
		{
		/*пример*/
		mysql_query('UPDATE characters.character SET account=0 WHERE online=0 AND guid='.$guid);
		return true;
		}
	public function IsItMyChar($guid)
		{
		return true;
		/*пример*/
		mysql_query('SELECT 1 FROM characters.characters WHERE account='.$account.' AND guid='.$guid);
		$rows	= mysql_affected_rows();
		if ($rows == 1)
			return true;
		return false;
		}
	public function MoveCharToAccount($guid,$toAccount)
		{
		/*пример*/
		$guid		= (int)$guid;
		$toAccount	= (int)$toAccount;
		$query		= 'SELECT COUNT(1) FROM characters.characters WHERE account='.$toAccount;
		$chars		= mysql_result($query,0);
		if ($chars >= MAX_CHARS_ON_ACCOUNT)
			{
			return false;
			}
		mysql_query('UPDATE characters.character SET account='.$toAccount.' WHERE guid='.$guid);
		$rows		= mysql_affected_rows();
		if ($rows >= 1)
			return true;
		return false;
		}
	public function GetNames($guids)
		{
		/*пример*/
		if (is_array($guids))
			{
			foreach ($guids as $id => &$guid)
				{
				$guid=(int)$guid;
				if ($guid<0) /*тоесть если был передан неверный аргумент*/
					{
					unset($guids[$id]);
					}
				}
			/*должны сохраниться ТОЛЬКО int*/
			$DBguids	= implode(',',$guids);
			}
		else
			{
			$DBguids	= (int)$guids;
			}
		if (empty($DBguids))
			return false;
		$query	= 'SELECT guid,name FROM characters.characters WHERE guid IN ('.$DBguids.')';
		$query	= mysql_query($query);
		while ($row = mysql_fetch_row($query))
			{
			$result[$row[0]]	= $row[1];
			}
			if (empty($result))
			return $guids;
		return $result;
		}
	function __construct($account = -1)
		{
		$account	= (int)$account;
		if (empty($account))
			{
			$account	= -1;
			}
		$this->account	= $account;
		}
	}
?>