<?php

declare(strict_types=1);

namespace Gr4vy\Magento\Block\Head;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;

class Embed extends \Magento\Framework\View\Element\AbstractBlock
{
    const EMBED_TEMPLATE = '<script nonce=\'{nonce}\'>require.config({ config: { mixins: { \'Magento_Checkout/js/sidebar\': { \'Gr4vy_Magento/js/sidebar-mixins\': true } } }, map: { \'*\': { gr4vyapi: \'https://cdn.{gr4vyId}.gr4vy.app/embed.latest.js\' } } });</script>';

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vy_helper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Gr4vyHelper $gr4vy_helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->gr4vy_helper = $gr4vy_helper;
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    protected function _toHtml() {
        $gr4vy_id = $this->gr4vy_helper->getGr4vyId();
        $nonce = $this->gr4vy_helper->getNonce();
        return $this->renderLinkTemplate($nonce, $gr4vy_id);
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
