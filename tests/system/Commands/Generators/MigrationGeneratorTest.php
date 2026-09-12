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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class MigrationGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        foreach (glob(APPPATH . 'Database/Migrations/*_*.php') as $file) {
            unlink($file);
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function getContents(string $basename): string
    {
        $files = glob(APPPATH . 'Database/Migrations/*_' . $basename . '.php');
        $this->assertCount(1, $files);

        $contents = file_get_contents($files[0]);
        $this->assertIsString($contents);

        return $contents;
    }

    public function testGenerateMigration(): void
    {
        command('make:migration database');

        $this->assertMatchesRegularExpression(
            '#^\nFile created: APPPATH/Database/Migrations/\d{4}-\d{2}-\d{2}-\d{6}_Database\.php\n$#',
            $this->getUndecoratedBuffer(),
        );

        $contents = $this->getContents('Database');
        $this->assertStringContainsString('namespace App\Database\Migrations;', $contents);
        $this->assertStringContainsString('class Database extends Migration', $contents);
        $this->assertStringNotContainsString('$DBGroup', $contents);
    }

    public function testGenerateMigrationWithOptionSession(): void
    {
        command('make:migration --session');

        $contents = $this->getContents('CreateCiSessionsTable');
        $this->assertStringContainsString('class CreateCiSessionsTable extends Migration', $contents);
        $this->assertStringContainsString("protected \$DBGroup = 'default';", $contents);
        $this->assertStringContainsString("\$this->forge->addKey('id', true);", $contents);
        $this->assertStringContainsString("\$this->forge->createTable('ci_sessions', true);", $contents);
    }

    public function testSessionIgnoresNameArgument(): void
    {
        command('make:migration database --session');

        $this->assertStringContainsString('_CreateCiSessionsTable.php', $this->getUndecoratedBuffer());
        $this->assertFileDoesNotExist(APPPATH . 'Database/Migrations/Database.php');
    }

    public function testGenerateMigrationWithOptionTableAndDbGroup(): void
    {
        command('make:migration --session --table logger --dbgroup tests');

        $contents = $this->getContents('CreateLoggerTable');
        $this->assertStringContainsString("protected \$DBGroup = 'tests';", $contents);
        $this->assertStringContainsString("\$this->forge->createTable('logger', true);", $contents);
        $this->assertStringContainsString("\$this->forge->dropTable('logger', true);", $contents);
    }

    public function testGenerateMigrationWithShortcuts(): void
    {
        command('make:migration --session -t logger -g tests');

        $contents = $this->getContents('CreateLoggerTable');
        $this->assertStringContainsString("protected \$DBGroup = 'tests';", $contents);
        $this->assertStringContainsString("\$this->forge->createTable('logger', true);", $contents);
    }

    public function testGenerateMigrationWithOptionSuffix(): void
    {
        command('make:migration database --suffix');

        $this->assertStringContainsString('class DatabaseMigration extends Migration', $this->getContents('DatabaseMigration'));
    }
}
