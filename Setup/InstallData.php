<?php

namespace Wezz\Yehhpay\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Wezz\Yehhpay\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('sales_order_payment');

        $setup->getConnection()->addColumn(
            $tableName,
            'yehhpay_transaction_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Yehhpay Transaction Id'
            ]
        );

        $setup->getConnection()->addColumn(
            $tableName,
            'yehhpay_transaction_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Yehhpay Transaction Date'
            ]
        );

        $setup->endSetup();
    }
}
