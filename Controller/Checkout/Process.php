<?php
declare(strict_types=1);

namespace Gr4vy\Magento\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Gr4vy\Magento\Model\Checkout\ProcessResponse;

/**
 * Process gr4vy response
 */
class Process implements ActionInterface
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProcessResponse
     */
    private $processResponse;

    /**
     * Process constructor.
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     * @param ProcessResponse $processResponse
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        ProcessResponse $processResponse
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        $this->processResponse = $processResponse;
    }

    /**
     * Calls model class to process gr4vy response disable quote after success transaction
     *
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        $params = $this->context->getRequest()->getParams();
        $orderId = $params['orderId'];
        //Process Gr4vy response data
        $this->processResponse->processGr4vyResponse($orderId);

        return $this->resultJsonFactory->create();
    }
}
