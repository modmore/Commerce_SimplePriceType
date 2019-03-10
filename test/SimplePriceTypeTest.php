<?php

namespace modmore\Commerce\SimplePriceType\Tests;

use modmore\Commerce\Events\Admin\PriceTypes;
use modmore\Commerce\Pricing\Interfaces\PriceInterface;
use modmore\Commerce\Pricing\Price;
use modmore\Commerce\Pricing\Pricing;
use modmore\Commerce\Pricing\ProductPricing;
use modmore\Commerce\SimplePriceType\Modules\SimplePriceType;
use modmore\Commerce\SimplePriceType\PriceType\Simple;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SimplePriceTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Commerce $commerce */
    public $commerce;
    /** @var \modmore\Commerce\Adapter\AdapterInterface $adapter */
    public $adapter;

    public function setUp()
    {
        global $commerce;
        $this->commerce = $commerce;
        $this->adapter = $this->commerce->adapter;
    }

    public function testSimplePriceTypeModule()
    {
        $module = new SimplePriceType($this->commerce);
        $dispatcher = new EventDispatcher();

        $module->initialize($dispatcher);

        self::assertTrue($dispatcher->hasListeners(\Commerce::EVENT_DASHBOARD_GET_PRICE_TYPES));

        /** @var PriceTypes $event */
        $event = $dispatcher->dispatch(
            \Commerce::EVENT_DASHBOARD_GET_PRICE_TYPES,
            new PriceTypes()
        );

        $types = $event->getPriceTypes();
        self::assertInternalType('array', $types);
        self::assertContains(Simple::class, $types);
    }

    public function testPriceType()
    {
        $simple = new Simple(new Price($this->commerce->getCurrency('EUR'), 1500));

        $price = $simple->getPrice();
        self::assertEquals(1500, $price->getInteger());
        self::assertEquals('EUR', $price->getCurrency()->get('alpha_code'));
    }

    public function testPriceTypeForItem()
    {
        /** @var \comOrderItem $item */
        $item = $this->adapter->newObject('comOrderItem');
        $item->set('quantity', 1);
        $item->set('currency', 'EUR');

        $simple = new Simple(new Price($this->commerce->getCurrency('EUR'), 1250));
        $price = $simple->getPriceForItem($item);
        self::assertInstanceOf(PriceInterface::class, $price);
        self::assertEquals(1250, $price->getInteger());
        self::assertEquals('EUR', $price->getCurrency()->get('alpha_code'));
    }

    public function testPriceTypeInPricing()
    {
        /** @var \comOrderItem $item */
        $item = $this->adapter->newObject('comOrderItem');
        $item->set('quantity', 1);
        $item->set('currency', 'EUR');

        $eur = $this->commerce->getCurrency('EUR');

        $pricing = new ProductPricing($eur, new Price($eur, 2900));

        $price = $pricing->getPriceForItem($item);
        self::assertInstanceOf(PriceInterface::class, $price);
        self::assertEquals(2900, $price->getInteger());
        self::assertEquals('EUR', $price->getCurrency()->get('alpha_code'));


        // This is more testing the Pricing behavior than the Simple, but..
        // if Simple price > Regular, use Regular
        $pricing->addPriceType(new Simple(new Price($eur, 3900)));
        $price = $pricing->getPriceForItem($item);
        self::assertInstanceOf(PriceInterface::class, $price);
        self::assertEquals(2900, $price->getInteger());
        self::assertEquals('EUR', $price->getCurrency()->get('alpha_code'));

        // if Simple price is cheaper, expect that
        $pricing->addPriceType(new Simple(new Price($eur, 2500)));
        $price = $pricing->getPriceForItem($item);
        self::assertInstanceOf(PriceInterface::class, $price);
        self::assertEquals(2500, $price->getInteger());
        self::assertEquals('EUR', $price->getCurrency()->get('alpha_code'));

    }
}
