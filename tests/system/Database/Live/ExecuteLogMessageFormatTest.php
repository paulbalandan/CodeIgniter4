<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ExecuteLogMessageFormatTest extends CIUnitTestCase
{
    public function testLogMessageWhenExecuteFailsShowFullStructuredBacktrace(): void
    {
        $db = Database::connect(getShared: false);
        $this->setPrivateProperty($db, 'DBDebug', false);

        $sql = 'SELECT * FROM some_table WHERE id = ? AND status = ? AND author = ?';
        $db->query($sql, [3, 'live', 'Rick']);

        $message = match ($db->DBDriver) {
            'MySQLi'  => 'Table \'test.some_table\' doesn\'t exist',
            'Postgre' => 'pg_query(): Query failed: ERROR:  relation "some_table" does not exist',
            'SQLite3' => 'Unable to prepare statement: no such table: some_table',
            'OCI8'    => 'oci_execute(): ORA-00942: table or view does not exist',
            'SQLSRV'  => '[Microsoft][ODBC Driver 18 for SQL Server][SQL Server]Invalid object name \'some_table\'.',
            default   => 'Unknown DB error',
        };
        $messageFromLogs = explode("\n", $this->getPrivateProperty(TestLogger::class, 'op_logs')[0]['message']);

        $this->assertSame($message, array_shift($messageFromLogs));

        if ($db->DBDriver === 'Postgre') {
            $messageFromLogs = array_slice($messageFromLogs, 2);
        } elseif ($db->DBDriver === 'OCI8') {
            $messageFromLogs = array_slice($messageFromLogs, 1);
        }

        $this->assertMatchesRegularExpression('/^in \S+ on line \d+\.$/', array_shift($messageFromLogs));

        foreach ($messageFromLogs as $line) {
            $this->assertMatchesRegularExpression('/^\s*\d* .+(?:\(\d+\))?: \S+(?:(?:\->|::)\S+)?\(.*\)$/', $line);
        }
    }
}