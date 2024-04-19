<?php

require 'vendor/autoload.php';

use LucidFrame\Console\ConsoleTable;

class HMAC
{
    public function __construct(public string $key, public array $arg)
    {
        $this->key = $key;
        $this->arg = $arg;
    }
    public function hmac()
    {
        $strArg = implode(' ', $this->arg);
        return hash_hmac('sha3-256', $strArg, $this->key);
    }
}

class Menu
{
    public function __construct(public array $arg)
    {
        $this->arg = $arg;
    }
    public function getList()
    {
        foreach ($this->arg as $n => $row) {
            $list[] = ($n + 1) . ' - ' . $row;
        }
        $list[] = '0 - exit';
        $list[] = '? - help';
        return implode("\n", $list);
    }
}

class ResultGame
{
    public function __construct(public int $userMove, public int $computerMove, public int $numberMoves)
    {
        $this->userMove = $userMove;
        $this->computerMove = $computerMove;
        $this->numberMoves = $numberMoves;
    }
    public function choice()
    {
        $n = floor($this->numberMoves / 2);
        $result = (($this->userMove - $this->computerMove + $n + $this->numberMoves)
            % $this->numberMoves - $n);
        if ($result > 0) {
            return 'Win';
        } elseif ($result == 0) {
            return 'Draw';
        } else {
            return 'Lose';
        }
    }
}

class HelpTable extends ResultGame
{
    public function __construct(public array $arg, public int $countArg)
    {
        $this->arg = $arg;
        $this->countArg = $countArg;
    }

    public function create()
    {
        $table = new ConsoleTable();
        $table->addHeader('v PC\User >');

        foreach ($this->arg as $arg) {
            $table->addHeader($arg);
        }

        for ($userMove = 0; $userMove <= $this->countArg - 1; $userMove++) {
            $columns = [];
            $columns[] = $this->arg[$userMove];
            for ($computerMove = 0; $computerMove <= $this->countArg - 1; $computerMove++) {
                $result = new ResultGame($userMove, $computerMove, $this->countArg);
                $result = $result->choice();
                $columns[] = $result;
            }

            $table->addRow($columns);
        }

        $table
            ->showAllBorders()
            ->display();

        return $table;
    }
}
$arg = array_slice($argv, 1);
$countArg = count($arg);
if ($countArg % 2 === 1 && $countArg >= 3) {
    $hma = new HMAC(sodium_crypto_secretbox_keygen(), $arg);
    echo "HMAC:\n" . $hma->hmac() . "\n";

    $list = new Menu($arg);
    echo 'Available moves:' . "\n" . $list->getList() . "\n";

    echo "Enter your move: ";
    $userMove = trim(fgets(STDIN));

    if ($userMove >= 1 && $userMove <= $countArg) {
        echo 'Your move: ' . $arg[$userMove - 1] . "\n";

        $randomComputerMove = rand(1, $countArg);
        $computerMove = $arg[$randomComputerMove - 1];
        echo 'Computer move: ' . $computerMove . "\n";

        $resultGame = new ResultGame($userMove, $randomComputerMove, $countArg);
        if ($resultGame->choice() === 'Draw') {
            echo 'Draw' . "\n";
        } else {
            echo 'You ' . $resultGame->choice() . "\n";
        }

        echo 'HMAC key:' . "\n" . bin2hex($hma->key);
    } elseif ($userMove == '?') {
        $table = new HelpTable($arg, $countArg);
        echo 'Information about game:' . "\n";
        $table->create();
    } else {
        exit('');
    }
} else {
    echo 'There must be an odd number of arguments.';
}
