<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const XML_PATH_WEIGHT_CONVERSION_RATIO = 'paazl/locale/weight_conversion';

    /** @var float */
    protected $weightConversion;

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param $weight
     * @return float
     */
    public function getConvertedWeight($weight)
    {
        if (is_null($this->weightConversion)) {
            $weightConversion = $this->scopeConfig->getValue(self::XML_PATH_WEIGHT_CONVERSION_RATIO);
            $this->weightConversion = (!is_null($weightConversion)) ? (float)$weightConversion : (float)1;
        }

        return (float)$weight * $this->weightConversion;
    }
}
