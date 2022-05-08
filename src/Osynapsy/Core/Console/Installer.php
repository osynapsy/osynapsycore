<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Console;

use Osynapsy\Db\DbFactory;
use Osynapsy\Console\Terminal;

class Installer
{
    private $terminal;
    private $answer;
    private $questions = [
        'dbtype' => "Digit db type (mysql, sqlite, oracle, postgres) : ",
        'dbhost' => "Digit db host : ",
        'dbname' => "Digit db name : ",
        'dbuser' => "Digit db user : ",
        'dbpwd'  => "Digit db pass : "
    ];

    public function __construct()
    {
        $this->terminal = new Terminal();
    }

    public function run()
    {
        $this->configureDatabase();
        $this->finish();
    }

    private function configureDatabase()
    {
        $i = 1;
        foreach ($this->questions as $key => $question) {
            $this->printQuestion('db',$key,$i.') '.$question);
            print PHP_EOL;
            $i++;
        }
        $this->testDatabaseConnection(implode(':',$this->answer));
    }

    private function testDatabaseConnection($connectionString)
    {
        try {
            $this->printLnMessage("Db Connetting test.....");
            $DbFactory = new DbFactory();
            $DbFactory->createConnection($connectionString);
            $this->printLnMessage('Connection ok');
        } catch (Exception $e) {
            $this->printMessage($e->getMessage());
            return;
        }

    }

    protected function printLnMessage($message)
    {
        print $message.PHP_EOL;
        sleep(1);
    }

    private function printQuestion($section, $key, $question)
    {
        $answer = $this->terminal->input($question);
        $this->answer[$key] = trim($answer);
    }

    private function finish()
    {
        file_put_contents('config.ini',implode(':',$this->answer));
        print $this->terminal->label(print_r($this->answer,true));
        print PHP_EOL;
    }
}
