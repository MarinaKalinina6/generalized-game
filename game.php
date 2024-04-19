
<?php

require 'vendor/autoload.php';

use LucidFrame\Console\ConsoleTable;

class HMAC
{
    public static function hmac(string $key, array $arg)
    {
        $strArg = implode(' ', $arg);
        return hash_hmac('sha3-256', $strArg, $key);
    }
}

class Menu
{
    public function __construct(private array $arg)
    {
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
    public static function choice( int $userMove, int $computerMove, int $numberMoves)
    {
        $n = floor($numberMoves / 2);
        $result = (($userMove - $computerMove + $n + $numberMoves)
            % $numberMoves - $n);
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
    public function __construct(private array $arg, private int $countArg)
    {
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
                $result =  ResultGame::choice($userMove, $computerMove, $this->countArg);
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
function get_array_duplicates( $arg ){
	return array_diff_assoc( $arg, array_unique( $arg ) );
}
$duplicates = get_array_duplicates( $arg );

if ($countArg % 2 === 1 && $countArg >= 3 && $duplicates === []) {
    $hashKey = random_bytes(32);

    echo "HMAC:\n" . HMAC::hmac($hashKey, $arg) . "\n";

    $list = new Menu($arg);
    echo 'Available moves:' . "\n" . $list->getList() . "\n";

    echo "Enter your move: ";
    $userMove = trim(fgets(STDIN));

    if ($userMove >= 1 && $userMove <= $countArg) {
        echo 'Your move: ' . $arg[$userMove - 1] . "\n";

        $randomComputerMove = rand(1, $countArg);
        $computerMove = $arg[$randomComputerMove - 1];
        echo 'Computer move: ' . $computerMove . "\n";

        $resultGame = ResultGame::choice($userMove, $randomComputerMove, $countArg);

        if ($resultGame === 'Draw') {
            echo 'Draw' . "\n";
        } else {
            echo 'You ' . $resultGame . "\n";
        }

        echo 'HMAC key:' . "\n" . bin2hex($hashKey) . "\n";
    } elseif ($userMove == '?') {
        $table = new HelpTable($arg, $countArg);
        echo 'Information about game:' . "\n";
        $table->create();
    } else {
        exit('');
    }
} else {
    echo 'Incorrect input values.';
}

