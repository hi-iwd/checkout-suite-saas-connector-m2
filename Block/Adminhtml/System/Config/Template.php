<?php

namespace IWD\CheckoutConnector\Block\Adminhtml\System\Config;

use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Registry;

class Template extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Registry
     */
    private $_coreRegistry;

    /**
     * @var Config
     */
    private $_emailConfig;

    /**
     * @var CollectionFactory
     */
    protected $_templatesFactory;

    /**
     * @param Registry $coreRegistry
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_coreRegistry = $coreRegistry;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $collection \Magento\Email\Model\ResourceModel\Template\Collection */
        if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
            $collection = $this->_templatesFactory->create();
            $collection->load();
            $this->_coreRegistry->register('config_system_email_template', $collection);
        }

        $options = $collection->toOptionArray();
        $templateId = str_replace('/', '_', $this->getPath());
        $templateId = preg_replace("/payment_([A-Za-z][A-Za-z]|other)_/", "payment_", $templateId ?? '');
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        return $options;
    }
}
