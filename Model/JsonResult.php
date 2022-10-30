<?php
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

/**
 * Providing controller result objects
 *
 * @internal
 */
class JsonResult
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param JsonFactory   $jsonFactory
     * @param ResultFactory $resultFactory
     * @codeCoverageIgnore
     */
    public function __construct(JsonFactory $jsonFactory, ResultFactory $resultFactory)
    {
        $this->jsonFactory   = $jsonFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Getting back a json result
     *
     * @param int   $httpCode
     * @param array $data
     * @return Json
     */
    public function getJsonResult(int $httpCode, array $data = []): Json
    {
        $resultPage = $this->jsonFactory->create();
        $resultPage->setData($data);
        $resultPage->setHttpResponseCode($httpCode);

        return $resultPage;
    }
}
