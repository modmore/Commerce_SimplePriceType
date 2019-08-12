<?php

namespace modmore\Commerce\SimplePriceType\PriceType;

use modmore\Commerce\Pricing\Exceptions\InvalidPriceTypeDataException;
use modmore\Commerce\Pricing\Interfaces\PriceInterface;
use modmore\Commerce\Pricing\Price;
use modmore\Commerce\Pricing\PriceType\Interfaces\ItemPriceTypeInterface;
use modmore\Commerce\Pricing\PriceType\Interfaces\PriceTypeInterface;

final class Simple implements PriceTypeInterface, ItemPriceTypeInterface {

    /**
     * @var PriceInterface
     */
    private $price;

    /**
     * Sale constructor.
     * @param PriceInterface $price
     */
    public function __construct(PriceInterface $price)
    {
        $this->price = $price;
    }

    /**
     * @param \comOrderItem $item
     * @return PriceInterface|false
     */
    public function getPriceForItem(\comOrderItem $item)
    {
        return $this->price;
    }

    /**
     * @return PriceInterface
     */
    public function getPrice()
    {
        return $this->price;
    }

    public function serialize()
    {
        return json_encode([
            'amount' => $this->price->getInteger(),
        ]);
    }

    /**
     * @param \comCurrency $currency
     * @param string $data
     * @return static
     * @throws InvalidPriceTypeDataException
     */
    public static function unserialize(\comCurrency $currency, $data)
    {
        $details = json_decode($data, true);
        if (!is_array($details)) {
            throw new InvalidPriceTypeDataException('Could not decode JSON "' . $data .'" for Simple Price Type');
        }

        $amount = array_key_exists('amount', $details) ? (int)$details['amount'] : 0;
        $price = new Price($currency, $amount);

        return new static($price);
    }

    public static function getTitle()
    {
        return 'commerce_simplepricetype.simple';
    }

    public static function getFields(\Commerce $commerce)
    {
        return [
            [
                'name' => 'amount',
                'type' => 'currency'
            ]
        ];
    }

    public static function doFieldsRepeat()
    {
        return false;
    }

    public static function allowMultiple()
    {
        return false;
    }

    public function __debugInfo()
    {
        return [
            'price' => $this->price,
        ];
    }
}
