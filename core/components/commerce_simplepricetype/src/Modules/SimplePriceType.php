<?php
namespace modmore\Commerce\SimplePriceType\Modules;
use modmore\Commerce\Events\Admin\PriceTypes;
use modmore\Commerce\Modules\BaseModule;
use modmore\Commerce\SimplePriceType\PriceType\Simple;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class SimplePriceType extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_simplepricetype:default');
        return 'commerce_simplepricetype.module';
    }

    public function getDescription()
    {
        return 'commerce_simplepricetype.module.description';
    }

    public function getAuthor()
    {
        return 'modmore';
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        $this->adapter->loadLexicon('commerce_simplepricetype:default');
        $dispatcher->addListener(\Commerce::EVENT_DASHBOARD_GET_PRICE_TYPES, [$this, 'registerPriceType']);
    }

    public function registerPriceType(PriceTypes $event)
    {
        $event->addPriceType(Simple::class);
    }
}
