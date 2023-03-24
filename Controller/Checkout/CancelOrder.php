<?php
declare(strict_types=1);

namespace Gr4vy\Magento\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Gr4vy\Magento\Helper\Order as OrderHelper;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Sales\Model\Order;

/**
 * Cancel magento order if gr4vy transaction is failed
 */
class CancelOrder implements ActionInterface
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ResultRedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * CancelOrder constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param OrderHelper $orderHelper
     * @param Validator $formKeyValidator
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        OrderHelper $orderHelper,
        Validator $formKeyValidator
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->orderHelper = $orderHelper;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Cancel order if gr4vy transaction fails
     *
     * @return ResponseInterface|Json|Redirect|ResultInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->context->getRequest())) {
            return $this->resultRedirectFactory->create()
                ->setPath('/');
        }
        $params = $this->context->getRequest()->getParams();
        $orderId = isset($params['orderId']) ? $params['orderId'] : null;
        if ($orderId) {
            $this->orderHelper->cancelMagentoOrder($orderId);
            $this->orderHelper->updateOrderHistoryData(
                $orderId,
                Order::STATE_CANCELED,
                __('Order has been cancelled by gr4vy payment response.')
            );
        }

        return $this->resultJsonFactory->create();
    }
}
