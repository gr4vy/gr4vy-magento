<?php
namespace Gr4vy\Magento\Cron;

use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Gr4vy\Magento\Model\ResourceModel\Transaction\CollectionFactory;
use Gr4vy\Magento\Model\Client\Transaction as Gr4vyTransaction;

class UpdateOrderStatus
{
    const BATCH_SIZE = 100;

    /**
     * @var TransactionRepositoryInterface
     */
    public $transactionRepository;

    /**
     * @var CollectionFactory
     */
    public $transactionCollectionFactory;

    /**
     * @var Gr4vyTransaction
     */
    public $transactionApi;

    /**
     * @var \Gr4vy\Magento\Helper\Order
     */
    public $gr4vyOrder;

    /**
     * @var \Gr4vy\Magento\Helper\Logger
     */
    public $gr4vyLogger;

    public function __construct(
        TransactionRepositoryInterface $transactionRepositoryInterface,
        CollectionFactory $transactionCollectionFactory,
        Gr4vyTransaction $transactionApi,
        \Gr4vy\Magento\Helper\Order $gr4vyOrder,
        \Gr4vy\Magento\Helper\Logger $gr4vyLogger
    )
    {
        $this->transactionRepository = $transactionRepositoryInterface;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->transactionApi = $transactionApi;
        $this->gr4vyOrder = $gr4vyOrder;
        $this->gr4vyLogger = $gr4vyLogger;
    }

    /**
     * retrieve and order order statuses
     *
     * @return void
     */
    public function execute()
    {
        $statuses = $this->gr4vyOrder->getGr4vyTransactionStatuses();

        $collection = $this->transactionCollectionFactory->create();
        $collection->addFieldToFilter('status', ['in' => $statuses['processing']]);
        $collection->getSelect()->limit(self::BATCH_SIZE);

        // process transactions
        foreach ($collection as $row){
            try {
                $dataModel = $row->getDataModel();
                $gr4vy_status = $this->transactionApi->getStatus($dataModel->getGr4vyTransactionId());

                if (in_array($gr4vy_status, $statuses['cancel'])) {
                    $this->gr4vyOrder->cancelOrderByGr4vyTransactionId($dataModel->getGr4vyTransactionId());
                }

                if (in_array($gr4vy_status, $statuses['refund']) || in_array($gr4vy_status, $statuses['success'])) {
                    // ignore refunded or succeeded orders
                }

                // Update Gr4vy transaction record to make processed transactions
                $dataModel->setStatus($gr4vy_status);
                $this->transactionRepository->save($dataModel);
            }
            catch (\Exception $e) {
                $this->gr4vyLogger->logException($e);
            }
        }
    }
}
