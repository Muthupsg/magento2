<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Controller\Add;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Url\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for Magento\ProductAlert\Controller\Add\Stock class.
 *
 * @magentoAppIsolation enabled
 */
class StockTest extends AbstractController
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Data
     */
    private $dataUrlHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connectionMock;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();

        $this->customerSession = $this->objectManager->get(Session::class);
        $this->dataUrlHelper = $this->objectManager->get(Data::class);

        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->connectionMock = $this->resource->getConnection();
    }

    /**
     * @magentoAppArea     frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSubscribe()
    {
        $productId = 1;
        $customerId = 1;

        $this->customerSession->setCustomerId($customerId);

        $encodedParameterValue = $this->getUrlEncodedParameter($productId);
        $this->getRequest()->setMethod('GET');
        $this->getRequest()->setQueryValue('product_id', $productId);
        $this->getRequest()->setQueryValue(Action::PARAM_NAME_URL_ENCODED, $encodedParameterValue);
        $this->dispatch('productalert/add/stock');

        $select = $this->connectionMock->select()->from($this->resource->getTableName('product_alert_stock'))
                                       ->where('`customer_id` LIKE ?', '1');
        $result = $this->connectionMock->fetchAll($select);
        $this->assertCount(1, $result);
    }

    /**
     * @param int $productId
     *
     * @return string
     */
    private function getUrlEncodedParameter(int $productId):string
    {
        $baseUrl = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getBaseUrl();
        $encodedParameterValue = urlencode(
            $this->dataUrlHelper->getEncodedUrl($baseUrl . 'productalert/add/stock/product_id/' . $productId)
        );

        return $encodedParameterValue;
    }
}
