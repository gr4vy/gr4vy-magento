<?php

declare(strict_types=1);

namespace Gr4vy\Magento\Block\Head;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Embed extends \Magento\Framework\View\Element\AbstractBlock
{
    const EMBED_TEMPLATE = 'require.config({ config: { mixins: { \'Magento_Checkout/js/sidebar\': { \'Gr4vy_Magento/js/sidebar-mixins\': true } } }, map: { \'*\': { gr4vyapi: \'https://cdn.{gr4vyId}.gr4vy.app/embed.latest.js\' } } });';

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vy_helper;

    /**
     * @var Gr4vyLogger
     */
    private $gr4vyLogger;

    private $secureRenderer;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Gr4vyHelper $gr4vy_helper,
        Gr4vyLogger $gr4vyLogger,
        SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        $this->gr4vy_helper = $gr4vy_helper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->secureRenderer = $secureRenderer;
        parent::__construct($context, $data);
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    protected function _toHtml() {
        $gr4vy_id = $this->gr4vy_helper->getGr4vyId();
        $nonce = $this->gr4vy_helper->getNonce();        

        $script = $this->renderLinkTemplate($nonce, $gr4vy_id);
        $secureTag = $this->secureRenderer->renderTag('script', ['type' => 'text/javascript'], $script, false);

        return $secureTag;
    }

    /**
     * @param string $assetUrl
     * @return string
     */
    protected function renderLinkTemplate($nonce, $gr4vy_id)
    {
        $embedTemplate = str_replace(
            ['{nonce}'],
            [$nonce],
            self::EMBED_TEMPLATE);

        return str_replace(
            ['{gr4vyId}'],
            [$gr4vy_id],
            $embedTemplate
        );
    }
}
