<?php

namespace Brick\Money\CurrencyProvider;

use Brick\Money\Currency;
use Brick\Money\CurrencyProvider;
use Brick\Money\Exception\UnknownCurrencyException;

/**
 * Built-in provider for ISO currencies.
 */
class ISOCurrencyProvider implements CurrencyProvider
{
    /**
     * @var ISOCurrencyProvider|null
     */
    private static $instance;

    /**
     * The raw currency data, indexed by currency code.
     *
     * @var array
     */
    private $currencyData;

    /**
     * The Currency instances.
     *
     * The instances are created on-demand, as soon as they are requested.
     *
     * @var Currency[]
     */
    private $currencies = [];

    /**
     * Whether the provider is in a partial state.
     *
     * This is true as long as all the currencies have not been instantiated yet.
     *
     * @var bool
     */
    private $isPartial = true;

    /**
     * Private constructor. Use `getInstance()` to obtain the singleton instance.
     */
    private function __construct()
    {
        $this->currencyData = require __DIR__ . '/../../data/iso-currencies.php';
    }

    /**
     * Returns the singleton instance of ISOCurrencyProvider.
     *
     * @return ISOCurrencyProvider
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ISOCurrencyProvider();
        }

        return self::$instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency($currencyCode)
    {
        if (isset($this->currencies[$currencyCode])) {
            return $this->currencies[$currencyCode];
        }

        if (! isset($this->currencyData[$currencyCode])) {
            throw UnknownCurrencyException::unknownCurrency($currencyCode);
        }

        $currency = new Currency(... $this->currencyData[$currencyCode]);

        return $this->currencies[$currencyCode] = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableCurrencies()
    {
        if ($this->isPartial) {
            foreach ($this->currencyData as $currencyCode => $data) {
                if (! isset($this->currencies[$currencyCode])) {
                    $this->currencies[$currencyCode] = new Currency(... $data);
                }
            }

            $this->isPartial = false;
        }

        return $this->currencies;
    }
}
