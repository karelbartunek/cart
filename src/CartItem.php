<?php

namespace Cart;

/**
 * @property string $id
 * @property int    $companyDesignId
 * @property int    $pitchPrintProjectId
 * @property string $name
 * @property string $sku
 * @property int    $quantity
 * @property float  $price
 * @property float  $colorPrice
 * @property float  $pagePrice
 * @property float  $tax
 * @property int    $colorId
 * @property int    $numEditPage
 * @property array  $designAttributies
 * @property array  $variantEntities
 * @property string $variant
 * @property string $thumbnail
 */
class CartItem implements \ArrayAccess, Arrayable
{
    /**
     * Cart item data.
     *
     * @var array
     */
    private $data;

    /**
     * Create a new cart item instance.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $defaults = [
            'companyDesignId' => null,
            'pitchPrintProjectId' => null,
            'name' => '',
            'sku' => '',
            'quantity' => 1,
            'price' => 0.00,
            'colorPrice' => 0.00,
            'pagePrice' => 0.00,
            'tax' => 0.00,
            'colorId' => null,
            'numEditPage' => 0,
            'designAttributies' => array(),
            'variantEntities' => array(),
            'variant' => '',
            'thumbnail' => ''
        ];

        $data = array_merge($defaults, $data);

        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Get the cart item id.
     *
     * @return string
     */
    public function getId()
    {
        // keys to ignore in the hashing process
        $ignoreKeys = ['quantity', 'variant'];

        // data to use for the hashing process
        $hashData = $this->data;
        foreach ($ignoreKeys as $key) {
            unset($hashData[$key]);
        }

        $hash = sha1(serialize($hashData));

        return $hash;
    }

    /**
     * Get a piece of data set on the cart item.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        switch ($key) {
            case 'id':
                return $this->getId();
            default:
                return $this->data[$key];
        }
    }

    /**
     * Set a piece of data on the cart item.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return string
     */
    public function set($key, $value)
    {
        switch ($key) {
            case 'quantity':
                $this->setCheckTypeInteger($value, $key);
                break;
            case 'variant':
                $this->setCheckTypeString($value, $key);
                break;
            case 'price':
            case 'colorPrice':
            case 'pagePrice':
            case 'tax':
                $this->setCheckIsNumeric($value, $key);

                $value = (float) $value;
        }

        $this->data[$key] = $value;

        return $this->getId();
    }

    /**
     * Check the value being set is an integer.
     *
     * @param mixed  $value
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    private function setCheckTypeInteger($value, $name)
    {
        if (!is_integer($value)) {
            throw new \InvalidArgumentException(sprintf('%s must be an integer.', $name));
        }
    }

    /**
     * Check the value being set is an string.
     *
     * @param mixed  $value
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    private function setCheckTypeString($value, $name)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('%s must be an string.', $name));
        }
    }

    /**
     * Check the value being set is an integer.
     *
     * @param mixed  $value
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    private function setCheckIsNumeric($value, $name)
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException(sprintf('%s must be numeric.', $name));
        }
    }

    /**
     * Get the total price of the cart item including tax.
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return (float) ($this->getSinglePrice()) * $this->quantity;
    }

    /**
     * Get the total price of the cart item excluding tax.
     *
     * @return float
     */
    public function getTotalPriceExcludingTax()
    {
        return (float) $this->getSinglePriceExcludingTax() * $this->quantity;
    }

    /**
     * Get the single price of the cart item including tax.
     *
     * @return float
     */
    public function getSinglePrice()
    {
        return (float) $this->price + $this->colorPrice + $this->getTotalPagePrice();
    }

    /**
     * Get the single price of the cart item excluding tax.
     *
     * @return float
     */
    public function getSinglePriceExcludingTax()
    {
        $singlePriceExcludingTax = $this->getSinglePrice() / (1 + ($this->tax / 100));
        return (float) round($singlePriceExcludingTax,0);
    }

    /**
     * Get the total tax for the cart item.
     *
     * @return float
     */
    public function getTotalTax()
    {
        return (float) $this->getSingleTax() * $this->quantity;
    }

    /**
     * Get the single tax value of the cart item.
     *
     * @return float
     */
    public function getSingleTax()
    {
        return (float) $this->getSinglePrice() - $this->getSinglePriceExcludingTax();
    }

    /**
     * Get the total value of number page edit.
     *
     * @return float
     */
    public function getTotalPagePrice()
    {
        return (float) $this->pagePrice * $this->numEditPage;
    }

    /**
     * Export the cart item as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'data' => $this->data,
        ];
    }

    /**
     * Determine if a piece of data is set on the cart item.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of data set on the cart item.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a piece of data on the cart item.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a piece of data from the cart item.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get a piece of data set on the cart item.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Set a piece of data on the cart item.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Determine if a piece of data is set on the cart item.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset a piece of data from the cart item.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}
