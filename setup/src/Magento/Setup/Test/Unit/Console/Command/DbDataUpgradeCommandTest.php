<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Console\Command\DbDataUpgradeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DbDataUpgradeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $installerFactory = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $installer = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);

        $installerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($installer));
        $installer->expects($this->once())->method('installDataFixtures');

        $commandTester = new CommandTester(new DbDataUpgradeCommand($installerFactory));
        $commandTester->execute([]);
    }
}
